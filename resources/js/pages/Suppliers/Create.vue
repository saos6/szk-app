<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import AppLayout from '@/layouts/AppLayout.vue';
import SupplierForm from '@/components/SupplierForm.vue';
import type { BreadcrumbItem } from '@/types';

interface Prefill {
    name?: string;
    name_kana?: string;
    postal_code?: string;
    address?: string;
    phone?: string;
    fax?: string;
    email?: string;
    contact_person?: string;
    payment_site?: number | null;
    remarks?: string;
}

const props = defineProps<{ prefill?: Prefill }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入先マスタ', href: SupplierController.index.url() },
    { title: '新規登録', href: SupplierController.create.url() },
];

const form = useForm({
    code:           '',
    name:           props.prefill?.name ?? '',
    name_kana:      props.prefill?.name_kana ?? '',
    postal_code:    props.prefill?.postal_code ?? '',
    address:        props.prefill?.address ?? '',
    phone:          props.prefill?.phone ?? '',
    fax:            props.prefill?.fax ?? '',
    email:          props.prefill?.email ?? '',
    contact_person: props.prefill?.contact_person ?? '',
    payment_site:   String(props.prefill?.payment_site ?? ''),
    remarks:        props.prefill?.remarks ?? '',
});

function submit() {
    form.post(SupplierController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="仕入先 新規登録" />
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">仕入先 新規登録</h1>
            <div class="rounded-md border p-6">
                <SupplierForm
                    :form="form"
                    :cancel-href="SupplierController.index.url()"
                    submit-label="登録する"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
