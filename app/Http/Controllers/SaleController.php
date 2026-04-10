<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        InventoryService::applyItems(
            $sale->sale_date->format('Y-m'),
            $items,
            'out'
        );

        return redirect()->route('sales.index')->with('success', '売上を登録しました。');
    }

    public function replicate(Sale $sale): Response
    {
        abort_if($sale->is_deleted, 404);
        $sale->load('items');

        $prefill = [
            'customer_id' => (string) $sale->customer_id,
            'employee_id' => $sale->employee_id ? (string) $sale->employee_id : '',
            'subject' => $sale->subject,
            'delivery_date' => $sale->delivery_date?->format('Y-m-d') ?? '',
            'remarks' => $sale->remarks ?? '',
            'items' => $sale->items->map(fn ($item) => [
                'vehicle_id' => $item->vehicle_id,
                'kisyu_cd' => $item->kisyu_cd ?? '',
                'frame_no' => $item->frame_no ?? '',
                'warehouse_code' => $item->warehouse_code ?? '',
                'iro_cd' => $item->iro_cd ?? '',
                'kisyu_nm' => $item->kisyu_nm ?? '',
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? '台',
                'sre_tan' => $item->sre_tan,
                'uri_tan' => $item->uri_tan,
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
            'sale' => $sale,
            'statuses' => Sale::STATUSES,
        ]);
    }

    public function edit(Sale $sale): Response
    {
        abort_if($sale->is_deleted, 404);
        $sale->load('items');

        return Inertia::render('Sales/Edit', array_merge(
            $this->formData(),
            ['sale' => $sale]
        ));
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        abort_if($sale->is_deleted, 404);

        // 更新前の在庫巻き戻し用に旧明細と旧年月を取得
        $sale->load('items');
        $oldYm    = $sale->sale_date->format('Y-m');
        $oldItems = $sale->items->map(fn ($i) => $i->toArray())->toArray();

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        [$subtotal, $taxAmount, $totalAmount, $cogsTotal] = $this->calculateTotals($items);
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['cogs_total'] = $cogsTotal;

        $sale->update($validated);
        $this->syncItems($sale, $items);

        // 旧明細を巻き戻し → 新明細を反映
        $newYm = \Carbon\Carbon::parse($validated['sale_date'])->format('Y-m');
        InventoryService::reverseItems($oldYm, $oldItems, 'out');
        InventoryService::applyItems($newYm, $items, 'out');

        return redirect()->route('sales.index')->with('success', '売上を更新しました。');
    }

    public function destroy(Sale $sale)
    {
        abort_if($sale->is_deleted, 404);

        // 削除前に在庫を巻き戻す
        $sale->load('items');
        InventoryService::reverseItems(
            $sale->sale_date->format('Y-m'),
            $sale->items->map(fn ($i) => $i->toArray())->toArray(),
            'out'
        );

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

    private function formData(): array
    {
        return [
            'customers' => Customer::active()->orderBy('code')->get(['id', 'name']),
            'employees' => Employee::active()->orderBy('code')->get(['id', 'name']),
            'vehicles' => Vehicle::active()
                ->orderBy('kisyu_cd')
                ->orderBy('frame_no')
                ->get(['id', 'kisyu_cd', 'frame_no', 'iro_cd', 'kisyu_nm', 'sre_tan', 'uri_tan']),
            'warehouses' => Warehouse::active()->orderBy('code')->get(['code', 'name']),
            'statuses' => Sale::STATUSES,
        ];
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $cogsTotal = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $uri = (float) ($item['uri_tan'] ?? 0);
            $sre = (float) ($item['sre_tan'] ?? 0);
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

        foreach ($items as $i => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $uri = (float) ($item['uri_tan'] ?? 0);
            $sre = (float) ($item['sre_tan'] ?? 0);

            SaleItem::create([
                'sale_id' => $sale->id,
                'line_no' => $i + 1,
                'vehicle_id' => $item['vehicle_id'] ?: null,
                'kisyu_cd' => $item['kisyu_cd'] ?? null,
                'frame_no' => $item['frame_no'] ?? null,
                'warehouse_code' => $item['warehouse_code'] ?: null,
                'iro_cd' => $item['iro_cd'] ?? null,
                'kisyu_nm' => $item['kisyu_nm'] ?? null,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? '台',
                'sre_tan' => $sre,
                'uri_tan' => $uri,
                'tax_rate' => $item['tax_rate'] ?? '10',
                'sale_amount' => round($qty * $uri, 2),
                'cogs_amount' => round($qty * $sre, 2),
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }
}
