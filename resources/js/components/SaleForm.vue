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
interface VehicleOption {
    id: number;
    model_code: string;
    frame_number: string;
    color_code: string | null;
    model_name: string | null;
    purchase_price: string;
    selling_price: string;
    terminal_price: string | null;
}
interface VehicleModelOption {
    model_code: string;
    color_code: string | null;
    model_name: string | null;
    purchase_price: string;
    selling_price: string;
    terminal_price: string | null;
}
interface Warehouse {
    code: string;
    name: string;
}
interface SaleItem {
    vehicle_id: number | null;
    model_code: string;
    frame_number: string;
    warehouse_code: string;
    color_code: string;
    model_name: string;
    quantity: string;
    unit: string;
    purchase_price: string;
    selling_price: string;
    terminal_price: string;
    tax_rate: string;
    sale_amount: number;
    cogs_amount: number;
    remarks: string;
}
interface FormData {
    customer_id: string;
    employee_id: string;
    sale_date: string;
    order_date: string;
    delivery_date: string;
    partner_slip_no: string;
    subject: string;
    status: string;
    remarks: string;
    items: SaleItem[];
    [key: string]: unknown;
}

const props = defineProps<{
    form: InertiaForm<FormData>;
    customers: Customer[];
    employees: Employee[];
    vehicles: VehicleOption[];
    vehicleModels: VehicleModelOption[];
    warehouses: Warehouse[];
    statuses: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}>();

const emit = defineEmits<{ submit: [] }>();

// 得意先・担当者のコンボボックス用選択肢
const customerOptions = computed(() =>
    props.customers.map((c) => ({ value: String(c.id), label: c.name })),
);

// 機種コードのコンボボックス用選択肢（VehicleModel + Vehicle を統合）
const modelCodeOptions = computed(() => {
    const seen = new Set<string>();
    const list: { value: string; label: string }[] = [];
    for (const m of props.vehicleModels) {
        if (!seen.has(m.model_code)) { seen.add(m.model_code); list.push({ value: m.model_code, label: m.model_code }); }
    }
    for (const v of props.vehicles) {
        if (!seen.has(v.model_code)) { seen.add(v.model_code); list.push({ value: v.model_code, label: v.model_code }); }
    }
    return list;
});

// 倉庫コードのコンボボックス用選択肢
const warehouseOptions = computed(() =>
    props.warehouses.map((w) => ({ value: w.code, label: `${w.code} ${w.name}` })),
);

// 機種コードで絞り込んだ車両一覧
function frameNumberList(modelCode: string): VehicleOption[] {
    return props.vehicles.filter((v) => v.model_code === modelCode);
}

function addItem() {
    props.form.items.push({
        vehicle_id: null,
        model_code: '',
        frame_number: '',
        warehouse_code: '',
        color_code: '',
        model_name: '',
        quantity: '1',
        unit: '台',
        purchase_price: '0',
        selling_price: '0',
        terminal_price: '',
        tax_rate: '10',
        sale_amount: 0,
        cogs_amount: 0,
        remarks: '',
    });
}

function removeItem(index: number) {
    props.form.items.splice(index, 1);
}

function colorCodeOptions(modelCode: string) {
    const seen = new Set<string>();
    return props.vehicleModels
        .filter((m) => m.model_code === modelCode && m.color_code)
        .filter((m) => { if (seen.has(m.color_code!)) return false; seen.add(m.color_code!); return true; })
        .map((m) => ({ value: m.color_code!, label: m.color_code! }));
}

function onModelCodeChange(i: number, val: string) {
    props.form.items[i].model_code = val;
    props.form.items[i].frame_number = '';
    props.form.items[i].color_code = '';
    props.form.items[i].model_name = '';
    props.form.items[i].purchase_price = '0';
    props.form.items[i].selling_price = '0';
    props.form.items[i].terminal_price = '';
    props.form.items[i].vehicle_id = null;
    recalcItem(i);
}

function onColorCodeChange(i: number, val: string) {
    props.form.items[i].color_code = val;
    const vm = props.vehicleModels.find(
        (m) => m.model_code === props.form.items[i].model_code && m.color_code === val,
    );
    if (vm) {
        props.form.items[i].model_name = vm.model_name ?? '';
        props.form.items[i].purchase_price = vm.purchase_price ?? '0';
        props.form.items[i].selling_price = vm.selling_price ?? '0';
        props.form.items[i].terminal_price = vm.terminal_price ?? '';
    }
    recalcItem(i);
}

function onFrameNumberChange(i: number) {
    const frameNumber = props.form.items[i].frame_number;
    const vehicle = props.vehicles.find(
        (v) => v.model_code === props.form.items[i].model_code && v.frame_number === frameNumber,
    );
    if (vehicle) {
        props.form.items[i].color_code = vehicle.color_code ?? '';
        props.form.items[i].model_name = vehicle.model_name ?? '';
        props.form.items[i].purchase_price = vehicle.purchase_price ?? '0';
        props.form.items[i].selling_price = vehicle.selling_price ?? '0';
        props.form.items[i].terminal_price = vehicle.terminal_price ?? '';
        props.form.items[i].vehicle_id = vehicle.id;
    }
    recalcItem(i);
}

function recalcItem(i: number) {
    const item = props.form.items[i];
    const qty = parseFloat(item.quantity) || 0;
    const uri = parseFloat(item.selling_price) || 0;
    const sre = parseFloat(item.purchase_price) || 0;
    item.sale_amount = Math.round(qty * uri * 100) / 100;
    item.cogs_amount = Math.round(qty * sre * 100) / 100;
}

const subtotal = computed(() =>
    props.form.items.reduce((s, i) => s + (i.sale_amount || 0), 0),
);

const taxAmount = computed(() =>
    props.form.items.reduce((s, i) => {
        const rate = parseInt(i.tax_rate) || 0;
        return s + Math.round((i.sale_amount || 0) * rate) / 100;
    }, 0),
);

const totalAmount = computed(() => subtotal.value + taxAmount.value);

const cogsTotal = computed(() =>
    props.form.items.reduce((s, i) => s + (i.cogs_amount || 0), 0),
);

function fmt(val: number): string {
    return '¥' + val.toLocaleString('ja-JP', { minimumFractionDigits: 0 });
}

function getItemError(index: number, field: string): string | undefined {
    return (props.form.errors as Record<string, string>)[
        `items.${index}.${field}`
    ];
}
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-6">
        <!-- ヘッダー情報 -->
        <div class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">
                基本情報
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- 得意先 -->
                <div class="grid gap-1.5">
                    <Label>得意先 <span class="text-destructive">*</span></Label>
                    <Combobox
                        :options="customerOptions"
                        :model-value="form.customer_id"
                        placeholder="得意先を検索..."
                        :class="{
                            '[&_input]:border-destructive': form.errors.customer_id,
                        }"
                        @update:model-value="(v) => (form.customer_id = v)"
                    />
                    <p
                        v-if="form.errors.customer_id"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.customer_id }}
                    </p>
                </div>

                <!-- 担当者 -->
                <div class="grid gap-1.5">
                    <Label>担当者</Label>
                    <Select
                        :model-value="form.employee_id || '__none__'"
                        @update:model-value="
                            (v) =>
                                (form.employee_id = v === '__none__' ? '' : v)
                        "
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
                                >{{ e.name }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <!-- 売上日 -->
                <div class="grid gap-1.5">
                    <Label>売上日 <span class="text-destructive">*</span></Label>
                    <Input
                        type="date"
                        v-model="form.sale_date"
                        :class="{
                            'border-destructive': form.errors.sale_date,
                        }"
                    />
                    <p
                        v-if="form.errors.sale_date"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.sale_date }}
                    </p>
                </div>

                <!-- 受注日 -->
                <div class="grid gap-1.5">
                    <Label>受注日</Label>
                    <Input
                        type="date"
                        v-model="form.order_date"
                        :class="{
                            'border-destructive': form.errors.order_date,
                        }"
                    />
                    <p
                        v-if="form.errors.order_date"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.order_date }}
                    </p>
                </div>

                <!-- 納品日 -->
                <div class="grid gap-1.5">
                    <Label>納品日</Label>
                    <Input
                        type="date"
                        v-model="form.delivery_date"
                        :class="{
                            'border-destructive': form.errors.delivery_date,
                        }"
                    />
                    <p
                        v-if="form.errors.delivery_date"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.delivery_date }}
                    </p>
                </div>

                <!-- 相手伝票NO -->
                <div class="grid gap-1.5">
                    <Label>相手伝票NO</Label>
                    <Input
                        v-model="form.partner_slip_no"
                        maxlength="50"
                        placeholder="相手先の伝票番号"
                        :class="{
                            'border-destructive': form.errors.partner_slip_no,
                        }"
                    />
                    <p
                        v-if="form.errors.partner_slip_no"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.partner_slip_no }}
                    </p>
                </div>

                <!-- 件名 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>件名 <span class="text-destructive">*</span></Label>
                    <Input
                        v-model="form.subject"
                        placeholder="例: ○○販売"
                        :class="{ 'border-destructive': form.errors.subject }"
                    />
                    <p
                        v-if="form.errors.subject"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.subject }}
                    </p>
                </div>

                <!-- ステータス -->
                <div class="grid gap-1.5">
                    <Label
                        >ステータス
                        <span class="text-destructive">*</span></Label
                    >
                    <Select
                        :model-value="form.status"
                        @update:model-value="(v) => (form.status = v)"
                    >
                        <SelectTrigger
                            :class="{
                                'border-destructive': form.errors.status,
                            }"
                        >
                            <SelectValue placeholder="選択してください" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(label, key) in statuses"
                                :key="key"
                                :value="key"
                                >{{ label }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.status"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.status }}
                    </p>
                </div>

                <!-- 備考 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>備考</Label>
                    <Textarea
                        v-model="form.remarks"
                        rows="3"
                        placeholder="備考・特記事項"
                    />
                </div>
            </div>
        </div>

        <!-- 明細 -->
        <div class="rounded-md border p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-muted-foreground">
                    明細
                </h2>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addItem"
                >
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
                                機種コード（商品）<span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                フレームNo（品番）<span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                色コード
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                倉庫
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                機種名（商品名）
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                数量 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                単位
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                仕入単価
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                売上単価
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                末端価格
                            </th>
                            <th class="px-3 py-2 text-center font-medium">
                                税率
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                売上金額
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                仕入金額
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                備考
                            </th>
                            <th class="w-10 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, i) in form.items"
                            :key="i"
                            class="border-t"
                        >
                            <!-- 機種コード -->
                            <td class="px-2 py-1.5">
                                <Combobox
                                    :options="modelCodeOptions"
                                    :model-value="item.model_code"
                                    placeholder="機種コード（商品）..."
                                    :class="['w-36', getItemError(i, 'model_code') ? '[&_input]:border-destructive' : '']"
                                    @update:model-value="(v) => onModelCodeChange(i, v)"
                                />
                                <p v-if="getItemError(i, 'model_code')" class="mt-0.5 text-xs text-destructive">{{ getItemError(i, 'model_code') }}</p>
                            </td>
                            <!-- フレームNo -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.frame_number"
                                    :list="`frame-number-${i}`"
                                    placeholder="フレームNo（品番）..."
                                    :class="['h-8 w-40', getItemError(i, 'frame_number') ? 'border-destructive' : '']"
                                    @change="onFrameNumberChange(i)"
                                />
                                <datalist :id="`frame-number-${i}`">
                                    <option v-for="v in frameNumberList(item.model_code)" :key="v.frame_number" :value="v.frame_number" />
                                </datalist>
                                <p v-if="getItemError(i, 'frame_number')" class="mt-0.5 text-xs text-destructive">{{ getItemError(i, 'frame_number') }}</p>
                            </td>
                            <!-- 色コード -->
                            <td class="px-2 py-1.5">
                                <Combobox
                                    :options="colorCodeOptions(item.model_code)"
                                    :model-value="item.color_code"
                                    placeholder="色..."
                                    class="w-24"
                                    @update:model-value="(v) => onColorCodeChange(i, v)"
                                />
                            </td>
                            <!-- 倉庫コード -->
                            <td class="px-2 py-1.5">
                                <Combobox
                                    :options="warehouseOptions"
                                    :model-value="item.warehouse_code"
                                    placeholder="倉庫..."
                                    class="w-32"
                                    @update:model-value="(v) => (item.warehouse_code = v)"
                                />
                            </td>
                            <!-- 機種名（自動） -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.model_name"
                                    class="h-8 min-w-40"
                                    readonly
                                    tabindex="-1"
                                />
                            </td>
                            <!-- 数量 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="h-8 w-20 text-right tabular-nums"
                                    :class="{
                                        'border-destructive': getItemError(
                                            i,
                                            'quantity',
                                        ),
                                    }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 単位 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.unit" class="h-8 w-14" />
                            </td>
                            <!-- 仕入単価（自動） -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.purchase_price"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-24 text-right tabular-nums"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 売上単価（自動） -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.selling_price"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-24 text-right tabular-nums"
                                    :class="{
                                        'border-destructive': getItemError(
                                            i,
                                            'selling_price',
                                        ),
                                    }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 末端価格 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.terminal_price"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-24 text-right tabular-nums"
                                />
                            </td>
                            <!-- 税率 -->
                            <td class="px-2 py-1.5">
                                <Select
                                    :model-value="item.tax_rate"
                                    @update:model-value="
                                        (v) => {
                                            item.tax_rate = v;
                                            recalcItem(i);
                                        }
                                    "
                                >
                                    <SelectTrigger class="h-8 w-20">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">0%</SelectItem>
                                        <SelectItem value="10">10%</SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <!-- 売上金額 -->
                            <td
                                class="px-2 py-1.5 text-right tabular-nums"
                            >
                                {{ fmt(item.sale_amount) }}
                            </td>
                            <!-- 仕入金額 -->
                            <td
                                class="px-2 py-1.5 text-right tabular-nums text-muted-foreground"
                            >
                                {{ fmt(item.cogs_amount) }}
                            </td>
                            <!-- 備考 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.remarks"
                                    class="h-8 w-28"
                                />
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
                        <tr>
                            <td
                                colspan="11"
                                class="px-3 py-2 text-right text-muted-foreground"
                            >
                                合計金額（税抜）
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ fmt(subtotal) }}
                            </td>
                            <td
                                class="px-3 py-2 text-right tabular-nums text-muted-foreground"
                            >
                                {{ fmt(cogsTotal) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td
                                colspan="11"
                                class="px-3 py-2 text-right text-muted-foreground"
                            >
                                消費税
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ fmt(taxAmount) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="text-base">
                            <td colspan="11" class="px-3 py-2 text-right">
                                税込み金額
                            </td>
                            <td
                                class="px-3 py-2 text-right font-bold tabular-nums"
                            >
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
