<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as DeptController from '@/actions/App/Http/Controllers/DeptController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Dept {
    id: number;
    name: string;
    parent: { id: number; name: string } | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ dept: Dept }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
    { title: props.dept.name, href: DeptController.show.url(props.dept.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`所属 ${dept.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">所属 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="DeptController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="DeptController.replicate.url(dept.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="DeptController.edit.url(dept.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">ID</dt>
                        <dd class="mt-0.5 font-medium">{{ dept.id }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">所属名</dt>
                        <dd class="mt-0.5 font-medium">{{ dept.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">親所属</dt>
                        <dd class="mt-0.5 font-medium">{{ dept.parent?.name ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(dept.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(dept.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
