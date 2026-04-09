<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import PaymentForm from '@/components/PaymentForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer { id: number; name: string; }
interface Employee { id: number; name: string; }
interface PaymentItemData {
    payment_type: string;
    amount: string;
    bank_info: string | null;
    remarks: string | null;
}
interface PaymentData {
    id: number;
    payment_number: string;
    customer_id: number;
    employee_id: number | null;
    payment_date: string;
    subject: string;
    status: string;
    remarks: string | null;
    items: PaymentItemData[];
}

const props = defineProps<{
    payment: PaymentData;
    customers: Customer[];
    employees: Employee[];
    statuses: Record<string, string>;
    paymentTypes: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '入金', href: PaymentController.index.url() },
    { title: props.payment.payment_number, href: PaymentController.show.url(props.payment.id) },
    { title: '編集', href: PaymentController.edit.url(props.payment.id) },
];

const form = useForm({
    customer_id:  String(props.payment.customer_id),
    employee_id:  props.payment.employee_id ? String(props.payment.employee_id) : '',
    payment_date: props.payment.payment_date,
    subject:      props.payment.subject,
    status:       props.payment.status,
    remarks:      props.payment.remarks ?? '',
    items: props.payment.items.map((item) => ({
        payment_type: item.payment_type,
        amount:       item.amount,
        bank_info:    item.bank_info ?? '',
        remarks:      item.remarks ?? '',
    })),
});

function submit() {
    form.put(PaymentController.update.url(props.payment.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`入金 ${payment.payment_number} 編集`" />
        <div class="p-4">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    入金 編集
                    <span class="font-mono text-muted-foreground">{{ payment.payment_number }}</span>
                </h1>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="PaymentController.replicate.url(payment.id)">
                        <Copy class="mr-1.5 h-4 w-4" />この入金を複製
                    </Link>
                </Button>
            </div>
            <PaymentForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :statuses="statuses"
                :payment-types="paymentTypes"
                :cancel-href="PaymentController.show.url(payment.id)"
                submit-label="更新"
                processing-label="更新中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
