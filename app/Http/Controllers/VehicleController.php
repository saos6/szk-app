<?php

namespace App\Http\Controllers;

use App\Exports\VehiclesExport;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VehicleController extends Controller
{
    private function formProps(): array
    {
        return [
            'vehicleModelList' => VehicleModel::active()
                ->orderBy('model_code')
                ->orderBy('color_code')
                ->get(['model_code', 'color_code', 'model_name_kanji'])
                ->toArray(),
            'genders' => Vehicle::GENDERS,
        ];
    }

    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'model_code', 'frame_number', 'model_name', 'vehicle_no', 'owner_name', 'shop_name', 'sale_date', 'first_reg_date', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'model_code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $vehicles = Vehicle::active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (string) $perPage,
            ],
            'genders' => Vehicle::GENDERS,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vehicles/Create', $this->formProps());
    }

    public function replicate(Vehicle $vehicle): Response
    {
        abort_if($vehicle->is_deleted, 404);

        $prefill = $vehicle->only([
            'model_code', 'name1', 'name2', 'model_name', 'model_type', 'model_number',
            'color_code', 'purchase_price', 'selling_price', 'terminal_price', 'standard_retail_price', 'maker_code', 'unit',
            'note1', 'note2', 'note3', 'shop_name',
        ]);

        return Inertia::render('Vehicles/Create', array_merge(
            $this->formProps(),
            ['prefill' => $prefill],
        ));
    }

    public function show(Vehicle $vehicle): Response
    {
        abort_if($vehicle->is_deleted, 404);

        return Inertia::render('Vehicles/Show', [
            'vehicle' => $vehicle,
            'genders' => Vehicle::GENDERS,
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        Vehicle::create($request->validated());

        return redirect()->route('vehicles.index')->with('success', '車両を登録しました。');
    }

    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('Vehicles/Edit', array_merge(
            $this->formProps(),
            ['vehicle' => $vehicle],
        ));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')->with('success', '車両を更新しました。');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->is_deleted = true;
        $vehicle->save();

        return redirect()->route('vehicles.index')->with('success', '車両を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new VehiclesExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'model_code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '車両品番_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }
}
