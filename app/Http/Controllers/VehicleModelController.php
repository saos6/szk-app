<?php

namespace App\Http\Controllers;

use App\Exports\VehicleModelsExport;
use App\Http\Requests\VehicleModelRequest;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VehicleModelController extends Controller
{
    private function enums(): array
    {
        return [
            'zeiKbn' => VehicleModel::ZEI_KBN,
            'g1Types' => VehicleModel::G1_TYPES,
            'g2Disp' => VehicleModel::G2_DISP,
            'g3Options' => VehicleModel::G3_OPTIONS,
            'g4Options' => VehicleModel::G4_OPTIONS,
            'g5Options' => VehicleModel::G5_OPTIONS,
        ];
    }

    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'model_code', 'color_code', 'model_name', 'model_abbr', 'model_name_kanji', 'base_model', 'purchase_price', 'selling_price', 'g1', 'g2', 'tax_type', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'model_code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $vehicleModels = VehicleModel::active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('VehicleModels/Index', array_merge([
            'vehicleModels' => $vehicleModels,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (string) $perPage,
            ],
        ], $this->enums()));
    }

    public function create(): Response
    {
        return Inertia::render('VehicleModels/Create', $this->enums());
    }

    public function replicate(VehicleModel $vehicleModel): Response
    {
        abort_if($vehicleModel->is_deleted, 404);

        $prefill = $vehicleModel->only([
            'model_name', 'model_abbr', 'base_model', 'model_name_kanji',
            'purchase_price', 'selling_price', 'terminal_price', 'standard_retail_price',
            'g1', 'g2', 'g3', 'g4', 'g5', 'order_number', 'tax_type',
        ]);

        return Inertia::render('VehicleModels/Create', array_merge(
            ['prefill' => $prefill],
            $this->enums(),
        ));
    }

    public function show(VehicleModel $vehicleModel): Response
    {
        abort_if($vehicleModel->is_deleted, 404);

        return Inertia::render('VehicleModels/Show', array_merge(
            ['vehicleModel' => $vehicleModel],
            $this->enums(),
        ));
    }

    public function store(VehicleModelRequest $request): RedirectResponse
    {
        VehicleModel::create($request->validated());

        return redirect()->route('vehicle-models.index')->with('success', '車両機種を登録しました。');
    }

    public function edit(VehicleModel $vehicleModel): Response
    {
        return Inertia::render('VehicleModels/Edit', array_merge(
            ['vehicleModel' => $vehicleModel],
            $this->enums(),
        ));
    }

    public function update(VehicleModelRequest $request, VehicleModel $vehicleModel): RedirectResponse
    {
        $vehicleModel->update($request->validated());

        return redirect()->route('vehicle-models.index')->with('success', '車両機種を更新しました。');
    }

    public function destroy(VehicleModel $vehicleModel): RedirectResponse
    {
        $vehicleModel->is_deleted = true;
        $vehicleModel->save();

        return redirect()->route('vehicle-models.index')->with('success', '車両機種を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new VehicleModelsExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'model_code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '機種商品_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }
}
