<script setup lang="ts">
import { computed, watch } from 'vue';
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

interface BillingBalanceFormData {
    billing_date: string;
    customer_id: string;
    prev_amount: string;
    sales_amount: string;
    tax_amount: string;
    total_amount: string;
    payment_amount: string;
    errors: {
        billing_date?: string;
        customer_id?: string;
        prev_amount?: string;
        sales_amount?: string;
        tax_amount?: string;
        total_amount?: string;
        payment_amount?: string;
    };
    processing: boolean;
}

interface Props {
    customers: Customer[];
    form: BillingBalanceFormData;
    submitLabel?: string;
    cancelHref: string;
}

const props = withDefaults(defineProps<Props>(), {
    submitLabel: '登録する',
});

const emit = defineEmits<{ submit: [] }>();

const customerOptions = computed(() =>
    props.customers.map((c) => ({
        value: String(c.id),
        label: `[${c.code}] ${c.name}`,
    })),
);

watch(
    () => [props.form.sales_amount, props.form.tax_amount],
    () => {
        const sales = parseFloat(props.form.sales_amount) || 0;
        const tax = parseFloat(props.form.tax_amount) || 0;
        props.form.total_amount = (sales + tax).toFixed(2);
    },
);
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
                    v-model="form.billing_date"
                />
                <p v-if="form.errors.billing_date" class="text-sm text-destructive">
                    {{ form.errors.billing_date }}
                </p>
            </div>

            <!-- 得意先 -->
            <div class="flex flex-col gap-1.5">
                <Label>得意先 <span class="text-destructive">*</span></Label>
                <Combobox
                    :options="customerOptions"
                    :model-value="form.customer_id"
                    placeholder="得意先を選択..."
                    @update:model-value="form.customer_id = $event"
                />
                <p v-if="form.errors.customer_id" class="text-sm text-destructive">
                    {{ form.errors.customer_id }}
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
                    v-model="form.prev_amount"
                />
                <p v-if="form.errors.prev_amount" class="text-sm text-destructive">
                    {{ form.errors.prev_amount }}
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
                    v-model="form.sales_amount"
                />
                <p v-if="form.errors.sales_amount" class="text-sm text-destructive">
                    {{ form.errors.sales_amount }}
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
                    v-model="form.tax_amount"
                />
                <p v-if="form.errors.tax_amount" class="text-sm text-destructive">
                    {{ form.errors.tax_amount }}
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
                    v-model="form.payment_amount"
                />
                <p v-if="form.errors.payment_amount" class="text-sm text-destructive">
                    {{ form.errors.payment_amount }}
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <Button variant="outline" type="button" as-child>
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? '処理中...' : submitLabel }}
            </Button>
        </div>
    </form>
</template>
