<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as WarehouseController from '@/actions/App/Http/Controllers/WarehouseController';
import WarehouseForm from '@/components/WarehouseForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Warehouse {
    code: string;
    name: string;
}

const props = defineProps<{ warehouse: Warehouse }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '倉庫マスタ', href: WarehouseController.index.url() },
    { title: '編集', href: WarehouseController.edit.url(props.warehouse.code) },
];

const form = useForm({
    code: props.warehouse.code,
    name: props.warehouse.name,
});

function submit() {
    form.put(WarehouseController.update.url(props.warehouse.code));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`倉庫マスタ 編集 - ${warehouse.name}`" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">倉庫 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="WarehouseController.replicate.url(warehouse.code)">
                            <Copy class="mr-1.5 h-4 w-4" />この倉庫を複製
                        </Link>
                    </Button>
                </div>
                <WarehouseForm
                    :form="form"
                    :is-edit="true"
                    :cancel-href="WarehouseController.show.url(warehouse.code)"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
