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

interface Prefill {
    customer_id?: string;
    prev_amount?: string;
    sales_amount?: string;
    tax_amount?: string;
    total_amount?: string;
    payment_amount?: string;
}

const props = defineProps<{
    customers: Customer[];
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '請求残高マスタ', href: BillingBalanceController.index.url() },
    { title: '新規登録', href: BillingBalanceController.create.url() },
];

const form = useForm({
    billing_date:   '',
    customer_id:    props.prefill?.customer_id    ?? '',
    prev_amount:    props.prefill?.prev_amount    ?? '0',
    sales_amount:   props.prefill?.sales_amount   ?? '0',
    tax_amount:     props.prefill?.tax_amount     ?? '0',
    total_amount:   props.prefill?.total_amount   ?? '0',
    payment_amount: props.prefill?.payment_amount ?? '0',
});

function submit() {
    form.post(BillingBalanceController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="請求残高 新規登録" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">請求残高 新規登録</h1>
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
                    submit-label="登録する"
                    :cancel-href="BillingBalanceController.index.url()"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
