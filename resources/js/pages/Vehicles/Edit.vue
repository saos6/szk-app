<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import * as VehicleController from '@/actions/App/Http/Controllers/VehicleController';
import VehicleForm from '@/components/VehicleForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface VehicleModelItem { kisyu_cd: string; iro_cd: string; kisyu_nm_h: string | null; }

interface Vehicle {
    id: number;
    kisyu_cd: string; frame_no: string;
    name1: string | null; name2: string | null;
    kisyu_nm: string | null; keishiki: string | null; kisyu_no: string | null;
    iro_cd: string | null;
    sre_tan: string | null; uri_tan: string | null;
    terminal_price: string | null; standard_retail_price: string | null;
    maker_code: string | null; unit: string | null;
    note1: string | null; note2: string | null; note3: string | null;
    first_reg_date: string | null; second_reg_date: string | null; vehicle_no: string | null;
    owner_name: string | null; owner_kana: string | null; birth_date: string | null;
    zip_code: string | null; gender: string | null;
    address1: string | null; address2: string | null; tel: string | null; mobile: string | null;
    has_security_reg: boolean; security_reg_date: string | null;
    has_theft_insurance: boolean; theft_insurance_date: string | null;
    has_warranty: boolean; has_application: boolean; has_dm: boolean;
    remarks: string | null; shop_name: string | null; sale_date: string | null;
}

const props = defineProps<{
    vehicle: Vehicle;
    vehicleModelList: VehicleModelItem[];
    genders: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '車両（品番）マスタ', href: VehicleController.index.url() },
    { title: '編集', href: VehicleController.edit.url(props.vehicle.id) },
];

const v = props.vehicle;
const form = useForm({
    kisyu_cd: v.kisyu_cd, frame_no: v.frame_no,
    name1: v.name1 ?? '', name2: v.name2 ?? '',
    kisyu_nm: v.kisyu_nm ?? '', keishiki: v.keishiki ?? '', kisyu_no: v.kisyu_no ?? '',
    iro_cd: v.iro_cd ?? (null as string | null),
    sre_tan: v.sre_tan ?? '', uri_tan: v.uri_tan ?? '',
    terminal_price: v.terminal_price ?? '', standard_retail_price: v.standard_retail_price ?? '',
    maker_code: v.maker_code ?? '', unit: v.unit ?? '',
    note1: v.note1 ?? '', note2: v.note2 ?? '', note3: v.note3 ?? '',
    first_reg_date: v.first_reg_date ?? '', second_reg_date: v.second_reg_date ?? '',
    vehicle_no: v.vehicle_no ?? '',
    owner_name: v.owner_name ?? '', owner_kana: v.owner_kana ?? '',
    birth_date: v.birth_date ?? '',
    zip_code: v.zip_code ?? '', gender: v.gender ?? (null as string | null),
    address1: v.address1 ?? '', address2: v.address2 ?? '',
    tel: v.tel ?? '', mobile: v.mobile ?? '',
    has_security_reg: v.has_security_reg,
    security_reg_date: v.security_reg_date ?? '',
    has_theft_insurance: v.has_theft_insurance,
    theft_insurance_date: v.theft_insurance_date ?? '',
    has_warranty: v.has_warranty, has_application: v.has_application, has_dm: v.has_dm,
    remarks: v.remarks ?? '', shop_name: v.shop_name ?? '', sale_date: v.sale_date ?? '',
});

function submit() {
    form.put(VehicleController.update.url(props.vehicle.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`車両（品番）マスタ 編集 - ${vehicle.frame_no}`" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">車両（品番） 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleController.replicate.url(vehicle.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この車両を複製
                        </Link>
                    </Button>
                </div>
                <VehicleForm :form="form" :vehicle-model-list="vehicleModelList" :genders="genders"
                    :cancel-href="VehicleController.index.url()"
                    submit-label="更新" processing-label="更新中..." @submit="submit" />
            </div>
        </div>
    </AppLayout>
</template>
