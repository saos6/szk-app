<?php

namespace App\Http\Controllers;

use App\Exports\PaymentsExport;
use App\Http\Requests\PaymentRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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

        if ($msg = $this->storeLockMsg($validated['payment_date'])) {
            return back()->with('error', $msg);
        }

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
            'locked'       => $this->lockMsg($payment) !== null,
        ]);
    }

    public function edit(Payment $payment): Response|RedirectResponse
    {
        abort_if($payment->is_deleted, 404);

        if ($msg = $this->lockMsg($payment)) {
            return redirect()->route('payments.show', $payment)->with('error', $msg);
        }

        $payment->load('items');

        return Inertia::render('Payments/Edit', array_merge(
            $this->formData(),
            ['payment' => $payment]
        ));
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        abort_if($payment->is_deleted, 404);

        if ($msg = $this->lockMsg($payment)) {
            return back()->with('error', $msg);
        }

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

        if ($msg = $this->lockMsg($payment)) {
            return back()->with('error', $msg);
        }

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

    /** 入金: 請求完了/完了ステータスまたは月次更新済み期間ならロック */
    private function lockMsg(Payment $payment): ?string
    {
        if (in_array($payment->status, ['completed', 'closed'])) {
            return 'ステータスが「'.Payment::STATUSES[$payment->status].'」の入金は修正・削除できません。';
        }
        $closingYm = SystemSetting::instance()->closing_ym;
        if (Carbon::parse($payment->payment_date)->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.Carbon::parse($payment->payment_date)->format('Y年n月').'）の入金は修正・削除できません。';
        }
        return null;
    }

    private function storeLockMsg(string $date): ?string
    {
        $closingYm = SystemSetting::instance()->closing_ym;
        if (Carbon::parse($date)->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.Carbon::parse($date)->format('Y年n月').'）への入金登録はできません。';
        }
        return null;
    }

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
