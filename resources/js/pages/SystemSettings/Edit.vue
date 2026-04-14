<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import * as SystemSettingController from '@/actions/App/Http/Controllers/SystemSettingController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
}

const props = defineProps<{ setting: SystemSetting }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '設定', href: SystemSettingController.show.url() },
    { title: '編集', href: SystemSettingController.edit.url() },
];

const form = useForm({
    closing_ym:        props.setting.closing_ym,
    company_name:      props.setting.company_name ?? '',
    company_name_kana: props.setting.company_name_kana ?? '',
    postal_code:       props.setting.postal_code ?? '',
    prefecture_city:   props.setting.prefecture_city ?? '',
    address:           props.setting.address ?? '',
    building:          props.setting.building ?? '',
    representative:    props.setting.representative ?? '',
    tel:               props.setting.tel ?? '',
    fax:               props.setting.fax ?? '',
    invoice_no:        props.setting.invoice_no ?? '',
    bank_info:         props.setting.bank_info ?? '',
    account_number:    props.setting.account_number ?? '',
    account_holder:    props.setting.account_holder ?? '',
    remarks:           props.setting.remarks ?? '',
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

            <form @submit.prevent="submit" class="flex flex-col gap-4">

                <!-- 月次設定 -->
                <div class="rounded-md border p-6">
                    <h2 class="mb-4 text-sm font-semibold text-muted-foreground">月次設定</h2>
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
                        <p class="text-xs text-muted-foreground">在庫月次繰越の対象年月です</p>
                    </div>
                </div>

                <!-- 自社情報 -->
                <div class="rounded-md border p-6">
                    <h2 class="mb-4 text-sm font-semibold text-muted-foreground">自社情報</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="grid gap-1.5">
                            <Label for="company_name">会社名</Label>
                            <Input id="company_name" v-model="form.company_name" maxlength="100" />
                            <p v-if="form.errors.company_name" class="text-xs text-destructive">{{ form.errors.company_name }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="company_name_kana">会社名カナ</Label>
                            <Input id="company_name_kana" v-model="form.company_name_kana" maxlength="100" />
                            <p v-if="form.errors.company_name_kana" class="text-xs text-destructive">{{ form.errors.company_name_kana }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="representative">代表者</Label>
                            <Input id="representative" v-model="form.representative" maxlength="100" />
                            <p v-if="form.errors.representative" class="text-xs text-destructive">{{ form.errors.representative }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="postal_code">郵便番号</Label>
                            <Input id="postal_code" v-model="form.postal_code" maxlength="10" placeholder="000-0000" />
                            <p v-if="form.errors.postal_code" class="text-xs text-destructive">{{ form.errors.postal_code }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="prefecture_city">都道府県市町村</Label>
                            <Input id="prefecture_city" v-model="form.prefecture_city" maxlength="100" />
                            <p v-if="form.errors.prefecture_city" class="text-xs text-destructive">{{ form.errors.prefecture_city }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="address">番地</Label>
                            <Input id="address" v-model="form.address" maxlength="200" />
                            <p v-if="form.errors.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                        </div>
                        <div class="grid gap-1.5 sm:col-span-2">
                            <Label for="building">ビル等</Label>
                            <Input id="building" v-model="form.building" maxlength="200" />
                            <p v-if="form.errors.building" class="text-xs text-destructive">{{ form.errors.building }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="tel">TEL番号</Label>
                            <Input id="tel" v-model="form.tel" maxlength="20" placeholder="000-000-0000" />
                            <p v-if="form.errors.tel" class="text-xs text-destructive">{{ form.errors.tel }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="fax">FAX番号</Label>
                            <Input id="fax" v-model="form.fax" maxlength="20" placeholder="000-000-0000" />
                            <p v-if="form.errors.fax" class="text-xs text-destructive">{{ form.errors.fax }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="invoice_no">インボイス登録番号</Label>
                            <Input id="invoice_no" v-model="form.invoice_no" maxlength="20" placeholder="T0000000000000" />
                            <p v-if="form.errors.invoice_no" class="text-xs text-destructive">{{ form.errors.invoice_no }}</p>
                        </div>
                    </div>
                </div>

                <!-- 振込先情報 -->
                <div class="rounded-md border p-6">
                    <h2 class="mb-4 text-sm font-semibold text-muted-foreground">振込先情報</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="grid gap-1.5 sm:col-span-2 lg:col-span-3">
                            <Label for="bank_info">銀行情報</Label>
                            <Input id="bank_info" v-model="form.bank_info" maxlength="200" placeholder="○○銀行 ○○支店 普通" />
                            <p v-if="form.errors.bank_info" class="text-xs text-destructive">{{ form.errors.bank_info }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="account_number">口座番号</Label>
                            <Input id="account_number" v-model="form.account_number" maxlength="50" />
                            <p v-if="form.errors.account_number" class="text-xs text-destructive">{{ form.errors.account_number }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="account_holder">口座名義人名</Label>
                            <Input id="account_holder" v-model="form.account_holder" maxlength="100" />
                            <p v-if="form.errors.account_holder" class="text-xs text-destructive">{{ form.errors.account_holder }}</p>
                        </div>
                        <div class="grid gap-1.5 sm:col-span-2 lg:col-span-3">
                            <Label for="remarks">備考</Label>
                            <Textarea id="remarks" v-model="form.remarks" rows="3" />
                            <p v-if="form.errors.remarks" class="text-xs text-destructive">{{ form.errors.remarks }}</p>
                        </div>
                    </div>
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
    </AppLayout>
</template>
