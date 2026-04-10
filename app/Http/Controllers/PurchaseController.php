<?php

namespace App\Http\Controllers;

use App\Exports\PurchasesExport;
use App\Http\Requests\PurchaseRequest;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'purchase_number');
        $direction = $request->input('direction', 'desc');
        $perPage   = (int) $request->input('per_page', 10);

        $allowedSorts = ['purchase_number', 'purchase_date', 'subject', 'status', 'total_amount', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'purchase_number';
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $purchases = Purchase::active()
            ->with(['supplier:id,name', 'employee:id,name'])
            ->filtered($search, $status)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters'   => compact('search', 'status', 'sort', 'direction') + ['per_page' => (string) $perPage],
            'statuses'  => Purchase::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Purchases/Create', $this->formData());
    }

    public function store(PurchaseRequest $request)
    {
        $validated = $request->validated();

        if ($msg = $this->storeLockMsg($validated['purchase_date'])) {
            return back()->with('error', $msg);
        }

        $items     = $validated['items'];
        unset($validated['items']);

        $validated['purchase_number'] = Purchase::generatePurchaseNumber();
        [$subtotal, $taxAmount, $totalAmount] = $this->calculateTotals($items);
        $validated['subtotal']     = $subtotal;
        $validated['tax_amount']   = $taxAmount;
        $validated['total_amount'] = $totalAmount;

        $purchase = Purchase::create($validated);
        $this->syncItems($purchase, $items);

        InventoryService::applyItems(
            $purchase->purchase_date->format('Y-m'),
            $items,
            'in'
        );

        return redirect()->route('purchases.index')->with('success', '仕入を登録しました。');
    }

    public function replicate(Purchase $purchase): Response
    {
        abort_if($purchase->is_deleted, 404);
        $purchase->load('items');

        $prefill = [
            'supplier_id' => (string) $purchase->supplier_id,
            'employee_id' => $purchase->employee_id ? (string) $purchase->employee_id : '',
            'subject'     => $purchase->subject,
            'remarks'     => $purchase->remarks ?? '',
            'items'       => $purchase->items->map(fn ($item) => [
                'vehicle_id'      => $item->vehicle_id,
                'kisyu_cd'        => $item->kisyu_cd ?? '',
                'frame_no'        => $item->frame_no ?? '',
                'warehouse_code'  => $item->warehouse_code ?? '',
                'iro_cd'          => $item->iro_cd ?? '',
                'kisyu_nm'        => $item->kisyu_nm ?? '',
                'quantity'        => $item->quantity,
                'unit'            => $item->unit ?? '台',
                'sre_tan'         => $item->sre_tan,
                'tax_rate'        => $item->tax_rate,
                'purchase_amount' => (float) $item->purchase_amount,
                'remarks'         => $item->remarks ?? '',
            ])->values()->all(),
        ];

        return Inertia::render('Purchases/Create', array_merge(
            $this->formData(),
            ['prefill' => $prefill]
        ));
    }

    public function show(Purchase $purchase): Response
    {
        abort_if($purchase->is_deleted, 404);
        $purchase->load(['supplier', 'employee', 'items']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
            'statuses' => Purchase::STATUSES,
            'locked'   => $this->lockMsg($purchase) !== null,
        ]);
    }

    public function edit(Purchase $purchase): Response|RedirectResponse
    {
        abort_if($purchase->is_deleted, 404);

        if ($msg = $this->lockMsg($purchase)) {
            return redirect()->route('purchases.show', $purchase)->with('error', $msg);
        }

        $purchase->load('items');

        return Inertia::render('Purchases/Edit', array_merge(
            $this->formData(),
            ['purchase' => $purchase]
        ));
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        abort_if($purchase->is_deleted, 404);

        if ($msg = $this->lockMsg($purchase)) {
            return back()->with('error', $msg);
        }

        // 更新前の在庫巻き戻し用に旧明細と旧年月を取得
        $purchase->load('items');
        $oldYm    = $purchase->purchase_date->format('Y-m');
        $oldItems = $purchase->items->map(fn ($i) => $i->toArray())->toArray();

        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        [$subtotal, $taxAmount, $totalAmount] = $this->calculateTotals($items);
        $validated['subtotal']     = $subtotal;
        $validated['tax_amount']   = $taxAmount;
        $validated['total_amount'] = $totalAmount;

        $purchase->update($validated);
        $this->syncItems($purchase, $items);

        // 旧明細を巻き戻し → 新明細を反映
        $newYm = \Carbon\Carbon::parse($validated['purchase_date'])->format('Y-m');
        InventoryService::reverseItems($oldYm, $oldItems, 'in');
        InventoryService::applyItems($newYm, $items, 'in');

        return redirect()->route('purchases.index')->with('success', '仕入を更新しました。');
    }

    public function destroy(Purchase $purchase)
    {
        abort_if($purchase->is_deleted, 404);

        if ($msg = $this->lockMsg($purchase)) {
            return back()->with('error', $msg);
        }

        // 削除前に在庫を巻き戻す
        $purchase->load('items');
        InventoryService::reverseItems(
            $purchase->purchase_date->format('Y-m'),
            $purchase->items->map(fn ($i) => $i->toArray())->toArray(),
            'in'
        );

        $purchase->is_deleted = true;
        $purchase->save();

        return redirect()->route('purchases.index')->with('success', '仕入を削除しました。');
    }

    public function pdf(Purchase $purchase)
    {
        abort_if($purchase->is_deleted, 404);
        $purchase->load(['supplier', 'employee', 'items']);

        $taxBreakdown = $purchase->items
            ->groupBy('tax_rate')
            ->map(fn ($items) => $items->sum(fn ($i) => round((float) $i->purchase_amount * (int) $i->tax_rate / 100, 0)))
            ->sortKeys();

        $pdf = Pdf::loadView('pdf.purchase', compact('purchase', 'taxBreakdown'))
            ->setPaper('a4', 'portrait');

        $filename = '仕入書_'.$purchase->purchase_number.'.pdf';

        return $pdf->download($filename);
    }

    public function exportMethod(Request $request)
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'purchase_number');
        $direction = $request->input('direction', 'desc');

        return Excel::download(
            new PurchasesExport($search, $status, $sort, $direction),
            '仕入一覧_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /** 仕入: 完了ステータスまたは月次更新済み期間ならロック */
    private function lockMsg(Purchase $purchase): ?string
    {
        if ($purchase->status === 'completed') {
            return 'ステータスが「'.Purchase::STATUSES['completed'].'」の仕入は修正・削除できません。';
        }
        $closingYm = SystemSetting::instance()->closing_ym;
        if ($purchase->purchase_date->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.$purchase->purchase_date->format('Y年n月').'）の仕入は修正・削除できません。';
        }
        return null;
    }

    private function storeLockMsg(string $date): ?string
    {
        $closingYm = SystemSetting::instance()->closing_ym;
        if (Carbon::parse($date)->format('Y-m') <= $closingYm) {
            return '月次更新済みの期間（'.Carbon::parse($date)->format('Y年n月').'）への仕入登録はできません。';
        }
        return null;
    }

    private function formData(): array
    {
        return [
            'suppliers' => Supplier::active()->orderBy('code')->get(['id', 'name']),
            'employees' => Employee::active()->orderBy('code')->get(['id', 'name']),
            'vehicles'  => Vehicle::active()
                ->orderBy('kisyu_cd')
                ->orderBy('frame_no')
                ->get(['id', 'kisyu_cd', 'frame_no', 'iro_cd', 'kisyu_nm', 'sre_tan']),
            'warehouses' => Warehouse::active()->orderBy('code')->get(['code', 'name']),
            'statuses'   => Purchase::STATUSES,
        ];
    }

    private function calculateTotals(array $items): array
    {
        $subtotal  = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $qty  = (float) ($item['quantity'] ?? 0);
            $sre  = (float) ($item['sre_tan'] ?? 0);
            $rate = (int) ($item['tax_rate'] ?? 10);
            $amt  = round($qty * $sre, 2);
            $subtotal  += $amt;
            $taxAmount += round($amt * $rate / 100, 2);
        }

        return [round($subtotal, 2), round($taxAmount, 2), round($subtotal + $taxAmount, 2)];
    }

    private function syncItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->delete();

        foreach ($items as $i => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $sre = (float) ($item['sre_tan'] ?? 0);

            PurchaseItem::create([
                'purchase_id'     => $purchase->id,
                'line_no'         => $i + 1,
                'vehicle_id'      => $item['vehicle_id'] ?: null,
                'kisyu_cd'        => $item['kisyu_cd'] ?? null,
                'frame_no'        => $item['frame_no'] ?? null,
                'warehouse_code'  => $item['warehouse_code'] ?: null,
                'iro_cd'          => $item['iro_cd'] ?? null,
                'kisyu_nm'        => $item['kisyu_nm'] ?? null,
                'quantity'        => $qty,
                'unit'            => $item['unit'] ?? '台',
                'sre_tan'         => $sre,
                'purchase_amount' => round($qty * $sre, 2),
                'tax_rate'        => $item['tax_rate'] ?? '10',
                'remarks'         => $item['remarks'] ?? null,
            ]);
        }
    }
}
