<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Employee {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    dept: { id: number; name: string } | null;
    email: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ employee: Employee }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '社員', href: EmployeeController.index.url() },
    { title: props.employee.name, href: EmployeeController.show.url(props.employee.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`社員 ${employee.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">社員 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="EmployeeController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="EmployeeController.replicate.url(employee.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="EmployeeController.edit.url(employee.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">社員コード</dt>
                        <dd class="mt-0.5 font-mono font-medium">{{ employee.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">氏名</dt>
                        <dd class="mt-0.5 font-medium">{{ employee.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">氏名カナ</dt>
                        <dd class="mt-0.5 font-medium">{{ employee.name_kana ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">所属</dt>
                        <dd class="mt-0.5 font-medium">{{ employee.dept?.name ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-muted-foreground">メールアドレス</dt>
                        <dd class="mt-0.5 font-medium">{{ employee.email ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(employee.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(employee.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
