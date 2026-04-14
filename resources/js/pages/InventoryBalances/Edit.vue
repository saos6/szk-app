<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as InventoryBalanceController from '@/actions/App/Http/Controllers/InventoryBalanceController';
import InventoryBalanceForm from '@/components/InventoryBalanceForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Warehouse { code: string; name: string; }
interface VehicleModel { model_code: string; model_name_kanji: string | null; }

interface InventoryBalance {
    id: number;
    stock_ym: string;
    warehouse_code: string;
    model_code: string;
    frame_number: string;
    prev_stock: number;
    in_stock: number;
    out_stock: number;
}

const props = defineProps<{
    inventoryBalance: InventoryBalance;
    warehouses: Warehouse[];
    vehicleModels: VehicleModel[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '在庫残高マスタ', href: InventoryBalanceController.index.url() },
    { title: '編集', href: InventoryBalanceController.edit.url(props.inventoryBalance.id) },
];

const form = useForm({
    stock_ym:           props.inventoryBalance.stock_ym,
    warehouse_code:     props.inventoryBalance.warehouse_code,
    model_code: props.inventoryBalance.model_code,
    frame_number:           props.inventoryBalance.frame_number,
    prev_stock:         props.inventoryBalance.prev_stock,
    in_stock:           props.inventoryBalance.in_stock,
    out_stock:          props.inventoryBalance.out_stock,
});

function submit() {
    form.put(InventoryBalanceController.update.url(props.inventoryBalance.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`在庫残高 編集 - ${inventoryBalance.stock_ym}`" />

        <div class="max-w-3xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">在庫残高 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="InventoryBalanceController.replicate.url(inventoryBalance.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この在庫残高を複製
                        </Link>
                    </Button>
                </div>
                <InventoryBalanceForm
                    :form="form"
                    :warehouses="warehouses"
                    :vehicle-models="vehicleModels"
                    :cancel-href="InventoryBalanceController.show.url(inventoryBalance.id)"
                    submit-label="更新する"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
