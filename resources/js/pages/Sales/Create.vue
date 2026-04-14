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
interface PrefillItem {
    vehicle_id: number | null;
    model_code: string;
    frame_number: string;
    warehouse_code: string;
    color_code: string;
    model_name: string;
    quantity: string;
    unit: string;
    purchase_price: string;
    selling_price: string;
    tax_rate: string;
    sale_amount: number;
    cogs_amount: number;
    remarks: string;
}
interface Prefill {
    customer_id?: string;
    employee_id?: string;
    sale_type?: string;
    sale_date?: string;
    order_date?: string;
    delivery_date?: string;
    partner_slip_no?: string;
    subject?: string;
    remarks?: string;
    items?: PrefillItem[];
}

interface VehicleModelOption { model_code: string; color_code: string | null; model_name: string | null; purchase_price: string; selling_price: string; }

const props = defineProps<{
    customers: Customer[];
    employees: Employee[];
    vehicles: VehicleOption[];
    vehicleModels: VehicleModelOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
    saleTypes: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '売上', href: SaleController.index.url() },
    { title: '新規作成', href: SaleController.create.url() },
];

const today = new Date().toISOString().slice(0, 10);
const p = props.prefill;

const defaultItem = {
    vehicle_id: null as number | null,
    model_code: '',
    frame_number: '',
    warehouse_code: '',
    color_code: '',
    model_name: '',
    quantity: '1',
    unit: '台',
    purchase_price: '0',
    selling_price: '0',
    tax_rate: '10',
    sale_amount: 0,
    cogs_amount: 0,
    remarks: '',
};

const form = useForm({
    customer_id:     p?.customer_id ?? '',
    employee_id:     p?.employee_id ?? '',
    sale_date:       today,
    order_date:      p?.order_date ?? '',
    delivery_date:   p?.delivery_date ?? '',
    partner_slip_no: p?.partner_slip_no ?? '',
    subject:         p?.subject ?? '',
    status:          'recorded',
    sale_type:       p?.sale_type ?? '',
    remarks:         p?.remarks ?? '',
    items: p?.items?.length ? p.items : [{ ...defaultItem }],
});

function submit() {
    form.post(SaleController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="売上 新規作成" />
        <div class="p-4">
            <div class="mb-6 flex items-center gap-3">
                <h1 class="text-2xl font-bold">売上 新規作成</h1>
                <span
                    v-if="prefill"
                    class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >複製元データ適用済み</span
                >
            </div>
            <SaleForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :vehicles="vehicles"
                :vehicle-models="vehicleModels"
                :warehouses="warehouses"
                :statuses="statuses"
                :sale-types="saleTypes"
                :cancel-href="SaleController.index.url()"
                submit-label="登録"
                processing-label="登録中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
