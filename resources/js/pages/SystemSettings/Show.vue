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
    company_name: string | null;
    company_name_kana: string | null;
    postal_code: string | null;
    prefecture_city: string | null;
    address: string | null;
    building: string | null;
    representative: string | null;
    tel: string | null;
    fax: string | null;
    invoice_no: string | null;
    bank_info: string | null;
    account_number: string | null;
    account_holder: string | null;
    remarks: string | null;
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

function val(v: string | null): string {
    return v ?? '—';
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

            <!-- 月次設定 -->
            <div class="rounded-md border p-6">
                <h2 class="mb-4 text-sm font-semibold text-muted-foreground">月次設定</h2>
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">月次更新年月</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ fmtYm(setting.closing_ym) }}</dd>
                        <p class="mt-0.5 text-xs text-muted-foreground">在庫月次繰越の対象年月です</p>
                    </div>
                </dl>
            </div>

            <!-- 自社情報 -->
            <div class="rounded-md border p-6">
                <h2 class="mb-4 text-sm font-semibold text-muted-foreground">自社情報</h2>
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-muted-foreground">会社名</dt>
                        <dd class="mt-1 font-medium">{{ val(setting.company_name) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">会社名カナ</dt>
                        <dd class="mt-1">{{ val(setting.company_name_kana) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">代表者</dt>
                        <dd class="mt-1">{{ val(setting.representative) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">郵便番号</dt>
                        <dd class="mt-1">{{ setting.postal_code ? '〒' + setting.postal_code : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">都道府県市町村</dt>
                        <dd class="mt-1">{{ val(setting.prefecture_city) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">番地</dt>
                        <dd class="mt-1">{{ val(setting.address) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">ビル等</dt>
                        <dd class="mt-1">{{ val(setting.building) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">TEL番号</dt>
                        <dd class="mt-1">{{ val(setting.tel) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">FAX番号</dt>
                        <dd class="mt-1">{{ val(setting.fax) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">インボイス登録番号</dt>
                        <dd class="mt-1 font-mono">{{ val(setting.invoice_no) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 振込先情報 -->
            <div class="rounded-md border p-6">
                <h2 class="mb-4 text-sm font-semibold text-muted-foreground">振込先情報</h2>
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-muted-foreground">銀行情報</dt>
                        <dd class="mt-1">{{ val(setting.bank_info) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">口座番号</dt>
                        <dd class="mt-1 font-mono">{{ val(setting.account_number) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">口座名義人名</dt>
                        <dd class="mt-1">{{ val(setting.account_holder) }}</dd>
                    </div>
                    <div v-if="setting.remarks" class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="mt-1 whitespace-pre-wrap">{{ setting.remarks }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 更新日時 -->
            <div class="text-xs text-muted-foreground">
                最終更新: {{ new Date(setting.updated_at).toLocaleString('ja-JP') }}
            </div>
        </div>
    </AppLayout>
</template>
