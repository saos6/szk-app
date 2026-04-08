<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Sale;
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
            'recentQuotes' => $recentQuotes,
            'recentSales' => $recentSales,
            'quoteStatuses' => Quote::STATUSES,
            'saleStatuses' => Sale::STATUSES,
        ]);
    }
}
