<?php

namespace App\Http\Controllers;

use App\Models\BillingBalance;
use App\Models\Customer;
use App\Services\BillingClosingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingClosingController extends Controller
{
    public function __construct(private BillingClosingService $service) {}

    public function index(): Response
    {
        $closingDays = $this->getClosingDays()->toArray();
        $defaultClosingDay  = $this->computeDefaultClosingDay($closingDays);
        $defaultBillingDate = $this->computeDefaultBillingDate($defaultClosingDay);

        return Inertia::render('BillingClosing/Index', [
            'defaultBillingDate' => $defaultBillingDate,
            'defaultClosingDay'  => $defaultClosingDay,
            'closingDays'        => $closingDays,
        ]);
    }

    /**
     * 検索：処理区分に応じたプレビューを返す（DBへの変更なし）(仕様6)
     */
    public function search(Request $request): Response
    {
        $validated = $request->validate([
            'billing_date' => ['required', 'date'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'from_code'    => ['nullable', 'string', 'max:20'],
            'to_code'      => ['nullable', 'string', 'max:20'],
            'mode'         => ['required', 'in:aggregate,confirm,cancel'],
        ]);

        $billingDate = $validated['billing_date'];
        $closingDay  = (int) $validated['closing_day'];
        $fromCode    = (string) ($validated['from_code'] ?? '');
        $toCode      = (string) ($validated['to_code'] ?? '');
        $mode        = $validated['mode'];

        $closingDays        = $this->getClosingDays()->toArray();
        $defaultClosingDay  = $this->computeDefaultClosingDay($closingDays);
        $baseProps = [
            'defaultBillingDate' => $this->computeDefaultBillingDate($defaultClosingDay),
            'defaultClosingDay'  => $defaultClosingDay,
            'closingDays'        => $closingDays,
            'mode'               => $mode,
            'billingDate'        => $billingDate,
            'closingDay'         => $closingDay,
            'fromCode'           => $fromCode,
            'toCode'             => $toCode,
        ];

        if ($mode === 'cancel') {
            $preview = $this->service->previewCancel($billingDate, $closingDay, $fromCode, $toCode);

            return Inertia::render('BillingClosing/Index', array_merge($baseProps, [
                'cancelPreview' => $preview->map(fn ($r) => [
                    'billing_number'  => $r['billing_balance']->billing_number,
                    'customer_code'   => $r['billing_balance']->customer->code,
                    'customer_name'   => $r['billing_balance']->customer->name,
                    'billing_date'    => $r['billing_balance']->billing_date->toDateString(),
                    'balance_amount'  => (float) $r['billing_balance']->balance_amount,
                    'cancelable'      => $r['cancelable'],
                    'newer_number'    => $r['newer_number'],
                ])->values(),
            ]));
        }

        // 集計：計上（recorded）を対象にプレビュー
        // 確定：請求中（invoiced）を対象にプレビュー
        $rows = $mode === 'confirm'
            ? $this->service->previewConfirm($billingDate, $closingDay, $fromCode, $toCode)
            : $this->service->aggregate($billingDate, $closingDay, $fromCode, $toCode);

        return Inertia::render('BillingClosing/Index', array_merge($baseProps, [
            'results' => $this->formatRows($rows),
        ]));
    }

    /**
     * 集計実行：計上→請求中へステータスを更新（BillingBalance未作成）
     */
    public function doAggregate(Request $request)
    {
        $validated = $request->validate([
            'billing_date' => ['required', 'date'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'from_code'    => ['nullable', 'string', 'max:20'],
            'to_code'      => ['nullable', 'string', 'max:20'],
        ]);

        $count = $this->service->executeAggregate(
            $validated['billing_date'],
            (int) $validated['closing_day'],
            (string) ($validated['from_code'] ?? ''),
            (string) ($validated['to_code'] ?? ''),
        );

        return redirect()->route('billing-closing.index')
            ->with('success', "集計が完了しました（{$count}件を請求中に更新）");
    }

    /**
     * 確定実行：請求中の売上・入金をBillingBalanceに確定 (仕様8)
     */
    public function doConfirm(Request $request)
    {
        $validated = $request->validate([
            'billing_date' => ['required', 'date'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'from_code'    => ['nullable', 'string', 'max:20'],
            'to_code'      => ['nullable', 'string', 'max:20'],
        ]);

        $billingDate = $validated['billing_date'];
        $closingDay  = (int) $validated['closing_day'];
        $fromCode    = (string) ($validated['from_code'] ?? '');
        $toCode      = (string) ($validated['to_code'] ?? '');

        $rows    = $this->service->confirm($billingDate, $closingDay, $fromCode, $toCode);
        $success = $rows->where('error', null)->count();
        $errors  = $rows->where('error', '!=', null)->count();

        $msg = "確定が完了しました（{$success}件）";
        if ($errors > 0) {
            $msg .= "（スキップ: {$errors}件）";
        }

        return redirect()->route('billing-closing.index')->with('success', $msg);
    }

    /**
     * 取消実行：売上・入金の確定フラグを未に戻し請求残高を削除 (仕様9)
     */
    public function doCancel(Request $request)
    {
        $validated = $request->validate([
            'billing_date' => ['required', 'date'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'from_code'    => ['nullable', 'string', 'max:20'],
            'to_code'      => ['nullable', 'string', 'max:20'],
        ]);

        $billingDate = $validated['billing_date'];
        $closingDay  = (int) $validated['closing_day'];
        $fromCode    = (string) ($validated['from_code'] ?? '');
        $toCode      = (string) ($validated['to_code'] ?? '');

        $rows    = $this->service->cancel($billingDate, $closingDay, $fromCode, $toCode);
        $success = $rows->where('error', null)->count();
        $errors  = $rows->where('error', '!=', null)->count();

        $msg = "取消が完了しました（{$success}件）";
        if ($errors > 0) {
            $msg .= "（スキップ: {$errors}件）";
        }

        return redirect()->route('billing-closing.index')->with('success', $msg);
    }

    /**
     * 請求書PDF一括出力 (仕様7)
     */
    public function pdf(Request $request)
    {
        $request->validate([
            'billing_date' => ['required', 'date'],
            'closing_day'  => ['required', 'integer', 'min:1', 'max:31'],
            'from_code'    => ['nullable', 'string'],
            'to_code'      => ['nullable', 'string'],
        ]);

        $billingDate = $request->input('billing_date');
        $closingDay  = (int) $request->input('closing_day');
        $fromCode    = (string) $request->input('from_code', '');
        $toCode      = (string) $request->input('to_code', '');

        $rows = $this->service->aggregate($billingDate, $closingDay, $fromCode, $toCode);

        $pdf = Pdf::loadView('pdf.billing', compact('rows', 'billingDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('請求書_'.$billingDate.'.pdf');
    }

    public function pdfSingle(BillingBalance $billingBalance)
    {
        abort_if($billingBalance->is_deleted, 404);
        $billingBalance->load(['customer', 'sales', 'payments']);

        $rows = collect([[
            'customer'           => $billingBalance->customer,
            'prev_amount'        => (float) $billingBalance->prev_amount,
            'sales_amount'       => (float) $billingBalance->sales_amount,
            'tax_amount'         => (float) $billingBalance->tax_amount,
            'total_amount'       => (float) $billingBalance->total_amount,
            'payment_amount'     => (float) $billingBalance->payment_amount,
            'balance_amount'     => (float) $billingBalance->balance_amount,
            'sales'              => $billingBalance->sales,
            'payments'           => $billingBalance->payments,
            'closing_start_date' => $billingBalance->closing_start_date?->toDateString(),
            'billing_number'     => $billingBalance->billing_number,
        ]]);

        $billingDate = $billingBalance->billing_date->toDateString();

        $pdf = Pdf::loadView('pdf.billing', compact('rows', 'billingDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('請求書_'.$billingBalance->billing_number.'.pdf');
    }

    // ─── Private ────────────────────────────────────────────────────────

    private function getClosingDays(): \Illuminate\Support\Collection
    {
        return Customer::active()
            ->whereNotNull('closing_day')
            ->distinct()
            ->orderBy('closing_day')
            ->pluck('closing_day');
    }

    /**
     * 現在日付より未来直近の締め日を返す (仕様10)
     */
    private function computeDefaultClosingDay(array $closingDays): int
    {
        if (empty($closingDays)) {
            return 31;
        }

        $today       = now();
        $todayDay    = (int) $today->day;
        $lastDayOfMonth = (int) $today->daysInMonth;

        foreach ($closingDays as $day) {
            $actualDay = (int) $day === 31 ? $lastDayOfMonth : min((int) $day, $lastDayOfMonth);
            if ($actualDay >= $todayDay) {
                return (int) $day;
            }
        }

        // 当月の締め日がすべて過去 → 最後の締め日を返す
        return (int) end($closingDays);
    }

    /**
     * 締め日プルダウンの値から当月の請求日を返す (仕様11)
     */
    private function computeDefaultBillingDate(int $closingDay): string
    {
        $today = now();
        if ($closingDay === 31) {
            return $today->copy()->endOfMonth()->toDateString();
        }
        $day = min($closingDay, (int) $today->daysInMonth);

        return $today->copy()->setDay($day)->toDateString();
    }

    private function formatRows(\Illuminate\Support\Collection $rows): array
    {
        return $rows->map(fn ($r) => [
            'customer_code'      => $r['customer']->code,
            'customer_name'      => $r['customer']->name,
            'prev_amount'        => $r['prev_amount'],
            'sales_amount'       => $r['sales_amount'],
            'tax_amount'         => $r['tax_amount'],
            'total_amount'       => $r['total_amount'],
            'payment_amount'     => $r['payment_amount'],
            'balance_amount'     => $r['balance_amount'],
            'sales_count'        => $r['sales']->count(),
            'payments_count'     => $r['payments']->count(),
            'closing_start_date' => $r['closing_start_date'],
            'error'              => $r['error'],
        ])->values()->all();
    }
}
