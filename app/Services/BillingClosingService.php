<?php

namespace App\Services;

use App\Models\BillingBalance;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingClosingService
{
    /**
     * 集計・確定プレビュー：各得意先の請求金額を計算して返す（DBへの変更なし）
     *
     * @return Collection<int, array{
     *   customer: Customer,
     *   prev_amount: float,
     *   sales_amount: float,
     *   tax_amount: float,
     *   total_amount: float,
     *   payment_amount: float,
     *   balance_amount: float,
     *   sales: Collection,
     *   payments: Collection,
     *   closing_start_date: string|null,
     *   error: string|null,
     * }>
     */
    public function aggregate(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date = Carbon::parse($billingDate);

        $customers = Customer::active()
            ->where('closing_day', $closingDay)
            ->when($fromCode !== '', fn ($q) => $q->where('code', '>=', $fromCode))
            ->when($toCode !== '', fn ($q) => $q->where('code', '<=', $toCode))
            ->orderBy('code')
            ->get();

        return $customers->map(function (Customer $customer) use ($date) {
            return $this->calcForCustomer($customer, $date);
        })->filter(fn ($row) => $row !== null)->values();
    }

    /**
     * 確定：集計結果をDBに保存し、売上・入金に確定フラグをセット (仕様8)
     */
    public function confirm(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date = Carbon::parse($billingDate);

        $customers = Customer::active()
            ->where('closing_day', $closingDay)
            ->when($fromCode !== '', fn ($q) => $q->where('code', '>=', $fromCode))
            ->when($toCode !== '', fn ($q) => $q->where('code', '<=', $toCode))
            ->orderBy('code')
            ->get();

        $results = collect();

        DB::transaction(function () use ($customers, $date, &$results) {
            foreach ($customers as $customer) {
                $row = $this->calcForCustomer($customer, $date);
                if ($row === null) {
                    continue;
                }

                // 順序チェック：この請求日より新しい確定済みが存在しないか
                $hasNewer = BillingBalance::active()
                    ->where('customer_id', $customer->id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', '>', $date->toDateString())
                    ->exists();

                if ($hasNewer) {
                    $row['error'] = '後続の確定済み締めが存在するため確定できません';
                    $results->push($row);
                    continue;
                }

                // 同一得意先×同一請求日の重複チェック
                $duplicate = BillingBalance::active()
                    ->where('customer_id', $customer->id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', $date->toDateString())
                    ->exists();

                if ($duplicate) {
                    $row['error'] = '同一請求日で既に確定済みです';
                    $results->push($row);
                    continue;
                }

                // BillingBalance 作成 (仕様8)
                $bb = BillingBalance::create([
                    'billing_number'     => BillingBalance::generateBillingNumber($date->toDateString()),
                    'billing_date'       => $date->toDateString(),
                    'closing_start_date' => $row['closing_start_date'],
                    'customer_id'        => $customer->id,
                    'prev_amount'        => $row['prev_amount'],
                    'sales_amount'       => $row['sales_amount'],
                    'tax_amount'         => $row['tax_amount'],
                    'total_amount'       => $row['total_amount'],
                    'payment_amount'     => $row['payment_amount'],
                    'balance_amount'     => $row['balance_amount'],
                    'status'             => 'confirmed',
                ]);

                // 売上に確定フラグをセット (仕様8)
                Sale::whereIn('id', $row['sales']->pluck('id'))
                    ->update([
                        'billing_balance_id' => $bb->id,
                        'status'             => 'invoiced',
                    ]);

                // 入金に確定フラグをセット (仕様8)
                Payment::whereIn('id', $row['payments']->pluck('id'))
                    ->update(['billing_balance_id' => $bb->id]);

                $row['billing_balance'] = $bb;
                $results->push($row);
            }
        });

        return $results;
    }

    /**
     * 取消プレビュー：指定請求日の確定済みBillingBalanceを返す (仕様5, 9)
     */
    public function previewCancel(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date = Carbon::parse($billingDate);

        return BillingBalance::active()
            ->with('customer')
            ->where('status', 'confirmed')
            ->where('billing_date', $date->toDateString())
            ->whereHas('customer', function ($q) use ($fromCode, $toCode, $closingDay) {
                $q->where('closing_day', $closingDay)
                  ->when($fromCode !== '', fn ($q2) => $q2->where('code', '>=', $fromCode))
                  ->when($toCode !== '', fn ($q2) => $q2->where('code', '<=', $toCode));
            })
            ->get()
            ->map(function (BillingBalance $bb) use ($date) {
                $hasNewer = BillingBalance::active()
                    ->where('customer_id', $bb->customer_id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', '>', $date->toDateString())
                    ->exists();

                $newerNumber = $hasNewer
                    ? BillingBalance::active()
                        ->where('customer_id', $bb->customer_id)
                        ->where('status', 'confirmed')
                        ->where('billing_date', '>', $date->toDateString())
                        ->orderBy('billing_date')
                        ->value('billing_number')
                    : null;

                return [
                    'billing_balance' => $bb,
                    'cancelable'      => ! $hasNewer,
                    'newer_number'    => $newerNumber,
                ];
            });
    }

    /**
     * 取消：確定済みBillingBalanceを取り消し、売上・入金の確定フラグを未に戻す (仕様9)
     */
    public function cancel(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date = Carbon::parse($billingDate);

        $targets = BillingBalance::active()
            ->with('customer')
            ->where('status', 'confirmed')
            ->where('billing_date', $date->toDateString())
            ->whereHas('customer', function ($q) use ($fromCode, $toCode, $closingDay) {
                $q->where('closing_day', $closingDay)
                  ->when($fromCode !== '', fn ($q2) => $q2->where('code', '>=', $fromCode))
                  ->when($toCode !== '', fn ($q2) => $q2->where('code', '<=', $toCode));
            })
            ->get();

        $results = collect();

        DB::transaction(function () use ($targets, $date, &$results) {
            foreach ($targets as $bb) {
                // 順序チェック
                $hasNewer = BillingBalance::active()
                    ->where('customer_id', $bb->customer_id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', '>', $date->toDateString())
                    ->exists();

                if ($hasNewer) {
                    $newerNumber = BillingBalance::active()
                        ->where('customer_id', $bb->customer_id)
                        ->where('status', 'confirmed')
                        ->where('billing_date', '>', $date->toDateString())
                        ->orderBy('billing_date')
                        ->value('billing_number');

                    $results->push([
                        'billing_balance' => $bb,
                        'error'           => "後続の確定済み締め（{$newerNumber}）が存在するため取消できません",
                    ]);
                    continue;
                }

                // 売上の確定フラグをリセット（未に戻す）(仕様9)
                Sale::where('billing_balance_id', $bb->id)
                    ->update([
                        'billing_balance_id' => null,
                        'status'             => 'delivered',
                    ]);

                // 入金の確定フラグをリセット（未に戻す）(仕様9)
                Payment::where('billing_balance_id', $bb->id)
                    ->update(['billing_balance_id' => null]);

                // 請求残高を削除（論理削除）(仕様9)
                $bb->is_deleted   = true;
                $bb->status       = 'cancelled';
                $bb->cancelled_at = now();
                $bb->cancelled_by = Auth::id();
                $bb->save();

                $results->push(['billing_balance' => $bb, 'error' => null]);
            }
        });

        return $results;
    }

    // ─── Private Helpers ────────────────────────────────────────────────

    /**
     * 得意先1件分の請求集計を計算する
     * 売上：請求確定フラグ=未 かつ 売上日<=請求日 (仕様3)
     * 入金：請求確定フラグ=未 かつ 入金日<=請求日 (仕様3)
     * 前月繰越：請求日より前の直近確定済み請求残高のbalance_amount (仕様3)
     */
    private function calcForCustomer(Customer $customer, Carbon $date): ?array
    {
        // 対象売上（未確定・納品済以上・請求日以内）
        $sales = Sale::active()
            ->where('customer_id', $customer->id)
            ->whereNull('billing_balance_id')
            ->whereIn('status', ['delivered', 'invoiced', 'completed'])
            ->where('sale_date', '<=', $date->toDateString())
            ->get();

        // 対象入金（未確定・確認済・請求日以内）
        $payments = Payment::active()
            ->where('customer_id', $customer->id)
            ->whereNull('billing_balance_id')
            ->where('status', 'confirmed')
            ->where('payment_date', '<=', $date->toDateString())
            ->get();

        // 売上も入金もなければスキップ
        if ($sales->isEmpty() && $payments->isEmpty()) {
            return null;
        }

        // 前回繰越：請求日より前の直近確定済み請求残高 (仕様3)
        $lastBilling = BillingBalance::active()
            ->where('customer_id', $customer->id)
            ->where('status', 'confirmed')
            ->where('billing_date', '<', $date->toDateString())
            ->orderByDesc('billing_date')
            ->first();

        $prevAmount   = $lastBilling ? (float) $lastBilling->balance_amount : 0.0;
        $closingStart = $lastBilling
            ? Carbon::parse($lastBilling->billing_date)->addDay()->toDateString()
            : null;

        $salesAmount   = $sales->sum(fn ($s) => (float) $s->subtotal);
        $taxAmount     = $sales->sum(fn ($s) => (float) $s->tax_amount);
        $totalAmount   = $prevAmount + $salesAmount + $taxAmount;
        $paymentAmount = $payments->sum(fn ($p) => (float) $p->total_amount);
        $balanceAmount = $totalAmount - $paymentAmount;

        return [
            'customer'           => $customer,
            'prev_amount'        => $prevAmount,
            'sales_amount'       => $salesAmount,
            'tax_amount'         => $taxAmount,
            'total_amount'       => $totalAmount,
            'payment_amount'     => $paymentAmount,
            'balance_amount'     => $balanceAmount,
            'sales'              => $sales,
            'payments'           => $payments,
            'closing_start_date' => $closingStart,
            'error'              => null,
        ];
    }
}
