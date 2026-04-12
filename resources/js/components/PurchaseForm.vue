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

interface Supplier {
    id: number;
    name: string;
}
interface Employee {
    id: number;
    name: string;
}
interface VehicleOption {
    id: number;
    kisyu_cd: string;
    frame_no: string;
    iro_cd: string | null;
    kisyu_nm: string | null;
    sre_tan: string;
}
interface VehicleModelOption {
    kisyu_cd: string;
    iro_cd: string | null;
    kisyu_nm: string | null;
    sre_tan: string;
}
interface Warehouse {
    code: string;
    name: string;
}
interface PurchaseItem {
    vehicle_id: number | null;
    kisyu_cd: string;
    frame_no: string;
    warehouse_code: string;
    iro_cd: string;
    kisyu_nm: string;
    quantity: string;
    unit: string;
    sre_tan: string;
    tax_rate: string;
    purchase_amount: number;
    remarks: string;
}
interface FormData {
    supplier_id: string;
    employee_id: string;
    purchase_date: string;
    subject: string;
    status: string;
    remarks: string;
    items: PurchaseItem[];
    [key: string]: unknown;
}

const props = defineProps<{
    form: InertiaForm<FormData>;
    suppliers: Supplier[];
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

const supplierOptions = computed(() =>
    props.suppliers.map((s) => ({ value: String(s.id), label: s.name })),
);

const kisyuCdOptions = computed(() => {
    const seen = new Set<string>();
    const list: { value: string; label: string }[] = [];
    for (const m of props.vehicleModels) {
        if (!seen.has(m.kisyu_cd)) { seen.add(m.kisyu_cd); list.push({ value: m.kisyu_cd, label: m.kisyu_cd }); }
    }
    for (const v of props.vehicles) {
        if (!seen.has(v.kisyu_cd)) { seen.add(v.kisyu_cd); list.push({ value: v.kisyu_cd, label: v.kisyu_cd }); }
    }
    return list;
});

const warehouseOptions = computed(() =>
    props.warehouses.map((w) => ({ value: w.code, label: `${w.code} ${w.name}` })),
);

function frameNoList(kisyuCd: string) {
    return props.vehicles.filter((v) => v.kisyu_cd === kisyuCd);
}

function addItem() {
    props.form.items.push({
        vehicle_id: null,
        kisyu_cd: '',
        frame_no: '',
        warehouse_code: '',
        iro_cd: '',
        kisyu_nm: '',
        quantity: '1',
        unit: '台',
        sre_tan: '0',
        tax_rate: '10',
        purchase_amount: 0,
        remarks: '',
    });
}

function removeItem(index: number) {
    props.form.items.splice(index, 1);
}

function iroCdOptions(kisyuCd: string) {
    const seen = new Set<string>();
    return props.vehicleModels
        .filter((m) => m.kisyu_cd === kisyuCd && m.iro_cd)
        .filter((m) => { if (seen.has(m.iro_cd!)) return false; seen.add(m.iro_cd!); return true; })
        .map((m) => ({ value: m.iro_cd!, label: m.iro_cd! }));
}

function onKisyuCdChange(i: number, val: string) {
    props.form.items[i].kisyu_cd = val;
    props.form.items[i].frame_no = '';
    props.form.items[i].iro_cd = '';
    props.form.items[i].kisyu_nm = '';
    props.form.items[i].sre_tan = '0';
    props.form.items[i].vehicle_id = null;
    recalcItem(i);
}

function onIroCdChange(i: number, val: string) {
    props.form.items[i].iro_cd = val;
    const vm = props.vehicleModels.find(
        (m) => m.kisyu_cd === props.form.items[i].kisyu_cd && m.iro_cd === val,
    );
    if (vm) {
        props.form.items[i].kisyu_nm = vm.kisyu_nm ?? '';
        props.form.items[i].sre_tan = vm.sre_tan ?? '0';
    }
    recalcItem(i);
}

function onFrameNoChange(i: number) {
    const frameNo = props.form.items[i].frame_no;
    const vehicle = props.vehicles.find(
        (v) => v.kisyu_cd === props.form.items[i].kisyu_cd && v.frame_no === frameNo,
    );
    if (vehicle) {
        props.form.items[i].iro_cd = vehicle.iro_cd ?? '';
        props.form.items[i].kisyu_nm = vehicle.kisyu_nm ?? '';
        props.form.items[i].sre_tan = vehicle.sre_tan ?? '0';
        props.form.items[i].vehicle_id = vehicle.id;
    }
    recalcItem(i);
}

function recalcItem(i: number) {
    const item = props.form.items[i];
    const qty = parseFloat(item.quantity) || 0;
    const sre = parseFloat(item.sre_tan) || 0;
    item.purchase_amount = Math.round(qty * sre * 100) / 100;
}

const subtotal = computed(() =>
    props.form.items.reduce((s, i) => s + (i.purchase_amount || 0), 0),
);

const taxAmount = computed(() =>
    props.form.items.reduce((s, i) => {
        const rate = parseInt(i.tax_rate) || 0;
        return s + Math.round((i.purchase_amount || 0) * rate) / 100;
    }, 0),
);

const totalAmount = computed(() => subtotal.value + taxAmount.value);

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
                <!-- 仕入先 -->
                <div class="grid gap-1.5">
                    <Label>仕入先 <span class="text-destructive">*</span></Label>
                    <Combobox
                        :options="supplierOptions"
                        :model-value="form.supplier_id"
                        placeholder="仕入先を検索..."
                        :class="{
                            '[&_input]:border-destructive': form.errors.supplier_id,
                        }"
                        @update:model-value="(v) => (form.supplier_id = v)"
                    />
                    <p v-if="form.errors.supplier_id" class="text-xs text-destructive">
                        {{ form.errors.supplier_id }}
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
                                >{{ e.name }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <!-- 仕入日 -->
                <div class="grid gap-1.5">
                    <Label>仕入日 <span class="text-destructive">*</span></Label>
                    <Input
                        type="date"
                        v-model="form.purchase_date"
                        :class="{ 'border-destructive': form.errors.purchase_date }"
                    />
                    <p v-if="form.errors.purchase_date" class="text-xs text-destructive">
                        {{ form.errors.purchase_date }}
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
                                >{{ label }}</SelectItem
                            >
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
                        placeholder="例: ○○仕入"
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
                            <th class="px-3 py-2 text-left font-medium">機種コード（商品）<span class="text-destructive">*</span></th>
                            <th class="px-3 py-2 text-left font-medium">フレームNo（品番）<span class="text-destructive">*</span></th>
                            <th class="px-3 py-2 text-left font-medium">色コード</th>
                            <th class="px-3 py-2 text-left font-medium">倉庫</th>
                            <th class="px-3 py-2 text-left font-medium">機種名（商品名）</th>
                            <th class="px-3 py-2 text-right font-medium">数量 <span class="text-destructive">*</span></th>
                            <th class="px-3 py-2 text-left font-medium">単位</th>
                            <th class="px-3 py-2 text-right font-medium">仕入単価</th>
                            <th class="px-3 py-2 text-center font-medium">税率</th>
                            <th class="px-3 py-2 text-right font-medium">仕入金額</th>
                            <th class="px-3 py-2 text-left font-medium">備考</th>
                            <th class="w-10 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in form.items" :key="i" class="border-t">
                            <!-- 機種コード -->
                            <td class="px-2 py-1.5">
                                <Combobox
                                    :options="kisyuCdOptions"
                                    :model-value="item.kisyu_cd"
                                    placeholder="機種コード（商品）..."
                                    :class="['w-36', getItemError(i, 'kisyu_cd') ? '[&_input]:border-destructive' : '']"
                                    @update:model-value="(v) => onKisyuCdChange(i, v)"
                                />
                                <p v-if="getItemError(i, 'kisyu_cd')" class="mt-0.5 text-xs text-destructive">{{ getItemError(i, 'kisyu_cd') }}</p>
                            </td>
                            <!-- フレームNo -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.frame_no"
                                    :list="`frame-no-${i}`"
                                    placeholder="フレームNo（品番）..."
                                    :class="['h-8 w-40', getItemError(i, 'frame_no') ? 'border-destructive' : '']"
                                    @change="onFrameNoChange(i)"
                                />
                                <datalist :id="`frame-no-${i}`">
                                    <option v-for="v in frameNoList(item.kisyu_cd)" :key="v.frame_no" :value="v.frame_no" />
                                </datalist>
                                <p v-if="getItemError(i, 'frame_no')" class="mt-0.5 text-xs text-destructive">{{ getItemError(i, 'frame_no') }}</p>
                            </td>
                            <!-- 色コード -->
                            <td class="px-2 py-1.5">
                                <Combobox
                                    :options="iroCdOptions(item.kisyu_cd)"
                                    :model-value="item.iro_cd"
                                    placeholder="色..."
                                    class="w-24"
                                    @update:model-value="(v) => onIroCdChange(i, v)"
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
                                <Input v-model="item.kisyu_nm" class="h-8 min-w-40" readonly tabindex="-1" />
                            </td>
                            <!-- 数量 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="h-8 w-20 text-right tabular-nums"
                                    :class="{ 'border-destructive': getItemError(i, 'quantity') }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 単位 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.unit" class="h-8 w-14" />
                            </td>
                            <!-- 仕入単価 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.sre_tan"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-24 text-right tabular-nums"
                                    :class="{ 'border-destructive': getItemError(i, 'sre_tan') }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 税率 -->
                            <td class="px-2 py-1.5">
                                <Select
                                    :model-value="item.tax_rate"
                                    @update:model-value="(v) => { item.tax_rate = v; recalcItem(i); }"
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
                            <!-- 仕入金額 -->
                            <td class="px-2 py-1.5 text-right tabular-nums">
                                {{ fmt(item.purchase_amount) }}
                            </td>
                            <!-- 備考 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.remarks" class="h-8 w-28" />
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
                            <td colspan="9" class="px-3 py-2 text-right text-muted-foreground">
                                合計金額（税抜）
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ fmt(subtotal) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="px-3 py-2 text-right text-muted-foreground">
                                消費税
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ fmt(taxAmount) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="text-base">
                            <td colspan="9" class="px-3 py-2 text-right">税込み仕入金額</td>
                            <td class="px-3 py-2 text-right font-bold tabular-nums">{{ fmt(totalAmount) }}</td>
                            <td colspan="2"></td>
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
