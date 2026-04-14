<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import * as SystemSettingController from '@/actions/App/Http/Controllers/SystemSettingController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface SystemSetting {
    id: number;
    closing_ym: string;
}

const props = defineProps<{ setting: SystemSetting }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '設定', href: SystemSettingController.show.url() },
    { title: '編集', href: SystemSettingController.edit.url() },
];

const form = useForm({
    closing_ym: props.setting.closing_ym,
});

function submit() {
    form.put(SystemSettingController.update.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="設定 編集" />
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">設定 編集</h1>

            <div class="rounded-md border p-6">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <!-- 月次更新年月 -->
                    <div class="grid max-w-sm gap-1.5">
                        <Label for="closing_ym">
                            月次更新年月 <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="closing_ym"
                            type="month"
                            v-model="form.closing_ym"
                            :class="{ 'border-destructive': form.errors.closing_ym }"
                        />
                        <p v-if="form.errors.closing_ym" class="text-xs text-destructive">
                            {{ form.errors.closing_ym }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            在庫月次繰越の対象年月です
                        </p>
                    </div>

                    <!-- ボタン -->
                    <div class="flex gap-2">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? '更新中...' : '更新する' }}
                        </Button>
                        <Button type="button" variant="outline" as-child>
                            <Link :href="SystemSettingController.show.url()">キャンセル</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
