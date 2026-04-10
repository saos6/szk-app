<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, FileDown, Pencil } from 'lucide-vue-next';
import * as SaleController from '@/actions/App/Http/Controllers/SaleController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface SaleItem {
    id: number;
    line_no: number;
    kisyu_cd: string | null;
    frame_no: string | null;
    warehouse_code: string | null;
    iro_cd: string | null;
    kisyu_nm: string | null;
    quantity: string;
    unit: string | null;
    sre_tan: string;
    uri_tan: string;
    tax_rate: string;
    sale_amount: string;
    cogs_amount: string;
    remarks: string | null;
}

interface Sale {
    id: number;
    sale_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    sale_date: string;
    delivery_date: string | null;
    subject: string;
    status: string;
    subtotal: string;
    tax_amount: string;
    total_amount: string;
    cogs_total: string;
    remarks: string | null;
    items: SaleItem[];
}

const props = defineProps<{
    sale: Sale;
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '売上', href: SaleController.index.url() },
    {
        title: props.sale.sale_number,
        href: SaleController.show.url(props.sale.id),
    },
];

const STATUS_VARIANT: Record<
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

function fmt(val: string | number): string {
    return '¥' + Number(val).toLocaleString('ja-JP');
}

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`売上 ${sale.sale_number}`" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold">
                        {{ sale.sale_number }}
                    </h1>
                    <Badge
                        :variant="STATUS_VARIANT[sale.status] ?? 'secondary'"
                        class="text-sm"
                    >
                        {{ statuses[sale.status] }}
                    </Badge>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="SaleController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <a
                            :href="SaleController.pdf.url(sale.id)"
                            target="_blank"
                        >
                            <FileDown class="mr-1 h-4 w-4" />納品書PDF
                        </a>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="SaleController.replicate.url(sale.id)">
                            <Copy class="mr-1 h-4 w-4" />この売上を複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="SaleController.edit.url(sale.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 基本情報 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">
                    基本情報
                </h2>
                <dl
                    class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-3"
                >
                    <div>
                        <dt class="text-muted-foreground">得意先</dt>
                        <dd class="font-medium">
                            {{ sale.customer?.name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当者</dt>
                        <dd>{{ sale.employee?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">売上日</dt>
                        <dd>{{ fmtDate(sale.sale_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">納品日</dt>
                        <dd>{{ fmtDate(sale.delivery_date) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-muted-foreground">件名</dt>
                        <dd class="font-medium">{{ sale.subject }}</dd>
                    </div>
                    <div v-if="sale.remarks" class="col-span-3">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="whitespace-pre-wrap">{{ sale.remarks }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 明細 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">
                    明細
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">#</th>
                                <th class="px-3 py-2 text-left font-medium">機種名</th>
                                <th class="px-3 py-2 text-left font-medium">フレームNo</th>
                                <th class="px-3 py-2 text-left font-medium">倉庫</th>
                                <th class="px-3 py-2 text-left font-medium">色コード</th>
                                <th class="px-3 py-2 text-right font-medium">数量</th>
                                <th class="px-3 py-2 text-left font-medium">単位</th>
                                <th class="px-3 py-2 text-right font-medium">仕入単価</th>
                                <th class="px-3 py-2 text-right font-medium">売上単価</th>
                                <th class="px-3 py-2 text-center font-medium">税率</th>
                                <th class="px-3 py-2 text-right font-medium">売上金額</th>
                                <th class="px-3 py-2 text-right font-medium">仕入金額</th>
                                <th class="px-3 py-2 text-left font-medium">備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in sale.items"
                                :key="item.id"
                                class="border-t"
                            >
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.line_no }}
                                </td>
                                <td class="px-3 py-2 font-medium">
                                    {{ item.kisyu_nm ?? '—' }}
                                </td>
                                <td class="px-3 py-2 font-mono">
                                    {{ item.frame_no ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.warehouse_code ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.iro_cd ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ Number(item.quantity).toLocaleString('ja-JP') }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.unit ?? '台' }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums text-muted-foreground">
                                    {{ fmt(item.sre_tan) }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ fmt(item.uri_tan) }}
                                </td>
                                <td class="px-3 py-2 text-center text-muted-foreground">
                                    {{ item.tax_rate }}%
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums font-medium">
                                    {{ fmt(item.sale_amount) }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums text-muted-foreground">
                                    {{ fmt(item.cogs_amount) }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.remarks ?? '' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/30 font-medium">
                            <tr>
                                <td
                                    colspan="10"
                                    class="px-3 py-2 text-right text-muted-foreground"
                                >
                                    合計金額（税抜）
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ fmt(sale.subtotal) }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums text-muted-foreground">
                                    {{ fmt(sale.cogs_total) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td
                                    colspan="10"
                                    class="px-3 py-2 text-right text-muted-foreground"
                                >
                                    消費税
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ fmt(sale.tax_amount) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="text-base">
                                <td
                                    colspan="10"
                                    class="px-3 py-2 text-right"
                                >
                                    税込み金額
                                </td>
                                <td
                                    class="px-3 py-2 text-right font-bold tabular-nums"
                                >
                                    {{ fmt(sale.total_amount) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
