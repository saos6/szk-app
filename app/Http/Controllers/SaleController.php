<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->input('search', '');
        $status = (string) $request->input('status', '');
        $sort = $request->input('sort', 'sale_number');
        $direction = $request->input('direction', 'desc');
        $perPage = (int) $request->input('per_page', 10);

        $allowedSorts = ['sale_number', 'sale_date', 'delivery_date', 'subject', 'status', 'total_amount', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'sale_number';
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $sales = Sale::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($search, $status)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => compact('search', 'status', 'sort', 'direction') + ['per_page' => (string) $perPage],
            'statuses' => Sale::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Sales/Create', $this->formData());
    }

    public function store(SaleRequest $request)
    {
        $validated = $request->validated();

        if ($msg = $this->storeLockMsg($validated['sale_date'])) {
            return back()->with('error', $msg);
        }

        $items = $validated['items'];
        unset($validated['items']);

        $validated['sale_number'] = Sale::generateSaleNumber();
        [$subtotal, $taxAmount, $totalAmount, $cogsTotal] = $this->calculateTotals($items);
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['cogs_total'] = $cogsTotal;

        $sale = Sale::create($validated);
        $this->syncItems($sale, $items);

        if (! in_array($sale->status, ['draft', 'cancelled'])) {
            InventoryService::applyItems(
                $sale->sale_date->format('Y-m'),
                $items,
                'out'
            );
        }

        return redirect()->route('sales.index')->with('success', '売上を登録しました。');
    }

    public function replicate(Sale $sale): Response
    {
        abort_if($sale->is_deleted, 404);
        $sale->load('items');

        $prefill = [
            'customer_id' => (string) $sale->customer_id,
            'employee_id' => $sale->employee_id ? (string) $sale->employee_id : '',
            'sale_type'   => $sale->sale_type ?? '',
            'subject' => $sale->subject,
            'delivery_date' => $sale->delivery_date?->format('Y-m-d') ?? '',
            'remarks' => $sale->remarks ?? '',
            'items' => $sale->items->map(fn ($item) => [
                'vehicle_id' => $item->vehicle_id,
                'model_code' => $item->model_code ?? '',
                'frame_number' => $item->frame_number ?? '',
                'warehouse_code' => $item->warehouse_code ?? '',
                'color_code' => $item->color_code ?? '',
                'model_name' => $item->model_name ?? '',
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? '台',
                'purchase_price' => $item->purchase_price,
                'selling_price' => $item->selling_price,
                'terminal_price' => $item->terminal_price ?? '',
                'tax_rate' => $item->tax_rate,
                'sale_amount' => (float) $item->sale_amount,
                'cogs_amount' => (float) $item->cogs_amount,
                'remarks' => $item->remarks ?? '',
            ])->values()->all(),
        ];

        return Inertia::render('Sales/Create', array_merge(
            $this->formData(),
            ['prefill' => $prefill]
        ));
    }

    public function show(Sale $sale): Response
    {
        abort_if($sale->is_deleted, 404);
        $sale->load(['customer', 'employee', 'items']);

        return Inertia::render('Sales/Show', [
            'sale'      => $sale,
            'statuses'  => Sale::STATUSES,
            'saleTypes' => Sale::SALE_TYPES,
            'locked'    => $this->lockMsg($sale) !== null,
        ]);
    }

    public function edit(Sale $sale): Response|RedirectResponse
    {
        abort_if($sale->is_deleted, 404);

        if ($msg = $this->lockMsg($sale)) {
            return redirect()->route('sales.show', $sale)->with('error', $msg);
        }

        $sale->load('items');

        return Inertia::render('Sales/Edit', array_merge(
            $this->formData(),
            ['sale' => $sale]
        ));
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        abort_if($sale->is_deleted, 404);

        if ($msg = $this->lockMsg($sale)) {
            return back()->with('error', $msg);
        }

        // 更新前の在庫巻き戻し用に旧明細・旧年月・旧ステータスを取得
        $sale->load('items');
        $oldYm     = $sale->sale_date->format('Y-m');
        $oldStatus = $sale->status;
        $oldItems  = $sale->items->map(fn ($i) => $i->toArray())->toArray();

        $validated = $request->validated();

        if ($oldStatus === 'recorded' && $validated['status'] === 'draft') {
            return back()->with('error', '計上済みの売上は下書きに戻せません。');
        }

        $noInventory = ['draft', 'cancelled'];
        $items = $validated['items'];
        unset($validated['items']);

        [$subtotal, $taxAmount, $totalAmount, $cogsTotal] = $this->calculateTotals($items);
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['cogs_total'] = $cogsTotal;

        $sale->update($validated);
        $this->syncItems($sale, $items);

        // 旧明細を巻き戻し → 新明細を反映（下書き・キャンセルはスキップ）
        $newYm     = \Carbon\Carbon::parse($validated['sale_date'])->format('Y-m');
        $newStatus = $validated['status'];
        if (! in_array($oldStatus, $noInventory)) {
            InventoryService::reverseItems($oldYm, $oldItems, 'out');
        }
        if (! in_array($newStatus, $noInventory)) {
            InventoryService::applyItems($newYm, $items, 'out');
        }

        return redirect()->route('sales.index')->with('success', '売上を更新しました。');
    }

    public function destroy(Sale $sale)
    {
        abort_if($sale->is_deleted, 404);

        if ($msg = $this->lockMsg($sale)) {
            return back()->with('error', $msg);
        }

        // 削除前に在庫を巻き戻す（下書きはスキップ）
        $sale->load('items');
        if ($sale->status !== 'draft') {
            InventoryService::reverseItems(
                $sale->sale_date->format('Y-m'),
                $sale->items->map(fn ($i) => $i->toArray())->toArray(),
                'out'
            );
        }

        $sale->is_deleted = true;
        $sale->save();

        return redirect()->route('sales.index')->with('success', '売上を削除しました。');
    }

    public function pdf(Sale $sale)
    {
        abort_if($sale->is_deleted, 404);
        $sale->load(['customer', 'employee', 'items']);

        // 税率別の消費税額を集計
        $taxBreakdown = $sale->items
            ->groupBy('tax_rate')
            ->map(fn ($items) => $items->sum(fn ($i) => round((float) $i->sale_amount * (int) $i->tax_rate / 100, 0)))
            ->sortKeys();

        $pdf = Pdf::loadView('pdf.sale', compact('sale', 'taxBreakdown'))
            ->setPaper('a4', 'portrait');

        $filename = '納品書_'.$sale->sale_number.'.pdf';

        return $pdf->download($filename);
    }

    public function exportMethod(Request $request)
    {
        $search = (string) $request->input('search', '');
        $status = (string) $request->input('status', '');
        $sort = $request->input('sort', 'sale_number');
        $direction = $request->input('direction', 'desc');

        return Excel::download(
            new SalesExport($search, $status, $sort, $direction),
            '売上一覧_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /** ロック判定（ステータスまたは日付が月次済み）→ エラーメッセージ、問題なければ null */
    private function lockMsg(Sale $sale): ?string
    {
        if (in_array($sale->status, ['completed', 'closed', 'cancelled'])) {
            return 'ステータスが「'.Sale::STATUSES[$sale->status].'」の売上は修正・削除できません。';
        }
        $closingYm = SystemSetting::instance()->closing_ym;
        if ($sale->sale_date->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.$sale->sale_date->format('Y年n月').'）の売上は修正・削除できません。';
        }
        return null;
    }

    /** 登録時の日付ロック判定 */
    private function storeLockMsg(string $date): ?string
    {
        $closingYm = SystemSetting::instance()->closing_ym;
        if (Carbon::parse($date)->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.Carbon::parse($date)->format('Y年n月').'）への売上登録はできません。';
        }
        return null;
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::active()->orderBy('code')->get(['id', 'name']),
            'employees' => Employee::active()->orderBy('code')->get(['id', 'name']),
            'vehicles' => Vehicle::active()
                ->orderBy('model_code')
                ->orderBy('frame_number')
                ->get(['id', 'model_code', 'frame_number', 'color_code', 'model_name', 'purchase_price', 'selling_price', 'terminal_price']),
            'vehicleModels' => VehicleModel::active()->orderBy('model_code')->orderBy('color_code')->get(['model_code', 'color_code', 'model_name', 'purchase_price', 'selling_price', 'terminal_price']),
            'warehouses' => Warehouse::active()->orderBy('code')->get(['code', 'name']),
            'statuses'  => Sale::STATUSES,
            'saleTypes' => Sale::SALE_TYPES,
        ];
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $cogsTotal = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $uri = (float) ($item['selling_price'] ?? 0);
            $sre = (float) ($item['purchase_price'] ?? 0);
            $rate = (int) ($item['tax_rate'] ?? 10);
            $saleAmt = round($qty * $uri, 2);
            $cogsAmt = round($qty * $sre, 2);
            $subtotal += $saleAmt;
            $taxAmount += round($saleAmt * $rate / 100, 2);
            $cogsTotal += $cogsAmt;
        }

        return [round($subtotal, 2), round($taxAmount, 2), round($subtotal + $taxAmount, 2), round($cogsTotal, 2)];
    }

    private function syncItems(Sale $sale, array $items): void
    {
        $sale->items()->delete();
        $sale->loadMissing('customer');

        foreach ($items as $i => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $uri = (float) ($item['selling_price'] ?? 0);
            $sre = (float) ($item['purchase_price'] ?? 0);

            SaleItem::create([
                'sale_id' => $sale->id,
                'line_no' => $i + 1,
                'vehicle_id' => $item['vehicle_id'] ?: null,
                'model_code' => $item['model_code'] ?? null,
                'frame_number' => $item['frame_number'] ?? null,
                'warehouse_code' => $item['warehouse_code'] ?: null,
                'color_code' => $item['color_code'] ?? null,
                'model_name' => $item['model_name'] ?? null,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? '台',
                'purchase_price' => $sre,
                'selling_price' => $uri,
                'terminal_price' => ($item['terminal_price'] ?? '') !== '' ? (float) $item['terminal_price'] : null,
                'tax_rate' => $item['tax_rate'] ?? '10',
                'sale_amount' => round($qty * $uri, 2),
                'cogs_amount' => round($qty * $sre, 2),
                'remarks' => $item['remarks'] ?? null,
            ]);

            $this->syncVehicleFromSaleItem($item, $sale);
        }
    }

    /** 売上明細から車両品番を自動登録・更新 */
    private function syncVehicleFromSaleItem(array $item, Sale $sale): void
    {
        $modelCode = ($item['model_code'] ?? '') ?: null;
        $frameNumber = ($item['frame_number'] ?? '') ?: null;
        if (! $modelCode || ! $frameNumber) {
            return;
        }

        $data = [
            'model_code'    => $modelCode,
            'model_name'    => ($item['model_name'] ?? '') ?: null,
            'color_code'    => ($item['color_code'] ?? '') ?: null,
            'purchase_price' => ($item['purchase_price'] ?? 0) ?: null,
            'selling_price'  => ($item['selling_price'] ?? 0) ?: null,
            'terminal_price' => ($item['terminal_price'] ?? '') !== '' ? (float) $item['terminal_price'] : null,
            'unit'           => ($item['unit'] ?? '') ?: null,
            'shop_name'      => $sale->customer?->name,
            'sale_date'      => $sale->sale_date,
        ];

        $vehicle = Vehicle::where('frame_number', $frameNumber)->where('is_deleted', false)->first();
        if ($vehicle) {
            $vehicle->update($data);
        } else {
            Vehicle::create(array_merge(['frame_number' => $frameNumber], $data));
        }
    }
}
