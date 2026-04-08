<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
import VehicleModelForm from '@/components/VehicleModelForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface VehicleModel {
    id: number;
    kisyu_cd: string;
    iro_cd: string;
    kisyu_nm: string | null;
    kisyu_nm_r: string | null;
    kihon: string | null;
    kisyu_nm_h: string | null;
    sre_tan: string | null;
    uri_tan: string | null;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_no: string | null;
    zei_kbn: string | null;
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
    { title: '車両機種マスタ', href: VehicleModelController.index.url() },
    { title: '編集', href: VehicleModelController.edit.url(props.vehicleModel.id) },
];

const form = useForm({
    kisyu_cd:   props.vehicleModel.kisyu_cd,
    iro_cd:     props.vehicleModel.iro_cd,
    kisyu_nm:   props.vehicleModel.kisyu_nm ?? '',
    kisyu_nm_r: props.vehicleModel.kisyu_nm_r ?? '',
    kihon:      props.vehicleModel.kihon ?? '',
    kisyu_nm_h: props.vehicleModel.kisyu_nm_h ?? '',
    sre_tan:    props.vehicleModel.sre_tan ?? '',
    uri_tan:    props.vehicleModel.uri_tan ?? '',
    g1:         props.vehicleModel.g1 ?? (null as string | null),
    g2:         props.vehicleModel.g2 ?? (null as string | null),
    g3:         props.vehicleModel.g3 ?? (null as string | null),
    g4:         props.vehicleModel.g4 ?? (null as string | null),
    g5:         props.vehicleModel.g5 ?? (null as string | null),
    order_no:   props.vehicleModel.order_no ?? '',
    zei_kbn:    props.vehicleModel.zei_kbn ?? (null as string | null),
});

function submit() {
    form.put(VehicleModelController.update.url(props.vehicleModel.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`車両機種マスタ 編集 - ${vehicleModel.kisyu_cd}`" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">車両機種 編集</h1>
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
