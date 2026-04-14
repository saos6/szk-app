<?php

namespace App\Http\Controllers;

use App\Exports\WarehousesExport;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WarehouseController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['code', 'name', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $warehouses = Warehouse::active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses,
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
        return Inertia::render('Warehouses/Create');
    }

    public function replicate(Warehouse $warehouse): Response
    {
        abort_if($warehouse->is_deleted, 404);

        return Inertia::render('Warehouses/Create', [
            'prefill' => [
                'name' => $warehouse->name,
            ],
        ]);
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        Warehouse::create($request->validated());

        return redirect()->route('warehouses.index')->with('success', '倉庫を登録しました。');
    }

    public function show(Warehouse $warehouse): Response
    {
        abort_if($warehouse->is_deleted, 404);

        return Inertia::render('Warehouses/Show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function edit(Warehouse $warehouse): Response
    {
        abort_if($warehouse->is_deleted, 404);

        return Inertia::render('Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return redirect()->route('warehouses.index')->with('success', '倉庫を更新しました。');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->is_deleted = true;
        $warehouse->save();

        return redirect()->route('warehouses.index')->with('success', '倉庫を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new WarehousesExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '倉庫_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }
}
