<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import { Button } from '@/components/ui/button';
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
interface PurchaseItemData {
    vehicle_id: number | null;
    kisyu_cd: string | null; frame_no: string | null; warehouse_code: string | null;
    iro_cd: string | null; kisyu_nm: string | null;
    quantity: string; unit: string | null;
    sre_tan: string; purchase_amount: string;
    tax_rate: string; remarks: string | null;
}
interface PurchaseData {
    id: number;
    purchase_number: string;
    supplier_id: number;
    employee_id: number | null;
    purchase_date: string;
    subject: string;
    status: string;
    remarks: string | null;
    items: PurchaseItemData[];
}

interface VehicleModelOption { kisyu_cd: string; iro_cd: string | null; kisyu_nm: string | null; sre_tan: string; }

const props = defineProps<{
    purchase: PurchaseData;
    suppliers: Supplier[];
    employees: Employee[];
    vehicles: VehicleOption[];
    vehicleModels: VehicleModelOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入', href: PurchaseController.index.url() },
    { title: props.purchase.purchase_number, href: PurchaseController.show.url(props.purchase.id) },
    { title: '編集', href: PurchaseController.edit.url(props.purchase.id) },
];

const form = useForm({
    supplier_id:   String(props.purchase.supplier_id),
    employee_id:   props.purchase.employee_id ? String(props.purchase.employee_id) : '',
    purchase_date: props.purchase.purchase_date,
    subject:       props.purchase.subject,
    status:        props.purchase.status,
    remarks:       props.purchase.remarks ?? '',
    items: props.purchase.items.map((item) => ({
        vehicle_id:      item.vehicle_id,
        kisyu_cd:        item.kisyu_cd ?? '',
        frame_no:        item.frame_no ?? '',
        warehouse_code:  item.warehouse_code ?? '',
        iro_cd:          item.iro_cd ?? '',
        kisyu_nm:        item.kisyu_nm ?? '',
        quantity:        item.quantity,
        unit:            item.unit ?? '台',
        sre_tan:         item.sre_tan,
        tax_rate:        item.tax_rate,
        purchase_amount: parseFloat(item.purchase_amount) || 0,
        remarks:         item.remarks ?? '',
    })),
});

function submit() {
    form.put(PurchaseController.update.url(props.purchase.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`仕入 ${purchase.purchase_number} 編集`" />
        <div class="p-4">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    仕入 編集
                    <span class="font-mono text-muted-foreground">{{ purchase.purchase_number }}</span>
                </h1>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="PurchaseController.replicate.url(purchase.id)">
                        <Copy class="mr-1.5 h-4 w-4" />この仕入を複製
                    </Link>
                </Button>
            </div>
            <PurchaseForm
                :form="form"
                :suppliers="suppliers"
                :employees="employees"
                :vehicles="vehicles"
                :vehicle-models="vehicleModels"
                :warehouses="warehouses"
                :statuses="statuses"
                :cancel-href="PurchaseController.show.url(purchase.id)"
                submit-label="更新"
                processing-label="更新中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
