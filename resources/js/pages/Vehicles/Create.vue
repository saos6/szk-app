<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as VehicleController from '@/actions/App/Http/Controllers/VehicleController';
import VehicleForm from '@/components/VehicleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface VehicleModelItem { model_code: string; color_code: string; model_name_kanji: string | null; }

interface Prefill {
    model_code?: string;
    name1?: string; name2?: string; model_name?: string;
    model_type?: string; model_number?: string;
    color_code?: string | null;
    purchase_price?: string | null; selling_price?: string | null;
    terminal_price?: string | null; standard_retail_price?: string | null;
    maker_code?: string; unit?: string; shop_name?: string;
    note1?: string; note2?: string; note3?: string;
}

const props = defineProps<{
    vehicleModelList: VehicleModelItem[];
    genders: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '車両品番', href: VehicleController.index.url() },
    { title: '新規登録', href: VehicleController.create.url() },
];

const p = props.prefill ?? {};

const form = useForm({
    model_code: p.model_code ?? '',
    frame_number: '',
    name1: p.name1 ?? '', name2: p.name2 ?? '',
    model_name: p.model_name ?? '', model_type: p.model_type ?? '', model_number: p.model_number ?? '',
    color_code: p.color_code ?? (null as string | null),
    purchase_price: p.purchase_price ?? '', selling_price: p.selling_price ?? '',
    terminal_price: p.terminal_price ?? '', standard_retail_price: p.standard_retail_price ?? '',
    maker_code: p.maker_code ?? '', unit: p.unit ?? '',
    note1: p.note1 ?? '', note2: p.note2 ?? '', note3: p.note3 ?? '',
    first_reg_date: '', second_reg_date: '', vehicle_no: '',
    owner_name: '', owner_kana: '', birth_date: '',
    zip_code: '', gender: null as string | null,
    address1: '', address2: '', tel: '', mobile: '',
    has_security_reg: false, security_reg_date: '',
    has_theft_insurance: false, theft_insurance_date: '',
    has_warranty: false, has_application: false, has_dm: false,
    remarks: '', shop_name: p.shop_name ?? '', sale_date: '',
});

function submit() {
    form.post(VehicleController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="車両品番 新規登録" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">車両品番 新規登録</h1>
                    <span v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                        複製元データ適用済み
                    </span>
                </div>
                <VehicleForm :form="form" :vehicle-model-list="vehicleModelList" :genders="genders"
                    :cancel-href="VehicleController.index.url()"
                    submit-label="登録" processing-label="登録中..." @submit="submit" />
            </div>
        </div>
    </AppLayout>
</template>
