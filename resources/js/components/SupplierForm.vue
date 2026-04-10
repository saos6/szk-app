<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface SupplierFormData {
    code: string;
    name: string;
    name_kana: string;
    postal_code: string;
    address: string;
    phone: string;
    fax: string;
    email: string;
    contact_person: string;
    payment_site: string;
    remarks: string;
    errors: {
        code?: string;
        name?: string;
        name_kana?: string;
        postal_code?: string;
        address?: string;
        phone?: string;
        fax?: string;
        email?: string;
        contact_person?: string;
        payment_site?: string;
        remarks?: string;
    };
    processing: boolean;
}

interface Props {
    form: SupplierFormData;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
    isEdit?: boolean;
}

defineProps<Props>();
const emit = defineEmits<{ submit: [] }>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
        <!-- 基本情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <!-- 仕入先コード -->
            <div class="flex flex-col gap-1.5">
                <Label for="code">
                    仕入先コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="code"
                    v-model="form.code"
                    placeholder="例：S001"
                    maxlength="20"
                    :disabled="isEdit"
                    :class="{ 'border-destructive': form.errors.code, 'bg-muted': isEdit }"
                />
                <InputError :message="form.errors.code" />
            </div>

            <!-- 担当者名（仕入先側） -->
            <div class="flex flex-col gap-1.5">
                <Label for="contact_person">担当者名</Label>
                <Input
                    id="contact_person"
                    v-model="form.contact_person"
                    placeholder="例：山田 太郎"
                    maxlength="100"
                    :class="{ 'border-destructive': form.errors.contact_person }"
                />
                <InputError :message="form.errors.contact_person" />
            </div>
        </div>

        <!-- 仕入先名 -->
        <div class="flex flex-col gap-1.5">
            <Label for="name">
                仕入先名
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="name"
                v-model="form.name"
                placeholder="例：ホンダモーターサイクルジャパン株式会社"
                maxlength="100"
                :class="{ 'border-destructive': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
        </div>

        <!-- 仕入先名カナ -->
        <div class="flex flex-col gap-1.5">
            <Label for="name_kana">仕入先名カナ</Label>
            <Input
                id="name_kana"
                v-model="form.name_kana"
                placeholder="例：ホンダモーターサイクルジャパンカブシキガイシャ"
                maxlength="200"
                :class="{ 'border-destructive': form.errors.name_kana }"
            />
            <InputError :message="form.errors.name_kana" />
        </div>

        <!-- 住所 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="postal_code">郵便番号</Label>
                <Input
                    id="postal_code"
                    v-model="form.postal_code"
                    placeholder="例：100-0001"
                    maxlength="8"
                    :class="{ 'border-destructive': form.errors.postal_code }"
                />
                <InputError :message="form.errors.postal_code" />
            </div>
            <div class="flex flex-col gap-1.5 sm:col-span-2">
                <Label for="address">住所</Label>
                <Input
                    id="address"
                    v-model="form.address"
                    placeholder="例：東京都港区南青山2-1-1"
                    maxlength="255"
                    :class="{ 'border-destructive': form.errors.address }"
                />
                <InputError :message="form.errors.address" />
            </div>
        </div>

        <!-- 連絡先 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="phone">電話番号</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    placeholder="例：03-1234-5678"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.phone }"
                />
                <InputError :message="form.errors.phone" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="fax">FAX番号</Label>
                <Input
                    id="fax"
                    v-model="form.fax"
                    placeholder="例：03-1234-5679"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.fax }"
                />
                <InputError :message="form.errors.fax" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="email">メールアドレス</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="例：info@example.com"
                    maxlength="255"
                    :class="{ 'border-destructive': form.errors.email }"
                />
                <InputError :message="form.errors.email" />
            </div>
        </div>

        <!-- 支払サイト -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="flex flex-col gap-1.5">
                <Label for="payment_site">支払サイト（日）</Label>
                <Input
                    id="payment_site"
                    v-model="form.payment_site"
                    type="number"
                    min="0"
                    max="999"
                    placeholder="例：60"
                    :class="{ 'border-destructive': form.errors.payment_site }"
                />
                <InputError :message="form.errors.payment_site" />
            </div>
        </div>

        <!-- 備考 -->
        <div class="flex flex-col gap-1.5">
            <Label for="remarks">備考</Label>
            <Textarea
                id="remarks"
                v-model="form.remarks"
                placeholder="備考を入力してください"
                rows="3"
                maxlength="1000"
                :class="{ 'border-destructive': form.errors.remarks }"
            />
            <InputError :message="form.errors.remarks" />
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
            <Button variant="outline" type="button" as-child>
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
