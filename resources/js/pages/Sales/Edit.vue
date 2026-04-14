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
    model_code: string;
    frame_number: string;
    color_code: string | null;
    model_name: string | null;
    purchase_price: string;
    selling_price: string;
}
interface Warehouse {
    code: string;
    name: string;
}
interface SaleItemData {
    vehicle_id: number | null;
    model_code: string | null;
    frame_number: string | null;
    warehouse_code: string | null;
    color_code: string | null;
    model_name: string | null;
    quantity: string;
    unit: string | null;
    purchase_price: string;
    selling_price: string;
    terminal_price: string | null;
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
    order_date: string | null;
    delivery_date: string | null;
    partner_slip_no: string | null;
    subject: string;
    status: string;
    sale_type: string | null;
    transaction_type: string | null;
    remarks: string | null;
    items: SaleItemData[];
}

interface VehicleModelOption { model_code: string; color_code: string | null; model_name: string | null; purchase_price: string; selling_price: string; }

const props = defineProps<{
    sale: SaleData;
    customers: Customer[];
    employees: Employee[];
    vehicles: VehicleOption[];
    vehicleModels: VehicleModelOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
    saleTypes: Record<string, string>;
    transactionTypes: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '売上', href: SaleController.index.url() },
    { title: props.sale.sale_number, href: SaleController.edit.url(props.sale.id) },
    { title: '編集', href: SaleController.edit.url(props.sale.id) },
];

const form = useForm({
    customer_id:     String(props.sale.customer_id),
    employee_id:     props.sale.employee_id ? String(props.sale.employee_id) : '',
    sale_date:       props.sale.sale_date,
    order_date:      props.sale.order_date ?? '',
    delivery_date:   props.sale.delivery_date ?? '',
    partner_slip_no: props.sale.partner_slip_no ?? '',
    subject:         props.sale.subject,
    status:          props.sale.status,
    sale_type:        props.sale.sale_type ?? '',
    transaction_type: props.sale.transaction_type ?? '',
    remarks:          props.sale.remarks ?? '',
    items: props.sale.items.map((item) => ({
        vehicle_id:  item.vehicle_id,
        model_code:       item.model_code ?? '',
        frame_number:       item.frame_number ?? '',
        warehouse_code: item.warehouse_code ?? '',
        color_code:         item.color_code ?? '',
        model_name:    item.model_name ?? '',
        quantity:    item.quantity,
        unit:        item.unit ?? '台',
        purchase_price:     item.purchase_price,
        selling_price:     item.selling_price,
        terminal_price:    item.terminal_price ?? '',
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
                :vehicle-models="vehicleModels"
                :warehouses="warehouses"
                :statuses="statuses"
                :sale-types="saleTypes"
                :transaction-types="transactionTypes"
                :cancel-href="SaleController.index.url()"
                submit-label="更新"
                processing-label="更新中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
