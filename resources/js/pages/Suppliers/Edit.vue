<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import { Button } from '@/components/ui/button';
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
    { title: '仕入先', href: SupplierController.index.url() },
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
            <div class="rounded-md border p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">仕入先 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="SupplierController.replicate.url(supplier.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この仕入先を複製
                        </Link>
                    </Button>
                </div>
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
