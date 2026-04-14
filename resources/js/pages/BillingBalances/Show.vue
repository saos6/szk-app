<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as BillingBalanceController from '@/actions/App/Http/Controllers/BillingBalanceController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface BillingBalance {
    id: number;
    billing_date: string;
    customer: { id: number; code: string; name: string } | null;
    prev_amount: string;
    sales_amount: string;
    tax_amount: string;
    total_amount: string;
    payment_amount: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    billingBalance: BillingBalance;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '請求残高', href: BillingBalanceController.index.url() },
    {
        title: props.billingBalance.billing_date,
        href: BillingBalanceController.show.url(props.billingBalance.id),
    },
];

function fmt(val: string | number): string {
    return '¥' + Number(val).toLocaleString('ja-JP');
}

function calcBillingAmount(b: BillingBalance): number {
    return Number(b.prev_amount) + Number(b.sales_amount) + Number(b.tax_amount) - Number(b.payment_amount);
}

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`請求残高 ${billingBalance.billing_date}`" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">請求残高 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="BillingBalanceController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="BillingBalanceController.replicate.url(billingBalance.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="BillingBalanceController.edit.url(billingBalance.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 詳細 -->
            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">請求日</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ fmtDate(billingBalance.billing_date) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">得意先</dt>
                        <dd class="mt-0.5 font-medium">
                            <span v-if="billingBalance.customer">
                                <span class="font-mono text-xs text-muted-foreground">
                                    [{{ billingBalance.customer.code }}]
                                </span>
                                {{ billingBalance.customer.name }}
                            </span>
                            <span v-else>—</span>
                        </dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">金額</h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">前月繰越額</dt>
                                <dd class="mt-0.5 tabular-nums text-muted-foreground">
                                    {{ fmt(billingBalance.prev_amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">売上金額</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">
                                    {{ fmt(billingBalance.sales_amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">消費税</dt>
                                <dd class="mt-0.5 tabular-nums text-muted-foreground">
                                    {{ fmt(billingBalance.tax_amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">税込金額</dt>
                                <dd class="mt-0.5 tabular-nums text-lg font-bold">
                                    {{ fmt(billingBalance.total_amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">入金額</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">
                                    {{ fmt(billingBalance.payment_amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">残高</dt>
                                <dd
                                    class="mt-0.5 tabular-nums font-medium"
                                    :class="{
                                        'text-destructive':
                                            Number(billingBalance.total_amount) -
                                                Number(billingBalance.payment_amount) >
                                            0,
                                    }"
                                >
                                    {{
                                        fmt(
                                            Number(billingBalance.total_amount) -
                                                Number(billingBalance.payment_amount),
                                        )
                                    }}
                                </dd>
                            </div>
                            <div class="sm:col-span-3 border-t pt-3">
                                <dt class="text-muted-foreground">当月請求額</dt>
                                <dd
                                    class="mt-0.5 tabular-nums text-lg font-bold"
                                    :class="{ 'text-destructive': calcBillingAmount(billingBalance) > 0 }"
                                >
                                    {{ fmt(calcBillingAmount(billingBalance)) }}
                                </dd>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    前月繰越額 + 当月税込金額 − 当月入金額
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(billingBalance.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(billingBalance.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
