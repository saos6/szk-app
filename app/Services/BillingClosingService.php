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
     * 集計プレビュー：計上（recorded）の売上・入金を集計して返す（DBへの変更なし）
     *
     * @return Collection<int, array{...}>
     */
    public function aggregate(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date      = Carbon::parse($billingDate);
        $customers = $this->getCustomers($closingDay, $fromCode, $toCode);

        return $customers->map(fn (Customer $c) => $this->calcForCustomer($c, $date, 'recorded', 'recorded'))
            ->filter()->values();
    }

    /**
     * 確定プレビュー：請求中（invoiced）の売上・入金を集計して返す（DBへの変更なし）
     */
    public function previewConfirm(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date      = Carbon::parse($billingDate);
        $customers = $this->getCustomers($closingDay, $fromCode, $toCode);

        return $customers->map(fn (Customer $c) => $this->calcForCustomer($c, $date, 'invoiced', 'confirmed'))
            ->filter()->values();
    }

    /**
     * 集計実行：計上→請求中へステータスを更新（BillingBalanceは作成しない）
     */
    public function executeAggregate(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): int
    {
        $date        = Carbon::parse($billingDate);
        $customerIds = $this->getCustomers($closingDay, $fromCode, $toCode)->pluck('id');

        $saleCount = Sale::active()
            ->whereIn('customer_id', $customerIds)
            ->whereNull('billing_balance_id')
            ->where('status', 'recorded')
            ->where('sale_date', '<=', $date->toDateString())
            ->update(['status' => 'invoiced']);

        $paymentCount = Payment::active()
            ->whereIn('customer_id', $customerIds)
            ->whereNull('billing_balance_id')
            ->where('status', 'recorded')
            ->where('payment_date', '<=', $date->toDateString())
            ->update(['status' => 'confirmed']);

        return $saleCount + $paymentCount;
    }

    /**
     * 確定：BillingBalance を作成し売上・入金を請求完了にセット (仕様8)
     */
    public function confirm(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date      = Carbon::parse($billingDate);
        $customers = $this->getCustomers($closingDay, $fromCode, $toCode);

        $results = collect();

        DB::transaction(function () use ($customers, $date, &$results) {
            foreach ($customers as $customer) {
                $row = $this->calcForCustomer($customer, $date, 'invoiced', 'confirmed');
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

                // 売上に確定フラグをセット・ステータスを完了に (仕様8)
                Sale::whereIn('id', $row['sales']->pluck('id'))
                    ->update([
                        'billing_balance_id' => $bb->id,
                        'status'             => 'completed',
                    ]);

                // 入金に確定フラグをセット・ステータスを完了に (仕様8)
                Payment::whereIn('id', $row['payments']->pluck('id'))
                    ->update([
                        'billing_balance_id' => $bb->id,
                        'status'             => 'completed',
                    ]);

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
            ->whereHas('customer', fn ($q) => $q->where('closing_day', $closingDay)
                ->when($fromCode !== '', fn ($q2) => $q2->where('code', '>=', $fromCode))
                ->when($toCode !== '', fn ($q2) => $q2->where('code', '<=', $toCode)))
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
     * 取消：確定済みBillingBalanceを取り消し、売上・入金を請求中に戻す (仕様9)
     */
    public function cancel(string $billingDate, int $closingDay, string $fromCode = '', string $toCode = ''): Collection
    {
        $date = Carbon::parse($billingDate);

        $targets = BillingBalance::active()
            ->with('customer')
            ->where('status', 'confirmed')
            ->where('billing_date', $date->toDateString())
            ->whereHas('customer', fn ($q) => $q->where('closing_day', $closingDay)
                ->when($fromCode !== '', fn ($q2) => $q2->where('code', '>=', $fromCode))
                ->when($toCode !== '', fn ($q2) => $q2->where('code', '<=', $toCode)))
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

                // 売上の確定フラグをリセット・ステータスを請求中に戻す (仕様9)
                Sale::where('billing_balance_id', $bb->id)
                    ->update([
                        'billing_balance_id' => null,
                        'status'             => 'invoiced',
                    ]);

                // 入金の確定フラグをリセット・ステータスを請求中に戻す (仕様9)
                Payment::where('billing_balance_id', $bb->id)
                    ->update([
                        'billing_balance_id' => null,
                        'status'             => 'confirmed',
                    ]);

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

    private function getCustomers(int $closingDay, string $fromCode, string $toCode): \Illuminate\Support\Collection
    {
        return Customer::active()
            ->where('closing_day', $closingDay)
            ->when($fromCode !== '', fn ($q) => $q->where('code', '>=', $fromCode))
            ->when($toCode !== '', fn ($q) => $q->where('code', '<=', $toCode))
            ->orderBy('code')
            ->get();
    }

    /**
     * 得意先1件分の請求集計を計算する
     *
     * @param string $saleStatus    対象売上ステータス ('recorded' or 'invoiced')
     * @param string $paymentStatus 対象入金ステータス ('recorded' or 'confirmed')
     */
    private function calcForCustomer(Customer $customer, Carbon $date, string $saleStatus = 'recorded', string $paymentStatus = 'recorded'): ?array
    {
        $sales = Sale::with('items')
            ->active()
            ->where('customer_id', $customer->id)
            ->whereNull('billing_balance_id')
            ->where('status', $saleStatus)
            ->where('sale_date', '<=', $date->toDateString())
            ->get();

        $payments = Payment::with('items')
            ->active()
            ->where('customer_id', $customer->id)
            ->whereNull('billing_balance_id')
            ->where('status', $paymentStatus)
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
