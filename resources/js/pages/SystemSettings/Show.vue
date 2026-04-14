<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import * as SystemSettingController from '@/actions/App/Http/Controllers/SystemSettingController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface SystemSetting {
    id: number;
    closing_ym: string;
    updated_at: string;
}

const props = defineProps<{ setting: SystemSetting }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '設定', href: SystemSettingController.show.url() },
];

function fmtYm(ym: string): string {
    const [y, m] = ym.split('-');
    return `${y}年${m}月`;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="設定" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">設定</h1>
                <Button size="sm" as-child>
                    <Link :href="SystemSettingController.edit.url()">
                        <Pencil class="mr-1 h-4 w-4" />編集
                    </Link>
                </Button>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 text-sm sm:grid-cols-2">
                    <!-- 月次更新年月 -->
                    <div>
                        <dt class="text-muted-foreground">月次更新年月</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ fmtYm(setting.closing_ym) }}</dd>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            在庫月次繰越の対象年月です
                        </p>
                    </div>

                    <!-- 更新日時 -->
                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="text-xs text-muted-foreground">
                            最終更新: {{ new Date(setting.updated_at).toLocaleString('ja-JP') }}
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
