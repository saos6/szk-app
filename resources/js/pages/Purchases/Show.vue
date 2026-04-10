<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, FileDown, Lock, Pencil } from 'lucide-vue-next';
import * as PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface PurchaseItem {
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
    purchase_amount: string;
    tax_rate: string;
    remarks: string | null;
}

interface Purchase {
    id: number;
    purchase_number: string;
    supplier: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    purchase_date: string;
    subject: string;
    status: string;
    subtotal: string;
    tax_amount: string;
    total_amount: string;
    remarks: string | null;
    items: PurchaseItem[];
}

const props = defineProps<{
    purchase: Purchase;
    statuses: Record<string, string>;
    locked: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入', href: PurchaseController.index.url() },
    { title: props.purchase.purchase_number, href: PurchaseController.show.url(props.purchase.id) },
];

const STATUS_VARIANT: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    draft:     'secondary',
    recorded:  'outline',
    completed: 'default',
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
        <Head :title="`仕入 ${purchase.purchase_number}`" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold">{{ purchase.purchase_number }}</h1>
                    <Badge :variant="STATUS_VARIANT[purchase.status] ?? 'secondary'" class="text-sm">
                        {{ statuses[purchase.status] }}
                    </Badge>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="PurchaseController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <a :href="PurchaseController.pdf.url(purchase.id)" target="_blank">
                            <FileDown class="mr-1 h-4 w-4" />仕入書PDF
                        </a>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="PurchaseController.replicate.url(purchase.id)">
                            <Copy class="mr-1 h-4 w-4" />この仕入を複製
                        </Link>
                    </Button>
                    <Button v-if="!locked" size="sm" as-child>
                        <Link :href="PurchaseController.edit.url(purchase.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                    <Button v-else size="sm" disabled>
                        <Lock class="mr-1 h-4 w-4" />編集不可
                    </Button>
                </div>
            </div>

            <!-- ロック警告 -->
            <div v-if="locked" class="flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <Lock class="h-4 w-4 shrink-0" />
                この仕入はロックされています。ステータスまたは月次更新済み期間のため修正・削除できません。
            </div>

            <!-- 基本情報 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">基本情報</h2>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-muted-foreground">仕入先</dt>
                        <dd class="font-medium">{{ purchase.supplier?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当者</dt>
                        <dd>{{ purchase.employee?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">仕入日</dt>
                        <dd>{{ fmtDate(purchase.purchase_date) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-muted-foreground">件名</dt>
                        <dd class="font-medium">{{ purchase.subject }}</dd>
                    </div>
                    <div v-if="purchase.remarks" class="col-span-3">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="whitespace-pre-wrap">{{ purchase.remarks }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 明細 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">明細</h2>
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
                                <th class="px-3 py-2 text-center font-medium">税率</th>
                                <th class="px-3 py-2 text-right font-medium">仕入金額</th>
                                <th class="px-3 py-2 text-left font-medium">備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in purchase.items" :key="item.id" class="border-t">
                                <td class="px-3 py-2 text-muted-foreground">{{ item.line_no }}</td>
                                <td class="px-3 py-2 font-medium">{{ item.kisyu_nm ?? '—' }}</td>
                                <td class="px-3 py-2 font-mono">{{ item.frame_no ?? '—' }}</td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.warehouse_code ?? '—' }}</td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.iro_cd ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ Number(item.quantity).toLocaleString('ja-JP') }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.unit ?? '台' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums text-muted-foreground">
                                    {{ fmt(item.sre_tan) }}
                                </td>
                                <td class="px-3 py-2 text-center text-muted-foreground">{{ item.tax_rate }}%</td>
                                <td class="px-3 py-2 text-right tabular-nums font-medium">
                                    {{ fmt(item.purchase_amount) }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.remarks ?? '' }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/30 font-medium">
                            <tr>
                                <td colspan="9" class="px-3 py-2 text-right text-muted-foreground">合計金額（税抜）</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(purchase.subtotal) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="px-3 py-2 text-right text-muted-foreground">消費税</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(purchase.tax_amount) }}</td>
                                <td></td>
                            </tr>
                            <tr class="text-base">
                                <td colspan="9" class="px-3 py-2 text-right">税込み仕入金額</td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">{{ fmt(purchase.total_amount) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
