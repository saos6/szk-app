<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface VehicleModel {
    id: number;
    model_code: string;
    color_code: string | null;
    model_name: string | null;
    model_abbr: string | null;
    model_name_kanji: string | null;
    base_model: string | null;
    purchase_price: string | null;
    selling_price: string | null;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_number: number | null;
    tax_type: string | null;
    created_at: string;
    updated_at: string;
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
    {
        title: props.vehicleModel.model_code,
        href: VehicleModelController.show.url(props.vehicleModel.id),
    },
];

function fmt(val: string | number | null): string {
    if (val === null || val === '') return '—';
    return '¥' + Number(val).toLocaleString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`機種商品 ${vehicleModel.model_code}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">機種商品 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleModelController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="VehicleModelController.replicate.url(vehicleModel.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="VehicleModelController.edit.url(vehicleModel.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 text-sm">

                    <!-- 基本情報 -->
                    <div>
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">基本情報</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">機種商品コード</dt>
                                <dd class="mt-0.5 font-mono font-medium">{{ vehicleModel.model_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">色</dt>
                                <dd class="mt-0.5 font-mono font-medium">{{ vehicleModel.color_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">表示順</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicleModel.order_number ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">機種商品名</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicleModel.model_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">機種名（読み）</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicleModel.model_abbr ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">機種名（略称）</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicleModel.model_name_kanji ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">基本</dt>
                                <dd class="mt-0.5 font-medium">{{ vehicleModel.base_model ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">税区分</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.tax_type !== null ? zeiKbn[vehicleModel.tax_type] ?? vehicleModel.tax_type : '—' }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- 単価 -->
                    <div class="border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">単価</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-muted-foreground">仕入単価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(vehicleModel.purchase_price) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">売上単価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(vehicleModel.selling_price) }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- 区分 -->
                    <div class="border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">区分</h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-5">
                            <div>
                                <dt class="text-muted-foreground">G1（タイプ）</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.g1 ? g1Types[vehicleModel.g1] ?? vehicleModel.g1 : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">G2（排気量）</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.g2 ? g2Disp[vehicleModel.g2] ?? vehicleModel.g2 : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">G3</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.g3 ? g3Options[vehicleModel.g3] ?? vehicleModel.g3 : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">G4</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.g4 ? g4Options[vehicleModel.g4] ?? vehicleModel.g4 : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">G5</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ vehicleModel.g5 ? g5Options[vehicleModel.g5] ?? vehicleModel.g5 : '—' }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(vehicleModel.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(vehicleModel.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
