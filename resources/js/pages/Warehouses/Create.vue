<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as WarehouseController from '@/actions/App/Http/Controllers/WarehouseController';
import WarehouseForm from '@/components/WarehouseForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Prefill {
    name?: string;
}

const props = defineProps<{
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '倉庫マスタ', href: WarehouseController.index.url() },
    { title: '新規登録', href: WarehouseController.create.url() },
];

const form = useForm({
    code: '',
    name: props.prefill?.name ?? '',
});

function submit() {
    form.post(WarehouseController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="倉庫マスタ 新規登録" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">倉庫 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >複製元データ適用済み</span>
                </div>
                <WarehouseForm
                    :form="form"
                    :cancel-href="WarehouseController.index.url()"
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
