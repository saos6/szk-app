<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as VehicleController from '@/actions/App/Http/Controllers/VehicleController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Vehicle {
    id: number;
    kisyu_cd: string | null;
    frame_no: string | null;
    name1: string | null;
    name2: string | null;
    kisyu_nm: string | null;
    keishiki: string | null;
    kisyu_no: string | null;
    iro_cd: string | null;
    sre_tan: string | null;
    uri_tan: string | null;
    maker_code: string | null;
    unit: string | null;
    note1: string | null;
    note2: string | null;
    note3: string | null;
    first_reg_date: string | null;
    second_reg_date: string | null;
    vehicle_no: string | null;
    owner_name: string | null;
    owner_kana: string | null;
    birth_date: string | null;
    zip_code: string | null;
    gender: string | null;
    address1: string | null;
    address2: string | null;
    tel: string | null;
    mobile: string | null;
    has_security_reg: boolean;
    security_reg_date: string | null;
    has_theft_insurance: boolean;
    theft_insurance_date: string | null;
    has_warranty: boolean;
    has_application: boolean;
    has_dm: boolean;
    remarks: string | null;
    shop_name: string | null;
    sale_date: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    vehicle: Vehicle;
    genders: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '車両マスタ', href: VehicleController.index.url() },
    {
        title: props.vehicle.kisyu_cd ?? String(props.vehicle.id),
        href: VehicleController.show.url(props.vehicle.id),
    },
];

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}

function fmt(val: string | number | null): string {
    if (val === null || val === '') return '—';
    return '¥' + Number(val).toLocaleString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`車両 ${vehicle.kisyu_cd ?? vehicle.id}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">車両 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleController.replicate.url(vehicle.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="VehicleController.edit.url(vehicle.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 text-sm">

                    <!-- 機種情報 -->
                    <div>
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">機種情報</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">機種コード</dt>
                                <dd class="mt-0.5 font-mono font-medium">{{ vehicle.kisyu_cd ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">車台番号</dt>
                                <dd class="mt-0.5 font-mono font-medium">{{ vehicle.frame_no ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">色コード</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.iro_cd ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">機種名</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.kisyu_nm ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">形式</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.keishiki ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">機種番号</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.kisyu_no ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">車両番号</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.vehicle_no ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">メーカーコード</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.maker_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">単位</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.unit ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">仕入単価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(vehicle.sre_tan) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">売上単価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(vehicle.uri_tan) }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- 登録情報 -->
                    <div class="border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">登録情報</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">初度登録年月</dt>
                                <dd class="mt-0.5 font-medium">{{ fmtDate(vehicle.first_reg_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">2次登録年月</dt>
                                <dd class="mt-0.5 font-medium">{{ fmtDate(vehicle.second_reg_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">販売日</dt>
                                <dd class="mt-0.5 font-medium">{{ fmtDate(vehicle.sale_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">販売店名</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.shop_name ?? '—' }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- オーナー情報 -->
                    <div class="border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">オーナー情報</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-muted-foreground">氏名</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.owner_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">氏名カナ</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.owner_kana ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">生年月日</dt>
                                <dd class="mt-0.5 font-medium">{{ fmtDate(vehicle.birth_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">性別</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicle.gender ? genders[vehicle.gender] ?? vehicle.gender : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">郵便番号</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.zip_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">電話番号</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.tel ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">携帯番号</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.mobile ?? '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-muted-foreground">住所1</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.address1 ?? '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-muted-foreground">住所2</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.address2 ?? '—' }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- オプション -->
                    <div class="border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">オプション</h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-5">
                            <div>
                                <dt class="text-muted-foreground">セキュリティ登録</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.has_security_reg ? 'あり' : 'なし' }}</dd>
                                <dd v-if="vehicle.has_security_reg" class="text-xs text-muted-foreground">{{ fmtDate(vehicle.security_reg_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">盗難保険</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.has_theft_insurance ? 'あり' : 'なし' }}</dd>
                                <dd v-if="vehicle.has_theft_insurance" class="text-xs text-muted-foreground">{{ fmtDate(vehicle.theft_insurance_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">保証</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.has_warranty ? 'あり' : 'なし' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">申請</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.has_application ? 'あり' : 'なし' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">DM</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicle.has_dm ? 'あり' : 'なし' }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- 備考 -->
                    <div class="border-t pt-4">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="mt-0.5 whitespace-pre-wrap font-medium">{{ vehicle.remarks ?? '—' }}</dd>
                    </div>

                    <div class="border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(vehicle.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(vehicle.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
