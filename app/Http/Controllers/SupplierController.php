<?php

namespace App\Http\Controllers;

use App\Exports\SuppliersExport;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'phone', 'contact_person', 'payment_site', 'created_at', 'updated_at'];
        $sort      = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage   = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search    = $request->get('search', '');

        $suppliers = Supplier::active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters'   => [
                'search'    => $search,
                'sort'      => $sort,
                'direction' => $direction,
                'per_page'  => (string) $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Suppliers/Create');
    }

    public function replicate(Supplier $supplier): Response
    {
        abort_if($supplier->is_deleted, 404);

        $prefill = $supplier->only([
            'name', 'name_kana', 'postal_code', 'address',
            'phone', 'fax', 'email', 'contact_person', 'payment_site', 'remarks',
        ]);

        return Inertia::render('Suppliers/Create', ['prefill' => $prefill]);
    }

    public function show(Supplier $supplier): Response
    {
        abort_if($supplier->is_deleted, 404);

        return Inertia::render('Suppliers/Show', ['supplier' => $supplier]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', '仕入先を登録しました。');
    }

    public function edit(Supplier $supplier): Response
    {
        abort_if($supplier->is_deleted, 404);

        return Inertia::render('Suppliers/Edit', ['supplier' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', '仕入先を更新しました。');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->is_deleted = true;
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', '仕入先を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new SuppliersExport(
            search:    $request->string('search')->toString(),
            sort:      $request->string('sort', 'code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '仕入先マスタ_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }
}
