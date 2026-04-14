<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
import VehicleModelForm from '@/components/VehicleModelForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Prefill {
    model_name?: string;
    model_abbr?: string;
    base_model?: string;
    model_name_kanji?: string;
    purchase_price?: string | null;
    selling_price?: string | null;
    terminal_price?: string | null;
    standard_retail_price?: string | null;
    g1?: string | null;
    g2?: string | null;
    g3?: string | null;
    g4?: string | null;
    g5?: string | null;
    order_number?: string;
    tax_type?: string | null;
}

const props = defineProps<{
    prefill?: Prefill;
    zeiKbn: Record<string, string>;
    g1Types: Record<string, string>;
    g2Disp: Record<string, string>;
    g3Options: Record<string, string>;
    g4Options: Record<string, string>;
    g5Options: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '車両機種（商品）マスタ', href: VehicleModelController.index.url() },
    { title: '新規登録', href: VehicleModelController.create.url() },
];

const p = props.prefill ?? {};

const form = useForm({
    model_code:            '',
    color_code:            '',
    model_name:            p.model_name ?? '',
    model_abbr:            p.model_abbr ?? '',
    base_model:            p.base_model ?? '',
    model_name_kanji:      p.model_name_kanji ?? '',
    purchase_price:        p.purchase_price ?? '',
    selling_price:         p.selling_price ?? '',
    terminal_price:        p.terminal_price ?? '',
    standard_retail_price: p.standard_retail_price ?? '',
    g1:                    p.g1 ?? (null as string | null),
    g2:                    p.g2 ?? (null as string | null),
    g3:                    p.g3 ?? (null as string | null),
    g4:                    p.g4 ?? (null as string | null),
    g5:                    p.g5 ?? (null as string | null),
    order_number:          p.order_number ?? '',
    tax_type:              p.tax_type ?? (null as string | null),
});

function submit() {
    form.post(VehicleModelController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="車両機種（商品）マスタ 新規登録" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">車両機種（商品） 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >複製元データ適用済み</span
                    >
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
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
