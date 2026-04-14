<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
import VehicleModelForm from '@/components/VehicleModelForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface VehicleModel {
    id: number;
    model_code: string;
    color_code: string;
    model_name: string | null;
    model_abbr: string | null;
    base_model: string | null;
    model_name_kanji: string | null;
    purchase_price: string | null;
    selling_price: string | null;
    terminal_price: string | null;
    standard_retail_price: string | null;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_number: string | null;
    tax_type: string | null;
}

const props = defineProps<{
    vehicleModel: VehicleModel;
    zeiKbn: Record<string, string>;
    g1Types: Record<string, string>;
    g2Disp: Record<string, string>;
    g3Options: Record<string, string>;
    g4Options: Record<string, string>;
    g5Options: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '機種商品', href: VehicleModelController.index.url() },
    { title: '編集', href: VehicleModelController.edit.url(props.vehicleModel.id) },
];

const form = useForm({
    model_code:            props.vehicleModel.model_code,
    color_code:            props.vehicleModel.color_code,
    model_name:            props.vehicleModel.model_name ?? '',
    model_abbr:            props.vehicleModel.model_abbr ?? '',
    base_model:            props.vehicleModel.base_model ?? '',
    model_name_kanji:      props.vehicleModel.model_name_kanji ?? '',
    purchase_price:        props.vehicleModel.purchase_price ?? '',
    selling_price:         props.vehicleModel.selling_price ?? '',
    terminal_price:        props.vehicleModel.terminal_price ?? '',
    standard_retail_price: props.vehicleModel.standard_retail_price ?? '',
    g1:                    props.vehicleModel.g1 ?? (null as string | null),
    g2:                    props.vehicleModel.g2 ?? (null as string | null),
    g3:                    props.vehicleModel.g3 ?? (null as string | null),
    g4:                    props.vehicleModel.g4 ?? (null as string | null),
    g5:                    props.vehicleModel.g5 ?? (null as string | null),
    order_number:          props.vehicleModel.order_number ?? '',
    tax_type:              props.vehicleModel.tax_type ?? (null as string | null),
});

function submit() {
    form.put(VehicleModelController.update.url(props.vehicleModel.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`機種商品 編集 - ${vehicleModel.model_code}`" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">機種商品 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleModelController.replicate.url(vehicleModel.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この機種を複製
                        </Link>
                    </Button>
                </div>
                <VehicleModelForm
                    :form="form"
                    :zei-kbn="zeiKbn"
                    :g1-types="g1Types"
                    :g2-disp="g2Disp"
                    :g3-options="g3Options"
                    :g4-options="g4Options"
                    :g5-options="g5Options"
                    :cancel-href="VehicleModelController.index.url()"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
