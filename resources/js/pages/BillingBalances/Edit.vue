<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import * as BillingBalanceController from '@/actions/App/Http/Controllers/BillingBalanceController';
import BillingBalanceForm from '@/components/BillingBalanceForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    code: string;
    name: string;
}

interface BillingBalance {
    id: number;
    billing_date: string;
    customer_id: number;
    prev_amount: string;
    sales_amount: string;
    tax_amount: string;
    total_amount: string;
    payment_amount: string;
}

const props = defineProps<{
    customers: Customer[];
    billingBalance: BillingBalance;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '請求残高マスタ', href: BillingBalanceController.index.url() },
    { title: '編集', href: BillingBalanceController.edit.url(props.billingBalance.id) },
];

const form = useForm({
    billing_date:   props.billingBalance.billing_date ?? '',
    customer_id:    String(props.billingBalance.customer_id),
    prev_amount:    props.billingBalance.prev_amount,
    sales_amount:   props.billingBalance.sales_amount,
    tax_amount:     props.billingBalance.tax_amount,
    total_amount:   props.billingBalance.total_amount,
    payment_amount: props.billingBalance.payment_amount,
});

function submit() {
    form.put(BillingBalanceController.update.url(props.billingBalance.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="請求残高 編集" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">請求残高 編集</h1>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="BillingBalanceController.index.url()">一覧へ戻る</Link>
                </Button>
            </div>
            <div class="rounded-md border p-6">
                <BillingBalanceForm
                    v-model="form"
                    :customers="customers"
                    :errors="form.errors"
                    :processing="form.processing"
                    submit-label="更新する"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
