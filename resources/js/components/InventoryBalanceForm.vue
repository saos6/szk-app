<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Combobox } from '@/components/ui/combobox';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Warehouse {
    code: string;
    name: string;
}

interface VehicleModel {
    kisyu_cd: string;
    kisyu_nm_h: string | null;
}

interface InventoryBalanceFormData {
    stock_ym: string;
    warehouse_code: string;
    vehicle_model_code: string;
    frame_no: string;
    prev_stock: number;
    in_stock: number;
    out_stock: number;
    errors: {
        stock_ym?: string;
        warehouse_code?: string;
        vehicle_model_code?: string;
        frame_no?: string;
        prev_stock?: string;
        in_stock?: string;
        out_stock?: string;
    };
    processing: boolean;
}

interface Props {
    form: InventoryBalanceFormData;
    warehouses: Warehouse[];
    vehicleModels: VehicleModel[];
    cancelHref: string;
    submitLabel?: string;
    processingLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    submitLabel: '登録する',
    processingLabel: '処理中...',
});

const emit = defineEmits<{ submit: [] }>();

const warehouseOptions = computed(() =>
    props.warehouses.map((w) => ({
        value: w.code,
        label: `[${w.code}] ${w.name}`,
    })),
);

const vehicleModelOptions = computed(() =>
    props.vehicleModels.map((m) => ({
        value: m.kisyu_cd,
        label: m.kisyu_nm_h ? `[${m.kisyu_cd}] ${m.kisyu_nm_h}` : m.kisyu_cd,
    })),
);
</script>

<template>
    <form class="flex flex-col gap-5" @submit.prevent="emit('submit')">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- 年月 -->
            <div class="flex flex-col gap-1.5">
                <Label for="stock_ym">
                    年月
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="stock_ym"
                    type="month"
                    v-model="form.stock_ym"
                    :class="{ 'border-destructive': form.errors.stock_ym }"
                />
                <InputError :message="form.errors.stock_ym" />
            </div>

            <!-- フレームNo -->
            <div class="flex flex-col gap-1.5">
                <Label for="frame_no">
                    フレームNo
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="frame_no"
                    v-model="form.frame_no"
                    placeholder="例：AB1234567C"
                    maxlength="10"
                    :class="{ 'border-destructive': form.errors.frame_no }"
                />
                <InputError :message="form.errors.frame_no" />
            </div>

            <!-- 倉庫 -->
            <div class="flex flex-col gap-1.5">
                <Label>
                    倉庫
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Combobox
                    :options="warehouseOptions"
                    :model-value="form.warehouse_code"
                    placeholder="倉庫を選択..."
                    @update:model-value="form.warehouse_code = $event"
                />
                <InputError :message="form.errors.warehouse_code" />
            </div>

            <!-- 機種コード -->
            <div class="flex flex-col gap-1.5">
                <Label>
                    機種コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Combobox
                    :options="vehicleModelOptions"
                    :model-value="form.vehicle_model_code"
                    placeholder="機種を選択..."
                    @update:model-value="form.vehicle_model_code = $event"
                />
                <InputError :message="form.errors.vehicle_model_code" />
            </div>
        </div>

        <!-- 在庫数 -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <!-- 前月繰越在庫数 -->
            <div class="flex flex-col gap-1.5">
                <Label for="prev_stock">
                    前月繰越在庫数
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="prev_stock"
                    type="number"
                    min="0"
                    v-model="form.prev_stock"
                    :class="{ 'border-destructive': form.errors.prev_stock }"
                />
                <InputError :message="form.errors.prev_stock" />
            </div>

            <!-- 当月入庫数 -->
            <div class="flex flex-col gap-1.5">
                <Label for="in_stock">
                    当月入庫数
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="in_stock"
                    type="number"
                    min="0"
                    v-model="form.in_stock"
                    :class="{ 'border-destructive': form.errors.in_stock }"
                />
                <InputError :message="form.errors.in_stock" />
            </div>

            <!-- 当月出庫数 -->
            <div class="flex flex-col gap-1.5">
                <Label for="out_stock">
                    当月出庫数
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="out_stock"
                    type="number"
                    min="0"
                    v-model="form.out_stock"
                    :class="{ 'border-destructive': form.errors.out_stock }"
                />
                <InputError :message="form.errors.out_stock" />
            </div>
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
