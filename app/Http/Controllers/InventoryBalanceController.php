<?php

namespace App\Http\Controllers;

use App\Exports\InventoryBalancesExport;
use App\Http\Requests\InventoryBalanceRequest;
use App\Models\InventoryBalance;
use App\Models\VehicleModel;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryBalanceController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['stock_ym', 'warehouse_code', 'model_code', 'frame_number', 'prev_stock', 'in_stock', 'out_stock', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'stock_ym';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search  = (string) $request->get('search', '');
        $ymFrom  = (string) $request->get('ym_from', '');
        $ymTo    = (string) $request->get('ym_to', '');

        $inventoryBalances = InventoryBalance::active()
            ->filtered($search, $ymFrom, $ymTo)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('InventoryBalances/Index', [
            'inventoryBalances' => $inventoryBalances,
            'filters' => [
                'search'    => $search,
                'ym_from'   => $ymFrom,
                'ym_to'     => $ymTo,
                'sort'      => $sort,
                'direction' => $direction,
                'per_page'  => (string) $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('InventoryBalances/Create', $this->formData());
    }

    public function replicate(InventoryBalance $inventoryBalance): Response
    {
        abort_if($inventoryBalance->is_deleted, 404);

        return Inertia::render('InventoryBalances/Create', array_merge(
            $this->formData(),
            [
                'prefill' => $inventoryBalance->only([
                    'warehouse_code', 'model_code', 'frame_number',
                    'prev_stock', 'in_stock', 'out_stock',
                ]),
            ]
        ));
    }

    public function store(InventoryBalanceRequest $request): RedirectResponse
    {
        InventoryBalance::create($request->validated());

        return redirect()->route('inventory-balances.index')->with('success', '在庫残高を登録しました。');
    }

    public function show(InventoryBalance $inventoryBalance): Response
    {
        abort_if($inventoryBalance->is_deleted, 404);

        return Inertia::render('InventoryBalances/Show', [
            'inventoryBalance' => $inventoryBalance,
        ]);
    }

    public function edit(InventoryBalance $inventoryBalance): Response
    {
        abort_if($inventoryBalance->is_deleted, 404);

        return Inertia::render('InventoryBalances/Edit', array_merge(
            $this->formData(),
            ['inventoryBalance' => $inventoryBalance]
        ));
    }

    public function update(InventoryBalanceRequest $request, InventoryBalance $inventoryBalance): RedirectResponse
    {
        abort_if($inventoryBalance->is_deleted, 404);

        $inventoryBalance->update($request->validated());

        return redirect()->route('inventory-balances.index')->with('success', '在庫残高を更新しました。');
    }

    public function destroy(InventoryBalance $inventoryBalance): RedirectResponse
    {
        abort_if($inventoryBalance->is_deleted, 404);

        $inventoryBalance->is_deleted = true;
        $inventoryBalance->save();

        return redirect()->route('inventory-balances.index')->with('success', '在庫残高を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new InventoryBalancesExport(
            search:    $request->string('search')->toString(),
            ymFrom:    $request->string('ym_from')->toString(),
            ymTo:      $request->string('ym_to')->toString(),
            sort:      $request->string('sort', 'stock_ym')->toString(),
            direction: $request->string('direction', 'desc')->toString(),
        );

        $filename = '在庫残高_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }

    private function formData(): array
    {
        return [
            'warehouses' => Warehouse::active()->orderBy('code')->get(['code', 'name']),
            'vehicleModels' => VehicleModel::active()
                ->selectRaw('model_code, MAX(model_name_kanji) as model_name_kanji')
                ->groupBy('model_code')
                ->orderBy('model_code')
                ->get(),
        ];
    }
}
