<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as InventoryBalanceController from '@/actions/App/Http/Controllers/InventoryBalanceController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface InventoryBalance {
    id: number;
    stock_ym: string;
    warehouse_code: string;
    model_code: string;
    frame_number: string;
    prev_stock: number;
    in_stock: number;
    out_stock: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ inventoryBalance: InventoryBalance }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '在庫残高', href: InventoryBalanceController.index.url() },
    {
        title: `${props.inventoryBalance.stock_ym} ${props.inventoryBalance.warehouse_code}`,
        href: InventoryBalanceController.show.url(props.inventoryBalance.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`在庫残高 ${inventoryBalance.stock_ym}`" />

        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">在庫残高 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="InventoryBalanceController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="InventoryBalanceController.replicate.url(inventoryBalance.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="InventoryBalanceController.edit.url(inventoryBalance.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">年月</dt>
                        <dd class="mt-0.5 font-medium">{{ inventoryBalance.stock_ym }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">倉庫コード</dt>
                        <dd class="mt-0.5 font-medium">{{ inventoryBalance.warehouse_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">機種商品コード</dt>
                        <dd class="mt-0.5 font-medium">{{ inventoryBalance.model_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">車両品番</dt>
                        <dd class="mt-0.5 font-medium">{{ inventoryBalance.frame_number }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-4 gap-x-8 gap-y-4 text-sm">
                            <div>
                                <dt class="text-muted-foreground">前月繰越在庫数</dt>
                                <dd class="mt-0.5 font-medium text-right">{{ inventoryBalance.prev_stock.toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">当月入庫数</dt>
                                <dd class="mt-0.5 font-medium text-right">{{ inventoryBalance.in_stock.toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">当月出庫数</dt>
                                <dd class="mt-0.5 font-medium text-right">{{ inventoryBalance.out_stock.toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground font-semibold">当月在庫数</dt>
                                <dd class="mt-0.5 font-bold text-right text-lg">{{ inventoryBalance.current_stock.toLocaleString() }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(inventoryBalance.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(inventoryBalance.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
