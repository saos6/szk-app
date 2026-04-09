<?php

namespace App\Http\Controllers;

use App\Exports\PaymentsExport;
use App\Http\Requests\PaymentRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\PaymentItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'payment_number');
        $direction = $request->input('direction', 'desc');
        $perPage   = (int) $request->input('per_page', 10);

        $allowedSorts = ['payment_number', 'payment_date', 'subject', 'status', 'total_amount', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'payment_number';
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $payments = Payment::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($search, $status)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters'  => compact('search', 'status', 'sort', 'direction') + ['per_page' => (string) $perPage],
            'statuses' => Payment::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Payments/Create', $this->formData());
    }

    public function store(PaymentRequest $request)
    {
        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        $validated['payment_number'] = Payment::generatePaymentNumber();
        $validated['total_amount']   = $this->calculateTotal($items);

        $payment = Payment::create($validated);
        $this->syncItems($payment, $items);

        return redirect()->route('payments.index')->with('success', '入金を登録しました。');
    }

    public function replicate(Payment $payment): Response
    {
        abort_if($payment->is_deleted, 404);
        $payment->load('items');

        $prefill = [
            'customer_id'  => (string) $payment->customer_id,
            'employee_id'  => $payment->employee_id ? (string) $payment->employee_id : '',
            'subject'      => $payment->subject,
            'remarks'      => $payment->remarks ?? '',
            'items'        => $payment->items->map(fn ($item) => [
                'payment_type' => $item->payment_type,
                'amount'       => (float) $item->amount,
                'bank_info'    => $item->bank_info ?? '',
                'remarks'      => $item->remarks ?? '',
            ])->values()->all(),
        ];

        return Inertia::render('Payments/Create', array_merge(
            $this->formData(),
            ['prefill' => $prefill]
        ));
    }

    public function show(Payment $payment): Response
    {
        abort_if($payment->is_deleted, 404);
        $payment->load(['customer', 'employee', 'items']);

        return Inertia::render('Payments/Show', [
            'payment'      => $payment,
            'statuses'     => Payment::STATUSES,
            'paymentTypes' => Payment::PAYMENT_TYPES,
        ]);
    }

    public function edit(Payment $payment): Response
    {
        abort_if($payment->is_deleted, 404);
        $payment->load('items');

        return Inertia::render('Payments/Edit', array_merge(
            $this->formData(),
            ['payment' => $payment]
        ));
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        abort_if($payment->is_deleted, 404);

        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        $validated['total_amount'] = $this->calculateTotal($items);

        $payment->update($validated);
        $this->syncItems($payment, $items);

        return redirect()->route('payments.index')->with('success', '入金を更新しました。');
    }

    public function destroy(Payment $payment)
    {
        abort_if($payment->is_deleted, 404);
        $payment->is_deleted = true;
        $payment->save();

        return redirect()->route('payments.index')->with('success', '入金を削除しました。');
    }

    public function pdf(Payment $payment)
    {
        abort_if($payment->is_deleted, 404);
        $payment->load(['customer', 'employee', 'items']);

        $pdf = Pdf::loadView('pdf.payment', compact('payment'))
            ->setPaper('a4', 'portrait');

        $filename = '入金確認書_'.$payment->payment_number.'.pdf';

        return $pdf->download($filename);
    }

    public function exportMethod(Request $request)
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'payment_number');
        $direction = $request->input('direction', 'desc');

        return Excel::download(
            new PaymentsExport($search, $status, $sort, $direction),
            '入金一覧_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    // ─── Private helpers ───────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'customers'    => Customer::active()->orderBy('code')->get(['id', 'name']),
            'employees'    => Employee::active()->orderBy('code')->get(['id', 'name']),
            'statuses'     => Payment::STATUSES,
            'paymentTypes' => Payment::PAYMENT_TYPES,
        ];
    }

    private function calculateTotal(array $items): float
    {
        return round(array_sum(array_column($items, 'amount')), 2);
    }

    private function syncItems(Payment $payment, array $items): void
    {
        $payment->items()->delete();

        foreach ($items as $i => $item) {
            PaymentItem::create([
                'payment_id'   => $payment->id,
                'line_no'      => $i + 1,
                'payment_type' => $item['payment_type'],
                'amount'       => (float) ($item['amount'] ?? 0),
                'bank_info'    => $item['bank_info'] ?: null,
                'remarks'      => $item['remarks'] ?: null,
            ]);
        }
    }
}
