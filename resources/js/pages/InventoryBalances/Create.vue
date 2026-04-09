<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as InventoryBalanceController from '@/actions/App/Http/Controllers/InventoryBalanceController';
import InventoryBalanceForm from '@/components/InventoryBalanceForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Warehouse { code: string; name: string; }
interface VehicleModel { kisyu_cd: string; kisyu_nm_h: string | null; }

interface Prefill {
    warehouse_code?: string;
    vehicle_model_code?: string;
    frame_no?: string;
    prev_stock?: number;
    in_stock?: number;
    out_stock?: number;
}

const props = defineProps<{
    warehouses: Warehouse[];
    vehicleModels: VehicleModel[];
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '在庫残高マスタ', href: InventoryBalanceController.index.url() },
    { title: '新規登録', href: InventoryBalanceController.create.url() },
];

const p = props.prefill;

const form = useForm({
    stock_ym:            '',
    warehouse_code:      p?.warehouse_code      ?? '',
    vehicle_model_code:  p?.vehicle_model_code  ?? '',
    frame_no:            p?.frame_no            ?? '',
    prev_stock:          p?.prev_stock          ?? 0,
    in_stock:            p?.in_stock            ?? 0,
    out_stock:           p?.out_stock           ?? 0,
});

function submit() {
    form.post(InventoryBalanceController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="在庫残高マスタ 新規登録" />

        <div class="max-w-3xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">在庫残高 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >複製元データ適用済み</span>
                </div>
                <InventoryBalanceForm
                    :form="form"
                    :warehouses="warehouses"
                    :vehicle-models="vehicleModels"
                    :cancel-href="InventoryBalanceController.index.url()"
                    submit-label="登録する"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
