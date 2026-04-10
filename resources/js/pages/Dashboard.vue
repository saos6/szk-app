<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);
import {
    FileText,
    TrendingUp,
    Send,
    CheckCircle,
    ShoppingCart,
    Banknote,
    PackageCheck,
    TrendingDown,
    CreditCard,
    CircleCheck,
} from 'lucide-vue-next';
import * as QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import * as SaleController from '@/actions/App/Http/Controllers/SaleController';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';

interface RecentQuote {
    id: number;
    quote_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    quote_date: string;
    subject: string;
    status: string;
    total_amount: string;
}

interface RecentSale {
    id: number;
    sale_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    sale_date: string;
    subject: string;
    status: string;
    total_amount: string;
    cogs_total: string;
}

interface RecentPayment {
    id: number;
    payment_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    payment_date: string;
    subject: string;
    status: string;
    total_amount: string;
}

const props = defineProps<{
    quoteStats: {
        thisMonthCount: number;
        thisMonthTotal: number;
        thisMonthAccepted: number;
        sentCount: number;
        acceptedCount: number;
        draftCount: number;
        totalCount: number;
    };
    saleStats: {
        thisMonthCount: number;
        thisMonthTotal: number;
        thisMonthCogs: number;
        thisMonthGrossProfit: number;
        pendingDeliveryCount: number;
        completedCount: number;
        totalCount: number;
    };
    recentQuotes: RecentQuote[];
    recentSales: RecentSale[];
    recentPayments: RecentPayment[];
    monthlySales: { month: string; total: number }[];
    topCustomers: { name: string; total: number }[];
    topProducts: { name: string; total: number }[];
    quoteStatuses: Record<string, string>;
    saleStatuses: Record<string, string>;
    paymentStatuses: Record<string, string>;
    paymentStats: {
        thisMonthCount: number;
        thisMonthTotal: number;
        confirmedCount: number;
        draftCount: number;
        totalCount: number;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const QUOTE_STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    draft:     'secondary',
    sent:      'outline',
    accepted:  'default',
    rejected:  'destructive',
    expired:   'secondary',
};

const SALE_STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    draft:     'secondary',
    recorded:  'outline',
    invoiced:  'default',
    completed: 'default',
    closed:    'default',
    cancelled: 'destructive',
};

const PAYMENT_STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    draft:     'secondary',
    recorded:  'outline',
    confirmed: 'default',
    completed: 'default',
    closed:    'default',
    cancelled: 'destructive',
};

function fmtAmount(val: number): string {
    return '¥' + val.toLocaleString('ja-JP');
}

function fmtDate(val: string): string {
    return new Date(val).toLocaleDateString('ja-JP');
}

const thisMonth = new Date().toLocaleDateString('ja-JP', {
    year: 'numeric',
    month: 'long',
});

const thisYear = new Date().getFullYear();

// ─── Chart.js ────────────────────────────────────────────────────────
function monthLabel(ym: string): string {
    const [y, m] = ym.split('-');
    return Number(y) === thisYear ? `${parseInt(m)}月` : `${y}/${parseInt(m)}`;
}

const CHART_COLORS = [
    'rgba(59,130,246,0.75)',
    'rgba(16,185,129,0.75)',
    'rgba(245,158,11,0.75)',
    'rgba(239,68,68,0.75)',
    'rgba(168,85,247,0.75)',
    'rgba(20,184,166,0.75)',
    'rgba(249,115,22,0.75)',
    'rgba(236,72,153,0.75)',
];

const monthlyChartData = computed(() => ({
    labels: props.monthlySales.map((d) => monthLabel(d.month)),
    datasets: [
        {
            label: '売上金額',
            data: props.monthlySales.map((d) => d.total),
            backgroundColor: 'rgba(59,130,246,0.7)',
            borderColor: 'rgba(59,130,246,1)',
            borderWidth: 1,
            borderRadius: 4,
        },
    ],
}));

const customerChartData = computed(() => ({
    labels: props.topCustomers.map((d) => d.name),
    datasets: [
        {
            label: '売上金額',
            data: props.topCustomers.map((d) => d.total),
            backgroundColor: props.topCustomers.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]),
            borderRadius: 4,
        },
    ],
}));

const productChartData = computed(() => ({
    labels: props.topProducts.map((d) => d.name),
    datasets: [
        {
            label: '売上金額',
            data: props.topProducts.map((d) => d.total),
            backgroundColor: props.topProducts.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]),
            borderRadius: 4,
        },
    ],
}));

const yTickCallback = (val: unknown) => '¥' + Number(val).toLocaleString('ja-JP');
const tooltipCallback = { label: (ctx: { raw: unknown }) => ' ¥' + Number(ctx.raw).toLocaleString('ja-JP') };

const monthlyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { callbacks: tooltipCallback } },
    scales: { y: { ticks: { callback: yTickCallback } } },
} as const;

const horizontalChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y' as const,
    plugins: { legend: { display: false }, tooltip: { callbacks: tooltipCallback } },
    scales: { x: { ticks: { callback: yTickCallback } } },
} as const;
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4">

        <!-- ─── 売上KPI ─── -->
        <div>
            <p class="mb-3 text-sm font-medium text-muted-foreground">
                {{ thisMonth }}の売上実績
            </p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- 今月の売上件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の売上件数</p>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ saleStats.thisMonthCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        累計 {{ saleStats.totalCount }} 件
                    </p>
                </div>

                <!-- 今月の売上金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の売上金額（税込）</p>
                        <Banknote class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums">
                        {{ fmtAmount(saleStats.thisMonthTotal) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        仕入 {{ fmtAmount(saleStats.thisMonthCogs) }}
                    </p>
                </div>

                <!-- 今月の粗利 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の粗利</p>
                        <TrendingUp class="h-4 w-4 text-green-500" />
                    </div>
                    <p
                        class="mt-2 text-2xl font-bold tabular-nums"
                        :class="
                            saleStats.thisMonthGrossProfit >= 0
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-destructive'
                        "
                    >
                        {{ fmtAmount(saleStats.thisMonthGrossProfit) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        完了 {{ saleStats.completedCount }} 件（全期間）
                    </p>
                </div>

                <!-- 請求未確定件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">請求未確定件数</p>
                        <PackageCheck class="h-4 w-4 text-amber-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ saleStats.pendingDeliveryCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        納品・完了以外のステータス
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── 入金KPI ─── -->
        <div>
            <p class="mb-3 text-sm font-medium text-muted-foreground">
                {{ thisMonth }}の入金実績
            </p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- 今月の入金件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の入金件数</p>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ paymentStats.thisMonthCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        累計 {{ paymentStats.totalCount }} 件
                    </p>
                </div>

                <!-- 今月の入金金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の入金金額</p>
                        <Banknote class="h-4 w-4 text-blue-500" />
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-blue-600 dark:text-blue-400">
                        {{ fmtAmount(paymentStats.thisMonthTotal) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">&nbsp;</p>
                </div>

                <!-- 確認済み件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">確認済み件数</p>
                        <CircleCheck class="h-4 w-4 text-green-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ paymentStats.confirmedCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        下書き {{ paymentStats.draftCount }} 件
                    </p>
                </div>

                <!-- 空カード（レイアウト揃え） -->
                <div class="rounded-xl border bg-card p-5 flex items-center justify-center">
                    <Link
                        :href="PaymentController.index.url()"
                        class="text-sm text-primary hover:underline"
                    >入金一覧を見る →</Link>
                </div>
            </div>
        </div>

        <!-- ─── 見積KPI ─── -->
        <div>
            <p class="mb-3 text-sm font-medium text-muted-foreground">
                {{ thisMonth }}の見積実績
            </p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- 今月の見積件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の見積件数</p>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ quoteStats.thisMonthCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        累計 {{ quoteStats.totalCount }} 件
                    </p>
                </div>

                <!-- 今月の見積金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の見積金額</p>
                        <TrendingDown class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums">
                        {{ fmtAmount(quoteStats.thisMonthTotal) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">&nbsp;</p>
                </div>

                <!-- 今月の受注金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の受注金額</p>
                        <CheckCircle class="h-4 w-4 text-green-500" />
                    </div>
                    <p
                        class="mt-2 text-2xl font-bold text-green-600 tabular-nums dark:text-green-400"
                    >
                        {{ fmtAmount(quoteStats.thisMonthAccepted) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        受注 {{ quoteStats.acceptedCount }} 件（全期間）
                    </p>
                </div>

                <!-- 送付済み（返答待ち） -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">送付済み（返答待ち）</p>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">
                        {{ quoteStats.sentCount
                        }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        下書き {{ quoteStats.draftCount }} 件
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── グラフ ─── -->
        <div class="grid gap-6 lg:grid-cols-2">

            <!-- 月別売上推移 -->
            <div class="rounded-xl border bg-card p-5 lg:col-span-2">
                <h2 class="mb-4 font-semibold">月別売上推移（過去12ヶ月）</h2>
                <div class="h-56">
                    <Bar :data="monthlyChartData" :options="monthlyChartOptions" />
                </div>
            </div>

            <!-- 得意先別売上 -->
            <div class="rounded-xl border bg-card p-5">
                <h2 class="mb-1 font-semibold">得意先別売上 TOP8</h2>
                <p class="mb-4 text-xs text-muted-foreground">{{ thisYear }}年度</p>
                <div v-if="topCustomers.length" class="h-64">
                    <Bar :data="customerChartData" :options="horizontalChartOptions" />
                </div>
                <p v-else class="flex h-64 items-center justify-center text-sm text-muted-foreground">
                    データがありません
                </p>
            </div>

            <!-- 商品別売上 -->
            <div class="rounded-xl border bg-card p-5">
                <h2 class="mb-1 font-semibold">商品別売上 TOP8</h2>
                <p class="mb-4 text-xs text-muted-foreground">{{ thisYear }}年度</p>
                <div v-if="topProducts.length" class="h-64">
                    <Bar :data="productChartData" :options="horizontalChartOptions" />
                </div>
                <p v-else class="flex h-64 items-center justify-center text-sm text-muted-foreground">
                    データがありません
                </p>
            </div>

        </div>

        <!-- ─── 下段テーブル ─── -->
        <div class="grid gap-6 lg:grid-cols-3">

            <!-- 直近の売上 -->
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <h2 class="font-semibold">直近の売上</h2>
                    <Link
                        :href="SaleController.index.url()"
                        class="text-sm text-primary hover:underline"
                        >すべて見る →</Link
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">売上番号</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">売上日</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">ステータス</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">税込合計</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sale in recentSales"
                                :key="sale.id"
                                class="border-t transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-2.5">
                                    <Link
                                        :href="SaleController.edit.url(sale.id)"
                                        class="font-mono text-primary hover:underline"
                                    >
                                        {{ sale.sale_number }}
                                    </Link>
                                </td>
                                <td class="max-w-[120px] truncate px-4 py-2.5">
                                    {{ sale.customer?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-muted-foreground">
                                    {{ fmtDate(sale.sale_date) }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <Badge
                                        :variant="SALE_STATUS_VARIANT[sale.status] ?? 'secondary'"
                                        class="text-xs"
                                    >
                                        {{ saleStatuses[sale.status] }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    {{ fmtAmount(Number(sale.total_amount)) }}
                                </td>
                            </tr>
                            <tr v-if="recentSales.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    データがありません
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 直近の入金 -->
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <h2 class="font-semibold">直近の入金</h2>
                    <Link
                        :href="PaymentController.index.url()"
                        class="text-sm text-primary hover:underline"
                        >すべて見る →</Link
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">入金番号</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">入金日</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">ステータス</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">合計金額</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="payment in recentPayments"
                                :key="payment.id"
                                class="border-t transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-2.5">
                                    <Link
                                        :href="PaymentController.show.url(payment.id)"
                                        class="font-mono text-primary hover:underline"
                                    >
                                        {{ payment.payment_number }}
                                    </Link>
                                </td>
                                <td class="max-w-[120px] truncate px-4 py-2.5">
                                    {{ payment.customer?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-muted-foreground">
                                    {{ fmtDate(payment.payment_date) }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <Badge
                                        :variant="PAYMENT_STATUS_VARIANT[payment.status] ?? 'secondary'"
                                        class="text-xs"
                                    >
                                        {{ paymentStatuses[payment.status] }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    {{ fmtAmount(Number(payment.total_amount)) }}
                                </td>
                            </tr>
                            <tr v-if="recentPayments.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    データがありません
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 直近の見積 -->
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <h2 class="font-semibold">直近の見積</h2>
                    <Link
                        :href="QuoteController.index.url()"
                        class="text-sm text-primary hover:underline"
                        >すべて見る →</Link
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">見積番号</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">見積日</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">ステータス</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">合計金額</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="quote in recentQuotes"
                                :key="quote.id"
                                class="border-t transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-2.5">
                                    <Link
                                        :href="QuoteController.show.url(quote.id)"
                                        class="font-mono text-primary hover:underline"
                                    >
                                        {{ quote.quote_number }}
                                    </Link>
                                </td>
                                <td class="max-w-[120px] truncate px-4 py-2.5">
                                    {{ quote.customer?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-muted-foreground">
                                    {{ fmtDate(quote.quote_date) }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <Badge
                                        :variant="QUOTE_STATUS_VARIANT[quote.status] ?? 'secondary'"
                                        class="text-xs"
                                    >
                                        {{ quoteStatuses[quote.status] }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    {{ fmtAmount(Number(quote.total_amount)) }}
                                </td>
                            </tr>
                            <tr v-if="recentQuotes.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    データがありません
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</template>
