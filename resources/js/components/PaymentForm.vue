<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Combobox } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface Customer {
    id: number;
    name: string;
}
interface Employee {
    id: number;
    name: string;
}
interface PaymentItem {
    payment_type: string;
    amount: string;
    bank_info: string;
    remarks: string;
}
interface FormData {
    customer_id: string;
    employee_id: string;
    payment_date: string;
    subject: string;
    status: string;
    remarks: string;
    items: PaymentItem[];
    [key: string]: unknown;
}

const props = defineProps<{
    form: InertiaForm<FormData>;
    customers: Customer[];
    employees: Employee[];
    statuses: Record<string, string>;
    paymentTypes: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}>();

const emit = defineEmits<{ submit: [] }>();

const customerOptions = computed(() =>
    props.customers.map((c) => ({ value: String(c.id), label: c.name })),
);

const totalAmount = computed(() =>
    props.form.items.reduce((s, i) => s + (parseFloat(i.amount) || 0), 0),
);

function fmt(val: number): string {
    return '¥' + val.toLocaleString('ja-JP', { minimumFractionDigits: 0 });
}

function addItem() {
    props.form.items.push({
        payment_type: 'transfer',
        amount: '0',
        bank_info: '',
        remarks: '',
    });
}

function removeItem(index: number) {
    props.form.items.splice(index, 1);
}

function getItemError(index: number, field: string): string | undefined {
    return (props.form.errors as Record<string, string>)[`items.${index}.${field}`];
}
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-6">
        <!-- 基本情報 -->
        <div class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">基本情報</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- 得意先 -->
                <div class="grid gap-1.5">
                    <Label>得意先 <span class="text-destructive">*</span></Label>
                    <Combobox
                        :options="customerOptions"
                        :model-value="form.customer_id"
                        placeholder="得意先を検索..."
                        :class="{ '[&_input]:border-destructive': form.errors.customer_id }"
                        @update:model-value="(v) => (form.customer_id = v)"
                    />
                    <p v-if="form.errors.customer_id" class="text-xs text-destructive">
                        {{ form.errors.customer_id }}
                    </p>
                </div>

                <!-- 担当者 -->
                <div class="grid gap-1.5">
                    <Label>担当者</Label>
                    <Select
                        :model-value="form.employee_id || '__none__'"
                        @update:model-value="(v) => (form.employee_id = v === '__none__' ? '' : v)"
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="未設定" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">未設定</SelectItem>
                            <SelectItem
                                v-for="e in employees"
                                :key="e.id"
                                :value="String(e.id)"
                            >{{ e.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- 入金日 -->
                <div class="grid gap-1.5">
                    <Label>入金日 <span class="text-destructive">*</span></Label>
                    <Input
                        type="date"
                        v-model="form.payment_date"
                        :class="{ 'border-destructive': form.errors.payment_date }"
                    />
                    <p v-if="form.errors.payment_date" class="text-xs text-destructive">
                        {{ form.errors.payment_date }}
                    </p>
                </div>

                <!-- ステータス -->
                <div class="grid gap-1.5">
                    <Label>ステータス <span class="text-destructive">*</span></Label>
                    <Select
                        :model-value="form.status"
                        @update:model-value="(v) => (form.status = v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                            <SelectValue placeholder="選択してください" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(label, key) in statuses"
                                :key="key"
                                :value="key"
                            >{{ label }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-xs text-destructive">
                        {{ form.errors.status }}
                    </p>
                </div>

                <!-- 件名 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>件名 <span class="text-destructive">*</span></Label>
                    <Input
                        v-model="form.subject"
                        placeholder="例: ○○入金"
                        :class="{ 'border-destructive': form.errors.subject }"
                    />
                    <p v-if="form.errors.subject" class="text-xs text-destructive">
                        {{ form.errors.subject }}
                    </p>
                </div>

                <!-- 備考 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>備考</Label>
                    <Textarea v-model="form.remarks" rows="3" placeholder="備考・特記事項" />
                </div>
            </div>
        </div>

        <!-- 明細 -->
        <div class="rounded-md border p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-muted-foreground">明細</h2>
                <Button type="button" variant="outline" size="sm" @click="addItem">
                    <Plus class="mr-1 h-4 w-4" />行追加
                </Button>
            </div>

            <p v-if="form.errors.items" class="mb-2 text-xs text-destructive">
                {{ form.errors.items }}
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">
                                入金区分 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                入金額 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">銀行情報</th>
                            <th class="px-3 py-2 text-left font-medium">備考</th>
                            <th class="w-10 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in form.items" :key="i" class="border-t">
                            <!-- 入金区分 -->
                            <td class="px-2 py-1.5">
                                <Select
                                    :model-value="item.payment_type"
                                    @update:model-value="(v) => (item.payment_type = v)"
                                >
                                    <SelectTrigger
                                        class="h-8 w-28"
                                        :class="{ 'border-destructive': getItemError(i, 'payment_type') }"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="(label, key) in paymentTypes"
                                            :key="key"
                                            :value="key"
                                        >{{ label }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <!-- 入金額 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.amount"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-32 text-right tabular-nums"
                                    :class="{ 'border-destructive': getItemError(i, 'amount') }"
                                />
                                <p v-if="getItemError(i, 'amount')" class="text-xs text-destructive">
                                    {{ getItemError(i, 'amount') }}
                                </p>
                            </td>
                            <!-- 銀行情報 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.bank_info" class="h-8 min-w-48" placeholder="例: ○○銀行 △△支店" />
                            </td>
                            <!-- 備考 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.remarks" class="h-8 w-32" />
                            </td>
                            <!-- 削除 -->
                            <td class="px-2 py-1.5 text-center">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 text-destructive hover:text-destructive"
                                    :disabled="form.items.length <= 1"
                                    @click="removeItem(i)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                    <!-- 合計フッター -->
                    <tfoot class="border-t bg-muted/30 font-medium">
                        <tr class="text-base">
                            <td class="px-3 py-2 text-right text-muted-foreground">合計入金額</td>
                            <td class="px-3 py-2 text-right font-bold tabular-nums">
                                {{ fmt(totalAmount) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" as-child>
                <a :href="cancelHref">キャンセル</a>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
