<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
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
    billing_date:   String(props.billingBalance.billing_date ?? '').substring(0, 10),
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
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">請求残高 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="BillingBalanceController.replicate.url(billingBalance.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この請求残高を複製
                        </Link>
                    </Button>
                </div>
                <BillingBalanceForm
                    :form="form"
                    :customers="customers"
                    submit-label="更新する"
                    :cancel-href="BillingBalanceController.show.url(billingBalance.id)"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
