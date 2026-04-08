<?php

namespace App\Http\Controllers;

use App\Exports\BillingBalancesExport;
use App\Http\Requests\BillingBalanceRequest;
use App\Models\BillingBalance;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BillingBalanceController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['billing_date', 'prev_amount', 'sales_amount', 'tax_amount', 'total_amount', 'payment_amount', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'billing_date';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = (string) $request->get('search', '');

        $billingBalances = BillingBalance::with('customer:id,code,name')
            ->active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('BillingBalances/Index', [
            'billingBalances' => $billingBalances,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (string) $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BillingBalances/Create', $this->formData());
    }

    public function replicate(BillingBalance $billingBalance): Response
    {
        abort_if($billingBalance->is_deleted, 404);

        $prefill = $billingBalance->only([
            'customer_id', 'prev_amount', 'sales_amount',
            'tax_amount', 'total_amount', 'payment_amount',
        ]);
        $prefill['customer_id'] = (string) $prefill['customer_id'];

        return Inertia::render('BillingBalances/Create', array_merge(
            $this->formData(),
            ['prefill' => $prefill]
        ));
    }

    public function store(BillingBalanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['total_amount'] = (float) $data['sales_amount'] + (float) $data['tax_amount'];

        BillingBalance::create($data);

        return redirect()->route('billing-balances.index')->with('success', '請求残高を登録しました。');
    }

    public function edit(BillingBalance $billingBalance): Response
    {
        abort_if($billingBalance->is_deleted, 404);

        return Inertia::render('BillingBalances/Edit', array_merge(
            $this->formData(),
            ['billingBalance' => $billingBalance->load('customer')]
        ));
    }

    public function update(BillingBalanceRequest $request, BillingBalance $billingBalance): RedirectResponse
    {
        abort_if($billingBalance->is_deleted, 404);

        $data = $request->validated();
        $data['total_amount'] = (float) $data['sales_amount'] + (float) $data['tax_amount'];

        $billingBalance->update($data);

        return redirect()->route('billing-balances.index')->with('success', '請求残高を更新しました。');
    }

    public function destroy(BillingBalance $billingBalance): RedirectResponse
    {
        abort_if($billingBalance->is_deleted, 404);

        $billingBalance->is_deleted = true;
        $billingBalance->save();

        return redirect()->route('billing-balances.index')->with('success', '請求残高を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new BillingBalancesExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'billing_date')->toString(),
            direction: $request->string('direction', 'desc')->toString(),
        );

        $filename = '請求残高マスタ_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }

    // ─── Private helpers ───────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'customers' => Customer::active()->orderBy('code')->get(['id', 'code', 'name']),
        ];
    }
}
