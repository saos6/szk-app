<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import PurchaseForm from '@/components/PurchaseForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Supplier { id: number; name: string; }
interface Employee { id: number; name: string; }
interface VehicleOption {
    id: number; kisyu_cd: string; frame_no: string;
    iro_cd: string | null; kisyu_nm: string | null; sre_tan: string;
}
interface Warehouse { code: string; name: string; }
interface PrefillItem {
    vehicle_id: number | null;
    kisyu_cd: string; frame_no: string; warehouse_code: string;
    iro_cd: string; kisyu_nm: string;
    quantity: string; unit: string; sre_tan: string;
    tax_rate: string; purchase_amount: number; remarks: string;
}
interface Prefill {
    supplier_id?: string;
    employee_id?: string;
    subject?: string;
    remarks?: string;
    items?: PrefillItem[];
}

interface VehicleModelOption { kisyu_cd: string; iro_cd: string | null; kisyu_nm: string | null; sre_tan: string; }

const props = defineProps<{
    suppliers: Supplier[];
    employees: Employee[];
    vehicles: VehicleOption[];
    vehicleModels: VehicleModelOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入', href: PurchaseController.index.url() },
    { title: '新規作成', href: PurchaseController.create.url() },
];

const today = new Date().toISOString().slice(0, 10);
const p = props.prefill;

const defaultItem = {
    vehicle_id: null as number | null,
    kisyu_cd: '', frame_no: '', warehouse_code: '',
    iro_cd: '', kisyu_nm: '',
    quantity: '1', unit: '台', sre_tan: '0',
    tax_rate: '10', purchase_amount: 0, remarks: '',
};

const form = useForm({
    supplier_id:   p?.supplier_id ?? '',
    employee_id:   p?.employee_id ?? '',
    purchase_date: today,
    subject:       p?.subject ?? '',
    status:        'draft',
    remarks:       p?.remarks ?? '',
    items: p?.items?.length ? p.items : [{ ...defaultItem }],
});

function submit() {
    form.post(PurchaseController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="仕入 新規作成" />
        <div class="p-4">
            <div class="mb-6 flex items-center gap-3">
                <h1 class="text-2xl font-bold">仕入 新規作成</h1>
                <span
                    v-if="prefill"
                    class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >複製元データ適用済み</span
                >
            </div>
            <PurchaseForm
                :form="form"
                :suppliers="suppliers"
                :employees="employees"
                :vehicles="vehicles"
                :vehicle-models="vehicleModels"
                :warehouses="warehouses"
                :statuses="statuses"
                :cancel-href="PurchaseController.index.url()"
                submit-label="登録"
                processing-label="登録中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
