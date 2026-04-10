<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import AppLayout from '@/layouts/AppLayout.vue';
import SupplierForm from '@/components/SupplierForm.vue';
import type { BreadcrumbItem } from '@/types';

interface Supplier {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    postal_code: string | null;
    address: string | null;
    phone: string | null;
    fax: string | null;
    email: string | null;
    contact_person: string | null;
    payment_site: number | null;
    remarks: string | null;
}

const props = defineProps<{ supplier: Supplier }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入先マスタ', href: SupplierController.index.url() },
    { title: props.supplier.name, href: SupplierController.show.url(props.supplier.id) },
    { title: '編集', href: SupplierController.edit.url(props.supplier.id) },
];

const form = useForm({
    code:           props.supplier.code,
    name:           props.supplier.name,
    name_kana:      props.supplier.name_kana ?? '',
    postal_code:    props.supplier.postal_code ?? '',
    address:        props.supplier.address ?? '',
    phone:          props.supplier.phone ?? '',
    fax:            props.supplier.fax ?? '',
    email:          props.supplier.email ?? '',
    contact_person: props.supplier.contact_person ?? '',
    payment_site:   String(props.supplier.payment_site ?? ''),
    remarks:        props.supplier.remarks ?? '',
});

function submit() {
    form.put(SupplierController.update.url(props.supplier.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`仕入先 編集 ${supplier.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">仕入先 編集</h1>
            <div class="rounded-md border p-6">
                <SupplierForm
                    :form="form"
                    :cancel-href="SupplierController.show.url(supplier.id)"
                    submit-label="更新する"
                    processing-label="更新中..."
                    :is-edit="true"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
