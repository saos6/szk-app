<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, FileDown, Pencil } from 'lucide-vue-next';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface PaymentItem {
    id: number;
    line_no: number;
    payment_type: string;
    amount: string;
    bank_info: string | null;
    remarks: string | null;
}

interface Payment {
    id: number;
    payment_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    payment_date: string;
    subject: string;
    status: string;
    total_amount: string;
    remarks: string | null;
    items: PaymentItem[];
}

const props = defineProps<{
    payment: Payment;
    statuses: Record<string, string>;
    paymentTypes: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '入金', href: PaymentController.index.url() },
    { title: props.payment.payment_number, href: PaymentController.show.url(props.payment.id) },
];

const STATUS_VARIANT: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    draft:     'secondary',
    recorded:  'outline',
    confirmed: 'default',
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
        <Head :title="`入金 ${payment.payment_number}`" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold">{{ payment.payment_number }}</h1>
                    <Badge :variant="STATUS_VARIANT[payment.status] ?? 'secondary'" class="text-sm">
                        {{ statuses[payment.status] }}
                    </Badge>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="PaymentController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <a :href="PaymentController.pdf.url(payment.id)" target="_blank">
                            <FileDown class="mr-1 h-4 w-4" />入金確認書PDF
                        </a>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="PaymentController.replicate.url(payment.id)">
                            <Copy class="mr-1 h-4 w-4" />この入金を複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="PaymentController.edit.url(payment.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 基本情報 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">基本情報</h2>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-muted-foreground">得意先</dt>
                        <dd class="font-medium">{{ payment.customer?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当者</dt>
                        <dd>{{ payment.employee?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">入金日</dt>
                        <dd>{{ fmtDate(payment.payment_date) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-muted-foreground">件名</dt>
                        <dd class="font-medium">{{ payment.subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">合計入金額</dt>
                        <dd class="text-lg font-bold">{{ fmt(payment.total_amount) }}</dd>
                    </div>
                    <div v-if="payment.remarks" class="col-span-3">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="whitespace-pre-wrap">{{ payment.remarks }}</dd>
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
                                <th class="px-3 py-2 text-left font-medium">入金区分</th>
                                <th class="px-3 py-2 text-right font-medium">入金額</th>
                                <th class="px-3 py-2 text-left font-medium">銀行情報</th>
                                <th class="px-3 py-2 text-left font-medium">備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in payment.items"
                                :key="item.id"
                                class="border-t"
                            >
                                <td class="px-3 py-2 text-muted-foreground">{{ item.line_no }}</td>
                                <td class="px-3 py-2 font-medium">
                                    {{ paymentTypes[item.payment_type] ?? item.payment_type }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums font-medium">
                                    {{ fmt(item.amount) }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.bank_info ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ item.remarks ?? '' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/30 font-medium text-base">
                            <tr>
                                <td class="px-3 py-2 text-right text-muted-foreground">合計入金額</td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">
                                    {{ fmt(payment.total_amount) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
