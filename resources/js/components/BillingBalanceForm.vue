<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Combobox } from '@/components/ui/combobox';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Customer {
    id: number;
    code: string;
    name: string;
}

interface FormData {
    billing_date: string;
    customer_id: string;
    prev_amount: string;
    sales_amount: string;
    tax_amount: string;
    total_amount: string;
    payment_amount: string;
    [key: string]: string;
}

interface Props {
    customers: Customer[];
    modelValue: FormData;
    errors: Partial<Record<keyof FormData, string>>;
    processing: boolean;
    submitLabel?: string;
    cancelHref: string;
}

const props = withDefaults(defineProps<Props>(), {
    submitLabel: '登録する',
});

const emit = defineEmits<{
    'update:modelValue': [value: FormData];
    submit: [];
}>();

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const customerOptions = computed(() =>
    props.customers.map((c) => ({
        value: String(c.id),
        label: `[${c.code}] ${c.name}`,
    })),
);

function set(field: keyof FormData, value: string) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function recalcTotal() {
    const sales = parseFloat(props.modelValue.sales_amount) || 0;
    const tax = parseFloat(props.modelValue.tax_amount) || 0;
    emit('update:modelValue', {
        ...props.modelValue,
        total_amount: (sales + tax).toFixed(2),
    });
}
</script>

<template>
    <form class="flex flex-col gap-6" @submit.prevent="emit('submit')">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- 請求日 -->
            <div class="flex flex-col gap-1.5">
                <Label for="billing_date">請求日 <span class="text-destructive">*</span></Label>
                <Input
                    id="billing_date"
                    type="date"
                    :model-value="form.billing_date"
                    @input="set('billing_date', ($event.target as HTMLInputElement).value)"
                />
                <p v-if="errors.billing_date" class="text-sm text-destructive">
                    {{ errors.billing_date }}
                </p>
            </div>

            <!-- 得意先 -->
            <div class="flex flex-col gap-1.5">
                <Label>得意先 <span class="text-destructive">*</span></Label>
                <Combobox
                    :options="customerOptions"
                    :model-value="form.customer_id"
                    placeholder="得意先を選択..."
                    @update:model-value="set('customer_id', $event)"
                />
                <p v-if="errors.customer_id" class="text-sm text-destructive">
                    {{ errors.customer_id }}
                </p>
            </div>

            <!-- 前月繰越額 -->
            <div class="flex flex-col gap-1.5">
                <Label for="prev_amount">前月繰越額 <span class="text-destructive">*</span></Label>
                <Input
                    id="prev_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    :model-value="form.prev_amount"
                    @input="set('prev_amount', ($event.target as HTMLInputElement).value)"
                />
                <p v-if="errors.prev_amount" class="text-sm text-destructive">
                    {{ errors.prev_amount }}
                </p>
            </div>

            <!-- 売上金額 -->
            <div class="flex flex-col gap-1.5">
                <Label for="sales_amount">売上金額 <span class="text-destructive">*</span></Label>
                <Input
                    id="sales_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    :model-value="form.sales_amount"
                    @input="
                        set('sales_amount', ($event.target as HTMLInputElement).value);
                        recalcTotal();
                    "
                />
                <p v-if="errors.sales_amount" class="text-sm text-destructive">
                    {{ errors.sales_amount }}
                </p>
            </div>

            <!-- 消費税 -->
            <div class="flex flex-col gap-1.5">
                <Label for="tax_amount">消費税 <span class="text-destructive">*</span></Label>
                <Input
                    id="tax_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    :model-value="form.tax_amount"
                    @input="
                        set('tax_amount', ($event.target as HTMLInputElement).value);
                        recalcTotal();
                    "
                />
                <p v-if="errors.tax_amount" class="text-sm text-destructive">
                    {{ errors.tax_amount }}
                </p>
            </div>

            <!-- 税込金額 (read-only) -->
            <div class="flex flex-col gap-1.5">
                <Label for="total_amount">税込金額</Label>
                <Input
                    id="total_amount"
                    type="number"
                    step="0.01"
                    :model-value="form.total_amount"
                    readonly
                    class="bg-muted/40 cursor-not-allowed"
                />
                <p class="text-xs text-muted-foreground">売上金額＋消費税で自動計算</p>
            </div>

            <!-- 入金額 -->
            <div class="flex flex-col gap-1.5">
                <Label for="payment_amount">入金額 <span class="text-destructive">*</span></Label>
                <Input
                    id="payment_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    :model-value="form.payment_amount"
                    @input="set('payment_amount', ($event.target as HTMLInputElement).value)"
                />
                <p v-if="errors.payment_amount" class="text-sm text-destructive">
                    {{ errors.payment_amount }}
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <Button variant="outline" type="button" as-child>
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ processing ? '処理中...' : submitLabel }}
            </Button>
        </div>
    </form>
</template>
