<?php

namespace App\Http\Controllers;

use App\Models\BillingBalance;
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
        return Inertia::render('BillingClosing/Index', [
            'defaultClosingDate' => now()->endOfMonth()->toDateString(),
        ]);
    }

    public function execute(Request $request)
    {
        $request->validate([
            'closing_date' => ['required', 'date'],
            'from_code'    => ['required', 'string', 'max:20'],
            'to_code'      => ['required', 'string', 'max:20'],
            'mode'         => ['required', 'in:aggregate,confirm,cancel'],
        ]);

        $closingDate = $request->input('closing_date');
        $fromCode    = $request->input('from_code');
        $toCode      = $request->input('to_code');
        $mode        = $request->input('mode');

        return match ($mode) {
            'aggregate' => $this->handleAggregate($closingDate, $fromCode, $toCode),
            'confirm'   => $this->handleConfirm($closingDate, $fromCode, $toCode),
            'cancel'    => $this->handleCancel($closingDate, $fromCode, $toCode),
        };
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'closing_date' => ['required', 'date'],
            'from_code'    => ['required', 'string'],
            'to_code'      => ['required', 'string'],
            'mode'         => ['required', 'in:aggregate,confirm'],
        ]);

        $rows = $this->service->aggregate(
            $request->input('closing_date'),
            $request->input('from_code'),
            $request->input('to_code'),
        );

        $billingDate = $request->input('closing_date');

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

    private function handleAggregate(string $closingDate, string $fromCode, string $toCode)
    {
        $rows = $this->service->aggregate($closingDate, $fromCode, $toCode);

        return Inertia::render('BillingClosing/Index', [
            'defaultClosingDate' => now()->endOfMonth()->toDateString(),
            'results'            => $this->formatRows($rows),
            'mode'               => 'aggregate',
            'closingDate'        => $closingDate,
            'fromCode'           => $fromCode,
            'toCode'             => $toCode,
        ]);
    }

    private function handleConfirm(string $closingDate, string $fromCode, string $toCode)
    {
        $rows = $this->service->confirm($closingDate, $fromCode, $toCode);

        return redirect()->route('billing-closing.index')
            ->with('success', "締め処理（確定）が完了しました（{$rows->where('error', null)->count()}件）");
    }

    private function handleCancel(string $closingDate, string $fromCode, string $toCode)
    {
        $preview = $this->service->previewCancel($closingDate, $fromCode, $toCode);

        if ($preview->isEmpty()) {
            return Inertia::render('BillingClosing/Index', [
                'defaultClosingDate' => now()->endOfMonth()->toDateString(),
                'results'            => [],
                'mode'               => 'cancel',
                'closingDate'        => $closingDate,
                'fromCode'           => $fromCode,
                'toCode'             => $toCode,
                'flash'              => ['error' => '取消対象の確定済み締めが見つかりませんでした'],
            ]);
        }

        return Inertia::render('BillingClosing/Index', [
            'defaultClosingDate' => now()->endOfMonth()->toDateString(),
            'cancelPreview'      => $preview->map(fn ($r) => [
                'billing_number'  => $r['billing_balance']->billing_number,
                'customer_code'   => $r['billing_balance']->customer->code,
                'customer_name'   => $r['billing_balance']->customer->name,
                'billing_date'    => $r['billing_balance']->billing_date->toDateString(),
                'balance_amount'  => (float) $r['billing_balance']->balance_amount,
                'cancelable'      => $r['cancelable'],
                'newer_number'    => $r['newer_number'],
            ])->values(),
            'mode'        => 'cancel',
            'closingDate' => $closingDate,
            'fromCode'    => $fromCode,
            'toCode'      => $toCode,
        ]);
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
