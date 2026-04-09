<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as WarehouseController from '@/actions/App/Http/Controllers/WarehouseController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Warehouse {
    code: string;
    name: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ warehouse: Warehouse }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '倉庫マスタ', href: WarehouseController.index.url() },
    { title: props.warehouse.name, href: WarehouseController.show.url(props.warehouse.code) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`倉庫 ${warehouse.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">倉庫 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="WarehouseController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="WarehouseController.replicate.url(warehouse.code)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="WarehouseController.edit.url(warehouse.code)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">倉庫コード</dt>
                        <dd class="mt-0.5 font-medium">{{ warehouse.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">倉庫名</dt>
                        <dd class="mt-0.5 font-medium">{{ warehouse.name }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(warehouse.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(warehouse.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
