<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as SaleController from '@/actions/App/Http/Controllers/SaleController';
import SaleForm from '@/components/SaleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    name: string;
}
interface Employee {
    id: number;
    name: string;
}
interface VehicleOption {
    id: number;
    kisyu_cd: string;
    frame_no: string;
    iro_cd: string | null;
    kisyu_nm: string | null;
    sre_tan: string;
    uri_tan: string;
}
interface Warehouse {
    code: string;
    name: string;
}
interface SaleItemData {
    vehicle_id: number | null;
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
interface SaleData {
    id: number;
    sale_number: string;
    customer_id: number;
    employee_id: number | null;
    sale_date: string;
    delivery_date: string | null;
    subject: string;
    status: string;
    remarks: string | null;
    items: SaleItemData[];
}

const props = defineProps<{
    sale: SaleData;
    customers: Customer[];
    employees: Employee[];
    vehicles: VehicleOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '売上', href: SaleController.index.url() },
    { title: props.sale.sale_number, href: SaleController.edit.url(props.sale.id) },
    { title: '編集', href: SaleController.edit.url(props.sale.id) },
];

const form = useForm({
    customer_id:   String(props.sale.customer_id),
    employee_id:   props.sale.employee_id ? String(props.sale.employee_id) : '',
    sale_date:     props.sale.sale_date,
    delivery_date: props.sale.delivery_date ?? '',
    subject:       props.sale.subject,
    status:        props.sale.status,
    remarks:       props.sale.remarks ?? '',
    items: props.sale.items.map((item) => ({
        vehicle_id:  item.vehicle_id,
        kisyu_cd:       item.kisyu_cd ?? '',
        frame_no:       item.frame_no ?? '',
        warehouse_code: item.warehouse_code ?? '',
        iro_cd:         item.iro_cd ?? '',
        kisyu_nm:    item.kisyu_nm ?? '',
        quantity:    item.quantity,
        unit:        item.unit ?? '台',
        sre_tan:     item.sre_tan,
        uri_tan:     item.uri_tan,
        tax_rate:    item.tax_rate,
        sale_amount: parseFloat(item.sale_amount) || 0,
        cogs_amount: parseFloat(item.cogs_amount) || 0,
        remarks:     item.remarks ?? '',
    })),
});

function submit() {
    form.put(SaleController.update.url(props.sale.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`売上 ${sale.sale_number} 編集`" />
        <div class="p-4">
            <h1 class="mb-6 text-2xl font-bold">
                売上 編集
                <span class="font-mono text-muted-foreground">{{
                    sale.sale_number
                }}</span>
            </h1>
            <SaleForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :vehicles="vehicles"
                :warehouses="warehouses"
                :statuses="statuses"
                :cancel-href="SaleController.index.url()"
                submit-label="更新"
                processing-label="更新中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
