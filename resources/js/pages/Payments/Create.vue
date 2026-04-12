<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import PaymentForm from '@/components/PaymentForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer { id: number; name: string; }
interface Employee { id: number; name: string; }
interface PrefillItem {
    payment_type: string;
    amount: number;
    bank_info: string;
    remarks: string;
}
interface Prefill {
    customer_id?: string;
    employee_id?: string;
    subject?: string;
    remarks?: string;
    items?: PrefillItem[];
}

const props = defineProps<{
    customers: Customer[];
    employees: Employee[];
    statuses: Record<string, string>;
    paymentTypes: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '入金', href: PaymentController.index.url() },
    { title: '新規作成', href: PaymentController.create.url() },
];

const today = new Date().toISOString().slice(0, 10);
const p = props.prefill;

const defaultItem = {
    payment_type: 'transfer',
    amount: '0',
    bank_info: '',
    remarks: '',
};

const form = useForm({
    customer_id:  p?.customer_id ?? '',
    employee_id:  p?.employee_id ?? '',
    payment_date: today,
    subject:      p?.subject ?? '',
    status:       'recorded',
    remarks:      p?.remarks ?? '',
    items: p?.items?.length
        ? p.items.map((i) => ({ ...i, amount: String(i.amount) }))
        : [{ ...defaultItem }],
});

function submit() {
    form.post(PaymentController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="入金 新規作成" />
        <div class="p-4">
            <div class="mb-6 flex items-center gap-3">
                <h1 class="text-2xl font-bold">入金 新規作成</h1>
                <span
                    v-if="prefill"
                    class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                >複製元データ適用済み</span>
            </div>
            <PaymentForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :statuses="statuses"
                :payment-types="paymentTypes"
                :cancel-href="PaymentController.index.url()"
                submit-label="登録"
                processing-label="登録中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
