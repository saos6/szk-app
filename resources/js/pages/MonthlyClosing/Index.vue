<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import * as MonthlyClosingController from '@/actions/App/Http/Controllers/MonthlyClosingController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ currentYm: string }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '月次繰越', href: MonthlyClosingController.index.url() },
];

const confirmForm = useForm({ ym: props.currentYm });
const cancelForm  = useForm({ ym: props.currentYm });

function doConfirm() {
    if (!confirm(`${fmtYm(confirmForm.ym)} の月次確定処理を実行しますか？\n\n対象年月の売上・入金（請求完了）と仕入（計上）をすべて完了にし、在庫繰越を行います。`)) return;
    confirmForm.post(MonthlyClosingController.confirm.url());
}

function doCancel() {
    if (!confirm(`${fmtYm(cancelForm.ym)} の月次取消処理を実行しますか？\n\n対象年月の売上・入金・仕入の完了ステータスを戻し、在庫繰越を取り消します。`)) return;
    cancelForm.post(MonthlyClosingController.cancel.url());
}

function fmtYm(ym: string): string {
    if (!ym) return '';
    const [y, m] = ym.split('-');
    return `${y}年${m}月`;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="月次繰越" />
        <div class="flex flex-col gap-6 p-4">
            <h1 class="text-2xl font-bold">月次繰越</h1>

            <!-- 確定 -->
            <div class="rounded-md border p-6">
                <h2 class="mb-4 text-lg font-semibold">確定処理</h2>
                <p class="mb-4 text-sm text-muted-foreground">
                    対象年月の売上・入金（請求完了）と仕入（計上）のステータスを「完了」に変更し、
                    在庫残高の翌月繰越データを作成・更新します。
                </p>
                <form @submit.prevent="doConfirm" class="flex flex-col gap-4">
                    <div class="grid max-w-xs gap-1.5">
                        <Label for="confirm-ym">処理年月 <span class="text-destructive">*</span></Label>
                        <Input
                            id="confirm-ym"
                            type="month"
                            v-model="confirmForm.ym"
                            :class="{ 'border-destructive': confirmForm.errors.ym }"
                        />
                        <p v-if="confirmForm.errors.ym" class="text-xs text-destructive">
                            {{ confirmForm.errors.ym }}
                        </p>
                    </div>
                    <div>
                        <Button type="submit" :disabled="confirmForm.processing">
                            {{ confirmForm.processing ? '処理中...' : '確定する' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- 取消 -->
            <div class="rounded-md border p-6">
                <h2 class="mb-4 text-lg font-semibold">取消処理</h2>
                <p class="mb-4 text-sm text-muted-foreground">
                    対象年月の売上・入金・仕入の「完了」ステータスを元に戻し、
                    在庫残高翌月レコードの前月繰越を 0 にリセットします。
                </p>
                <form @submit.prevent="doCancel" class="flex flex-col gap-4">
                    <div class="grid max-w-xs gap-1.5">
                        <Label for="cancel-ym">処理年月 <span class="text-destructive">*</span></Label>
                        <Input
                            id="cancel-ym"
                            type="month"
                            v-model="cancelForm.ym"
                            :class="{ 'border-destructive': cancelForm.errors.ym }"
                        />
                        <p v-if="cancelForm.errors.ym" class="text-xs text-destructive">
                            {{ cancelForm.errors.ym }}
                        </p>
                    </div>
                    <div>
                        <Button type="submit" variant="destructive" :disabled="cancelForm.processing">
                            {{ cancelForm.processing ? '処理中...' : '取消する' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- 現在の設定 -->
            <div class="rounded-md bg-muted/50 p-4 text-sm text-muted-foreground">
                設定の月次更新年月：<span class="font-semibold text-foreground">{{ fmtYm(currentYm) }}</span>
            </div>
        </div>
    </AppLayout>
</template>
