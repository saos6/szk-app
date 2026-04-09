<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $thisMonth = now()->startOfMonth();

        // ─── 見積 ──────────────────────────────────────────────────
        $quoteStatusCounts = Quote::active()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $thisMonthQuoteStats = Quote::active()
            ->where('quote_date', '>=', $thisMonth)
            ->selectRaw('count(*) as count, coalesce(sum(total_amount), 0) as total')
            ->first();

        $thisMonthAccepted = Quote::active()
            ->where('status', 'accepted')
            ->where('quote_date', '>=', $thisMonth)
            ->sum('total_amount');

        $recentQuotes = Quote::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->orderByDesc('quote_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'quote_number', 'customer_id', 'employee_id', 'quote_date', 'subject', 'status', 'total_amount']);

        // ─── 売上 ──────────────────────────────────────────────────
        $saleStatusCounts = Sale::active()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $thisMonthSaleStats = Sale::active()
            ->where('sale_date', '>=', $thisMonth)
            ->selectRaw('count(*) as count, coalesce(sum(total_amount), 0) as total, coalesce(sum(cogs_total), 0) as cogs')
            ->first();

        // 未納品件数（下書き or 請求済み以外で納品が完了していないもの）
        $pendingDeliveryCount = Sale::active()
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->count();

        $recentSales = Sale::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'sale_number', 'customer_id', 'employee_id', 'sale_date', 'subject', 'status', 'total_amount', 'cogs_total']);

        // ─── グラフ用データ ────────────────────────────────────────────
        $thisYear = now()->startOfYear();

        // 月別売上推移（過去12ヶ月）
        $monthlyRaw = Sale::active()
            ->where('sale_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', sale_date) as month, coalesce(sum(total_amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthlySales = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = now()->subMonths($i)->format('Y-m');
            $monthlySales[] = [
                'month' => $ym,
                'total' => (float) ($monthlyRaw[$ym] ?? 0),
            ];
        }

        // 得意先別売上 TOP8（今年度）
        $topCustomers = Sale::query()
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.is_deleted', false)
            ->where('sales.sale_date', '>=', $thisYear)
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(8)
            ->selectRaw('customers.name, coalesce(sum(sales.total_amount), 0) as total')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => (float) $r->total])
            ->values();

        // 商品別売上 TOP8（今年度）
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.is_deleted', false)
            ->where('sales.sale_date', '>=', $thisYear)
            ->whereNotNull('sale_items.kisyu_nm')
            ->where('sale_items.kisyu_nm', '!=', '')
            ->groupBy('sale_items.kisyu_nm')
            ->orderByDesc('total')
            ->limit(8)
            ->selectRaw('sale_items.kisyu_nm as name, coalesce(sum(sale_items.sale_amount), 0) as total')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => (float) $r->total])
            ->values();

        // ─── 入金 ──────────────────────────────────────────────────
        $paymentStatusCounts = Payment::active()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $thisMonthPaymentStats = Payment::active()
            ->where('payment_date', '>=', $thisMonth)
            ->selectRaw('count(*) as count, coalesce(sum(total_amount), 0) as total')
            ->first();

        $recentPayments = Payment::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'payment_number', 'customer_id', 'employee_id', 'payment_date', 'subject', 'status', 'total_amount']);

        return Inertia::render('Dashboard', [
            'quoteStats' => [
                'thisMonthCount' => (int) $thisMonthQuoteStats->count,
                'thisMonthTotal' => (float) $thisMonthQuoteStats->total,
                'thisMonthAccepted' => (float) $thisMonthAccepted,
                'sentCount' => (int) ($quoteStatusCounts['sent'] ?? 0),
                'acceptedCount' => (int) ($quoteStatusCounts['accepted'] ?? 0),
                'draftCount' => (int) ($quoteStatusCounts['draft'] ?? 0),
                'totalCount' => array_sum($quoteStatusCounts),
            ],
            'saleStats' => [
                'thisMonthCount' => (int) $thisMonthSaleStats->count,
                'thisMonthTotal' => (float) $thisMonthSaleStats->total,
                'thisMonthCogs' => (float) $thisMonthSaleStats->cogs,
                'thisMonthGrossProfit' => (float) $thisMonthSaleStats->total - (float) $thisMonthSaleStats->cogs,
                'pendingDeliveryCount' => (int) $pendingDeliveryCount,
                'completedCount' => (int) ($saleStatusCounts['completed'] ?? 0),
                'totalCount' => array_sum($saleStatusCounts),
            ],
            'monthlySales' => $monthlySales,
            'topCustomers' => $topCustomers,
            'topProducts' => $topProducts,
            'recentQuotes' => $recentQuotes,
            'recentSales' => $recentSales,
            'recentPayments' => $recentPayments,
            'quoteStatuses' => Quote::STATUSES,
            'saleStatuses' => Sale::STATUSES,
            'paymentStatuses' => Payment::STATUSES,
            'paymentStats' => [
                'thisMonthCount' => (int) $thisMonthPaymentStats->count,
                'thisMonthTotal' => (float) $thisMonthPaymentStats->total,
                'confirmedCount' => (int) ($paymentStatusCounts['confirmed'] ?? 0),
                'draftCount' => (int) ($paymentStatusCounts['draft'] ?? 0),
                'totalCount' => array_sum($paymentStatusCounts),
            ],
        ]);
    }
}
