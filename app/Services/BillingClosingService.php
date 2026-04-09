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
     * 集計：各得意先の請求金額を計算して返す（DBへの変更なし）
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
     * }>
     */
    public function aggregate(string $closingDate, string $fromCode, string $toCode): Collection
    {
        $date = Carbon::parse($closingDate);

        $customers = Customer::active()
            ->whereBetween('code', [$fromCode, $toCode])
            ->orderBy('code')
            ->get();

        return $customers->map(function (Customer $customer) use ($date) {
            return $this->calcForCustomer($customer, $date);
        })->filter(fn ($row) => $row !== null)->values();
    }

    /**
     * 確定：集計結果をDBに保存し、売上・入金に確定フラグをセット
     */
    public function confirm(string $closingDate, string $fromCode, string $toCode): Collection
    {
        $date = Carbon::parse($closingDate);

        $customers = Customer::active()
            ->whereBetween('code', [$fromCode, $toCode])
            ->orderBy('code')
            ->get();

        $results = collect();

        DB::transaction(function () use ($customers, $date, &$results) {
            foreach ($customers as $customer) {
                $row = $this->calcForCustomer($customer, $date);
                if ($row === null) {
                    continue;
                }

                // 順序チェック：この締め日より新しい確定済みが存在しないか
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

                // 同一得意先×同一締め日の重複チェック
                $duplicate = BillingBalance::active()
                    ->where('customer_id', $customer->id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', $date->toDateString())
                    ->exists();

                if ($duplicate) {
                    $row['error'] = '同一締め日で既に確定済みです';
                    $results->push($row);
                    continue;
                }

                // BillingBalance 作成
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

                // 売上に確定フラグ
                Sale::whereIn('id', $row['sales']->pluck('id'))
                    ->update([
                        'billing_balance_id' => $bb->id,
                        'status'             => 'invoiced',
                    ]);

                // 入金に確定フラグ
                Payment::whereIn('id', $row['payments']->pluck('id'))
                    ->update(['billing_balance_id' => $bb->id]);

                $row['billing_balance'] = $bb;
                $results->push($row);
            }
        });

        return $results;
    }

    /**
     * 取消：指定締め日の確定済みBillingBalanceを取り消す
     */
    public function cancel(string $closingDate, string $fromCode, string $toCode): Collection
    {
        $date = Carbon::parse($closingDate);

        $targets = BillingBalance::active()
            ->with('customer')
            ->where('status', 'confirmed')
            ->where('billing_date', $date->toDateString())
            ->whereHas('customer', fn ($q) => $q->whereBetween('code', [$fromCode, $toCode]))
            ->get();

        $results = collect();

        DB::transaction(function () use ($targets, $date, &$results) {
            foreach ($targets as $bb) {
                // 順序チェック：この締め日より新しい確定済みが存在しないか
                $hasNewer = BillingBalance::active()
                    ->where('customer_id', $bb->customer_id)
                    ->where('status', 'confirmed')
                    ->where('billing_date', '>', $date->toDateString())
                    ->exists();

                if ($hasNewer) {
                    $results->push([
                        'billing_balance' => $bb,
                        'error' => '後続の確定済み締め（'.BillingBalance::active()
                            ->where('customer_id', $bb->customer_id)
                            ->where('status', 'confirmed')
                            ->where('billing_date', '>', $date->toDateString())
                            ->orderBy('billing_date')
                            ->value('billing_number').'）が存在するため取消できません',
                    ]);
                    continue;
                }

                // 売上の確定フラグをリセット
                Sale::where('billing_balance_id', $bb->id)
                    ->update([
                        'billing_balance_id' => null,
                        'status'             => 'delivered',
                    ]);

                // 入金の確定フラグをリセット
                Payment::where('billing_balance_id', $bb->id)
                    ->update(['billing_balance_id' => null]);

                // BillingBalance を取消
                $bb->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id(),
                ]);

                $results->push(['billing_balance' => $bb, 'error' => null]);
            }
        });

        return $results;
    }

    /**
     * 取消対象の一覧（プレビュー用）
     */
    public function previewCancel(string $closingDate, string $fromCode, string $toCode): Collection
    {
        $date = Carbon::parse($closingDate);

        return BillingBalance::active()
            ->with('customer')
            ->where('status', 'confirmed')
            ->where('billing_date', $date->toDateString())
            ->whereHas('customer', fn ($q) => $q->whereBetween('code', [$fromCode, $toCode]))
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

    // ─── Private Helpers ────────────────────────────────────────────────

    private function calcForCustomer(Customer $customer, Carbon $date): ?array
    {
        // 対象売上（未確定・納品済以上・締め日以内）
        $sales = Sale::active()
            ->where('customer_id', $customer->id)
            ->whereNull('billing_balance_id')
            ->whereIn('status', ['delivered', 'invoiced', 'completed'])
            ->where('sale_date', '<=', $date->toDateString())
            ->get();

        // 対象入金（未確定・確認済・締め日以内）
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

        // 前回繰越
        $lastBilling = BillingBalance::active()
            ->where('customer_id', $customer->id)
            ->where('status', 'confirmed')
            ->orderByDesc('billing_date')
            ->first();

        $prevAmount     = $lastBilling ? (float) $lastBilling->balance_amount : 0.0;
        $closingStart   = $lastBilling
            ? Carbon::parse($lastBilling->billing_date)->addDay()->toDateString()
            : null;

        $salesAmount    = $sales->sum(fn ($s) => (float) $s->subtotal);
        $taxAmount      = $sales->sum(fn ($s) => (float) $s->tax_amount);
        $totalAmount    = $prevAmount + $salesAmount + $taxAmount;
        $paymentAmount  = $payments->sum(fn ($p) => (float) $p->total_amount);
        $balanceAmount  = $totalAmount - $paymentAmount;

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
