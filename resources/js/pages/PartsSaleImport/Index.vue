<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeftRight,
    CheckCircle2,
    Columns3,
    FileUp,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import * as PartsSaleImportController from '@/actions/App/Http/Controllers/PartsSaleImportController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Work {
    id: number;
    processing_ym: string;
    monthly_f_kbn: string | null;
    control_code: string | null;
    office_code: string | null;
    hinban: string | null;
    slip_no: string | null;
    order_qty: string | null;
    order_date_raw: string | null;
    order_date: string | null;
    ship_qty: string;
    sale_date_raw: string | null;
    sale_date: string | null;
    unit_price: string;
    sale_kbn: string | null;
    les_rate: string | null;
    partner_code: string | null;
    dealer_code: string | null;
    cost_price: string;
    terminal_price: string | null;
    breakdown_code: string | null;
    maintenance_no: string | null;
    red_black_kbn: string;
    invoice_kbn: string | null;
    invoice_m_kbn: string | null;
    dispatch_source: string | null;
    staff_code: string | null;
    rank_cd: string | null;
    first_ship_kbn: string | null;
    item_code: string | null;
    item_name: string | null;
    open_kbn: string | null;
    standard_retail_price: string | null;
    model_group: string | null;
    filler: string | null;
    quantity: string;
    model_kisyu_cd: string | null;
    vehicle_kisyu_cd: string | null;
    check_flag: number;
    check_message: string | null;
}

interface Props {
    works: Work[];
    processingYm: string;
    summary: { total: number; errors: number; ok: number };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '部品売上一括取込', href: PartsSaleImportController.index.url() },
];

// ── 列表示設定 ──
type ColumnKey =
    | 'processing_ym'
    | 'monthly_f_kbn'
    | 'control_code'
    | 'office_code'
    | 'hinban'
    | 'slip_no'
    | 'order_qty'
    | 'order_date'
    | 'ship_qty'
    | 'red_black_kbn'
    | 'sale_date'
    | 'unit_price'
    | 'sale_kbn'
    | 'les_rate'
    | 'partner_code'
    | 'dealer_code'
    | 'cost_price'
    | 'terminal_price'
    | 'breakdown_code'
    | 'maintenance_no'
    | 'invoice_kbn'
    | 'invoice_m_kbn'
    | 'dispatch_source'
    | 'staff_code'
    | 'rank_cd'
    | 'first_ship_kbn'
    | 'item_code'
    | 'item_name'
    | 'open_kbn'
    | 'standard_retail_price'
    | 'model_group'
    | 'filler'
    | 'quantity'
    | 'model_kisyu_cd'
    | 'vehicle_kisyu_cd';

const LS_KEY = 'parts-sale-work.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    processing_ym:         { label: '処理年月',         visible: true  },
    monthly_f_kbn:         { label: '月次区分',         visible: false },
    control_code:          { label: '制御コード',       visible: false },
    office_code:           { label: '営業所CD',         visible: false },
    hinban:                { label: '品番',             visible: true  },
    slip_no:               { label: '伝票NO',           visible: true  },
    order_qty:             { label: '受注数',           visible: false },
    order_date:            { label: '受注日',           visible: false },
    ship_qty:              { label: '出荷数',           visible: false },
    red_black_kbn:         { label: '赤黒区分',         visible: false },
    sale_date:             { label: '売上日',           visible: true  },
    unit_price:            { label: '販売単価',         visible: true  },
    sale_kbn:              { label: '売上区分',         visible: false },
    les_rate:              { label: 'LES比率',          visible: false },
    partner_code:          { label: '販売店CD',         visible: true  },
    dealer_code:           { label: '販売店CD(全角)',   visible: false },
    cost_price:            { label: '売上原価',         visible: true  },
    terminal_price:        { label: '端末価格',         visible: false },
    breakdown_code:        { label: '内訳CD',           visible: false },
    maintenance_no:        { label: '整備注文NO',       visible: false },
    invoice_kbn:           { label: '請求区分',         visible: false },
    invoice_m_kbn:         { label: '請求月区分',       visible: false },
    dispatch_source:       { label: '出庫元',           visible: false },
    staff_code:            { label: '担当CD',           visible: false },
    rank_cd:               { label: 'ランクCD',         visible: false },
    first_ship_kbn:        { label: '初回区分',         visible: false },
    item_code:             { label: '品目CD',           visible: false },
    item_name:             { label: '品名',             visible: true  },
    open_kbn:              { label: '公開区分',         visible: false },
    standard_retail_price: { label: '標準小売価格',     visible: false },
    model_group:           { label: 'モデルグループ',   visible: false },
    filler:                { label: 'フィラー',         visible: false },
    quantity:              { label: '数量(変換後)',     visible: true  },
    model_kisyu_cd:        { label: '機種CD',           visible: false },
    vehicle_kisyu_cd:      { label: '車体機種CD',       visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(LS_KEY);
        if (saved) {
            const parsed = JSON.parse(saved) as Record<string, boolean>;
            const result = structuredClone(defaultColumns);
            for (const key of Object.keys(parsed) as ColumnKey[]) {
                if (result[key] !== undefined) result[key].visible = parsed[key];
            }
            return result;
        }
    } catch {}
    return structuredClone(defaultColumns);
}

const columns = ref(loadColumns());

function saveColumns() {
    const vis: Record<string, boolean> = {};
    for (const [k, v] of Object.entries(columns.value)) vis[k] = v.visible;
    localStorage.setItem(LS_KEY, JSON.stringify(vis));
}

function toggleColumn(key: ColumnKey) {
    columns.value[key].visible = !columns.value[key].visible;
    saveColumns();
}

const visibleKeys = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible),
);

// ── 処理年月 ──
const searchYm = ref(props.processingYm);

// ── CSV取込フォーム ──
const uploadForm = useForm({
    processing_ym: props.processingYm,
    csv_file: null as File | null,
});

function handleFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    uploadForm.csv_file = input.files?.[0] ?? null;
}

function doUpload() {
    if (!uploadForm.csv_file) {
        alert('CSVファイルを選択してください。');
        return;
    }
    uploadForm.processing_ym = searchYm.value;
    uploadForm.post(PartsSaleImportController.upload.url());
}

// ── 検索 ──
function doSearch() {
    router.get(
        PartsSaleImportController.index.url(),
        { processing_ym: searchYm.value },
        { preserveState: false, replace: true },
    );
}

// ── 売上変換処理 ──
const convertForm = useForm({ processing_ym: props.processingYm });

function doConvert() {
    if (
        !confirm(
            `${fmtYm(props.processingYm)} の部品売上データを売上に変換します。\n\nよろしいですか？`,
        )
    )
        return;
    convertForm.processing_ym = props.processingYm;
    convertForm.post(PartsSaleImportController.convert.url());
}

// ── ワーク行 CRUD ──
const dialogOpen = ref(false);
const editingId = ref<number | null>(null);

const workForm = useForm({
    processing_ym:         '',
    monthly_f_kbn:         '',
    control_code:          '',
    office_code:           '',
    hinban:                '',
    slip_no:               '',
    order_qty:             '',
    order_date:            '',
    ship_qty:              '',
    red_black_kbn:         '0',
    sale_date:             '',
    unit_price:            '',
    sale_kbn:              '',
    les_rate:              '',
    partner_code:          '',
    dealer_code:           '',
    cost_price:            '',
    terminal_price:        '',
    breakdown_code:        '',
    maintenance_no:        '',
    invoice_kbn:           '',
    invoice_m_kbn:         '',
    dispatch_source:       '',
    staff_code:            '',
    rank_cd:               '',
    first_ship_kbn:        '',
    item_code:             '',
    item_name:             '',
    open_kbn:              '',
    standard_retail_price: '',
    model_group:           '',
    filler:                '',
});

function openAddDialog() {
    editingId.value = null;
    workForm.reset();
    workForm.processing_ym = props.processingYm;
    workForm.red_black_kbn = '0';
    dialogOpen.value = true;
}

function openEditDialog(work: Work) {
    editingId.value = work.id;
    workForm.processing_ym         = work.processing_ym;
    workForm.monthly_f_kbn         = work.monthly_f_kbn         ?? '';
    workForm.control_code          = work.control_code          ?? '';
    workForm.office_code           = work.office_code           ?? '';
    workForm.hinban                = work.hinban                ?? '';
    workForm.slip_no               = work.slip_no               ?? '';
    workForm.order_qty             = work.order_qty             ?? '';
    workForm.order_date            = work.order_date            ?? '';
    workForm.ship_qty              = work.ship_qty              ?? '';
    workForm.red_black_kbn         = work.red_black_kbn         ?? '0';
    workForm.sale_date             = work.sale_date             ?? '';
    workForm.unit_price            = work.unit_price            ?? '';
    workForm.sale_kbn              = work.sale_kbn              ?? '';
    workForm.les_rate              = work.les_rate              ?? '';
    workForm.partner_code          = work.partner_code          ?? '';
    workForm.dealer_code           = work.dealer_code           ?? '';
    workForm.cost_price            = work.cost_price            ?? '';
    workForm.terminal_price        = work.terminal_price        ?? '';
    workForm.breakdown_code        = work.breakdown_code        ?? '';
    workForm.maintenance_no        = work.maintenance_no        ?? '';
    workForm.invoice_kbn           = work.invoice_kbn           ?? '';
    workForm.invoice_m_kbn         = work.invoice_m_kbn         ?? '';
    workForm.dispatch_source       = work.dispatch_source       ?? '';
    workForm.staff_code            = work.staff_code            ?? '';
    workForm.rank_cd               = work.rank_cd               ?? '';
    workForm.first_ship_kbn        = work.first_ship_kbn        ?? '';
    workForm.item_code             = work.item_code             ?? '';
    workForm.item_name             = work.item_name             ?? '';
    workForm.open_kbn              = work.open_kbn              ?? '';
    workForm.standard_retail_price = work.standard_retail_price ?? '';
    workForm.model_group           = work.model_group           ?? '';
    workForm.filler                = work.filler                ?? '';
    dialogOpen.value = true;
}

function submitWorkForm() {
    if (editingId.value !== null) {
        workForm.put(PartsSaleImportController.update.url(editingId.value), {
            onSuccess: () => { dialogOpen.value = false; },
        });
    } else {
        workForm.post(PartsSaleImportController.store.url(), {
            onSuccess: () => { dialogOpen.value = false; },
        });
    }
}

function deleteWork(work: Work) {
    if (
        !confirm(
            `品番[${work.hinban ?? ''}] 伝票NO[${work.slip_no ?? ''}] を削除してよろしいですか？`,
        )
    )
        return;
    router.delete(PartsSaleImportController.destroy.url(work.id));
}

// ── フォーマット ──
function fmtYm(ym: string): string {
    if (!ym) return '';
    const [y, m] = ym.split('-');
    return `${y}年${parseInt(m)}月`;
}

function fmtDate(d: string | null): string {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ja-JP');
}

function fmtNum(n: string | null | undefined): string {
    if (n === null || n === '' || n === undefined) return '—';
    return Number(n).toLocaleString('ja-JP');
}

function getCellValue(key: ColumnKey, work: Work): string {
    switch (key) {
        case 'processing_ym':         return fmtYm(work.processing_ym);
        case 'monthly_f_kbn':         return work.monthly_f_kbn         ?? '—';
        case 'control_code':          return work.control_code          ?? '—';
        case 'office_code':           return work.office_code           ?? '—';
        case 'hinban':                return work.hinban                ?? '—';
        case 'slip_no':               return work.slip_no               ?? '—';
        case 'order_qty':             return fmtNum(work.order_qty);
        case 'order_date':            return fmtDate(work.order_date);
        case 'ship_qty':              return fmtNum(work.ship_qty);
        case 'red_black_kbn':         return work.red_black_kbn === '2' ? '赤伝(2)' : '黒伝(0)';
        case 'sale_date':             return fmtDate(work.sale_date);
        case 'unit_price':            return fmtNum(work.unit_price);
        case 'sale_kbn':              return work.sale_kbn              ?? '—';
        case 'les_rate':              return work.les_rate              ?? '—';
        case 'partner_code':          return work.partner_code          ?? '—';
        case 'dealer_code':           return work.dealer_code           ?? '—';
        case 'cost_price':            return fmtNum(work.cost_price);
        case 'terminal_price':        return work.terminal_price        ?? '—';
        case 'breakdown_code':        return work.breakdown_code        ?? '—';
        case 'maintenance_no':        return work.maintenance_no        ?? '—';
        case 'invoice_kbn':           return work.invoice_kbn           ?? '—';
        case 'invoice_m_kbn':         return work.invoice_m_kbn         ?? '—';
        case 'dispatch_source':       return work.dispatch_source       ?? '—';
        case 'staff_code':            return work.staff_code            ?? '—';
        case 'rank_cd':               return work.rank_cd               ?? '—';
        case 'first_ship_kbn':        return work.first_ship_kbn        ?? '—';
        case 'item_code':             return work.item_code             ?? '—';
        case 'item_name':             return work.item_name             ?? '—';
        case 'open_kbn':              return work.open_kbn              ?? '—';
        case 'standard_retail_price': return work.standard_retail_price ?? '—';
        case 'model_group':           return work.model_group           ?? '—';
        case 'filler':                return work.filler                ?? '—';
        case 'quantity':              return fmtNum(work.quantity);
        case 'model_kisyu_cd':        return work.model_kisyu_cd        ?? '—';
        case 'vehicle_kisyu_cd':      return work.vehicle_kisyu_cd      ?? '—';
        default:                      return '—';
    }
}

const numericColumns = new Set<ColumnKey>([
    'order_qty', 'ship_qty', 'unit_price', 'cost_price', 'quantity',
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="部品売上一括取込" />
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">部品売上一括取込</h1>

            <!-- 上部コントロールパネル -->
            <div class="rounded-md border p-4">
                <div class="flex flex-wrap items-end gap-4">
                    <!-- 処理年月 -->
                    <div class="flex flex-col gap-1.5">
                        <Label>処理年月</Label>
                        <Input type="month" v-model="searchYm" class="w-36" />
                    </div>

                    <!-- CSVファイル -->
                    <div class="flex flex-col gap-1.5">
                        <Label>CSVファイル（B4URIFD.DAT）</Label>
                        <Input
                            type="file"
                            accept=".dat,.csv,.txt"
                            class="w-72"
                            @change="handleFileChange"
                        />
                        <p
                            v-if="uploadForm.errors.csv_file"
                            class="text-xs text-destructive"
                        >
                            {{ uploadForm.errors.csv_file }}
                        </p>
                    </div>

                    <!-- ボタン群 -->
                    <div class="flex gap-2 pb-0.5">
                        <Button @click="doUpload" :disabled="uploadForm.processing">
                            <FileUp class="mr-1 h-4 w-4" />
                            {{ uploadForm.processing ? '取込中...' : '取込' }}
                        </Button>

                        <Button @click="doSearch" variant="secondary">
                            <Search class="mr-1 h-4 w-4" />
                            検索
                        </Button>

                        <Button
                            @click="doConvert"
                            :disabled="convertForm.processing || summary.total === 0 || summary.errors > 0"
                            variant="outline"
                            class="border-green-500 text-green-700 hover:bg-green-50 disabled:opacity-50"
                        >
                            <ArrowLeftRight class="mr-1 h-4 w-4" />
                            {{ convertForm.processing ? '変換中...' : '売上変換処理' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- サマリー -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <span class="text-muted-foreground">
                    全 <strong class="text-foreground">{{ summary.total }}</strong> 件
                </span>
                <span class="flex items-center gap-1 text-green-700">
                    <CheckCircle2 class="h-4 w-4" />
                    正常: <strong>{{ summary.ok }}</strong> 件
                </span>
                <span
                    v-if="summary.errors > 0"
                    class="flex items-center gap-1 text-destructive"
                >
                    <AlertCircle class="h-4 w-4" />
                    エラー: <strong>{{ summary.errors }}</strong> 件（修正後に売上変換を実行してください）
                </span>
            </div>

            <!-- テーブルヘッダー -->
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">
                    {{ fmtYm(processingYm) }} のデータ
                </span>
                <div class="flex items-center gap-2">
                    <!-- 列表示切替 -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Columns3 class="mr-1 h-4 w-4" />列
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="max-h-80 w-52 overflow-y-auto" align="end">
                            <DropdownMenuLabel>表示列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="key in (Object.keys(columns) as ColumnKey[])"
                                :key="key"
                                :checked="columns[key].visible"
                                @update:checked="toggleColumn(key)"
                            >
                                {{ columns[key].label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button size="sm" @click="openAddDialog">
                        <Plus class="mr-1 h-4 w-4" />行追加
                    </Button>
                </div>
            </div>

            <!-- テーブル -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th
                                v-for="key in visibleKeys"
                                :key="key"
                                class="whitespace-nowrap px-3 py-2 font-medium"
                                :class="numericColumns.has(key) ? 'text-right' : 'text-left'"
                            >
                                {{ columns[key].label }}
                            </th>
                            <th class="whitespace-nowrap px-3 py-2 text-left font-medium">チェック</th>
                            <th class="whitespace-nowrap px-3 py-2 text-left font-medium">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="work in works"
                            :key="work.id"
                            class="border-t transition-colors"
                            :class="
                                work.check_flag !== 0
                                    ? 'bg-red-50 hover:bg-red-100'
                                    : 'hover:bg-muted/30'
                            "
                        >
                            <td
                                v-for="key in visibleKeys"
                                :key="key"
                                class="px-3 py-2 text-xs"
                                :class="[
                                    numericColumns.has(key) ? 'text-right tabular-nums' : 'text-left',
                                    key === 'hinban' ? 'font-mono' : '',
                                    key === 'item_name' ? 'max-w-[160px] truncate' : '',
                                ]"
                            >
                                {{ getCellValue(key, work) }}
                            </td>
                            <td class="px-3 py-2">
                                <span
                                    v-if="work.check_flag === 0"
                                    class="flex items-center gap-1 text-xs text-green-700"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />正常
                                </span>
                                <span
                                    v-else
                                    class="flex items-center gap-1 text-xs text-destructive"
                                    :title="work.check_message ?? ''"
                                >
                                    <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                                    <span class="max-w-[200px] truncate">{{
                                        work.check_message
                                    }}</span>
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        title="編集"
                                        @click="openEditDialog(work)"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive hover:text-destructive"
                                        title="削除"
                                        @click="deleteWork(work)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="works.length === 0">
                            <td
                                :colspan="visibleKeys.length + 2"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                データがありません。処理年月を選択してCSVを取込んでください。
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 追加・編集ダイアログ -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-h-[90vh] max-w-4xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{
                        editingId !== null ? '行を編集' : '行を追加'
                    }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitWorkForm" class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-4">

                        <!-- ── 基本情報 ── -->
                        <div class="col-span-full border-b pb-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">基本情報</span>
                        </div>

                        <!-- 処理年月 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>処理年月 <span class="text-destructive">*</span></Label>
                            <Input type="month" v-model="workForm.processing_ym" />
                            <p v-if="workForm.errors.processing_ym" class="text-xs text-destructive">{{ workForm.errors.processing_ym }}</p>
                        </div>
                        <!-- 月次区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>月次区分</Label>
                            <Input v-model="workForm.monthly_f_kbn" maxlength="5" />
                            <p v-if="workForm.errors.monthly_f_kbn" class="text-xs text-destructive">{{ workForm.errors.monthly_f_kbn }}</p>
                        </div>
                        <!-- 制御コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>制御コード</Label>
                            <Input v-model="workForm.control_code" maxlength="5" />
                            <p v-if="workForm.errors.control_code" class="text-xs text-destructive">{{ workForm.errors.control_code }}</p>
                        </div>
                        <!-- 営業所コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>営業所CD</Label>
                            <Input v-model="workForm.office_code" maxlength="10" />
                            <p v-if="workForm.errors.office_code" class="text-xs text-destructive">{{ workForm.errors.office_code }}</p>
                        </div>

                        <!-- ── 伝票・受注情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">伝票・受注情報</span>
                        </div>

                        <!-- 品番 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>品番 <span class="text-destructive">*</span></Label>
                            <Input v-model="workForm.hinban" maxlength="20" placeholder="13桁品番" class="font-mono" />
                            <p v-if="workForm.errors.hinban" class="text-xs text-destructive">{{ workForm.errors.hinban }}</p>
                        </div>
                        <!-- 伝票NO -->
                        <div class="flex flex-col gap-1.5">
                            <Label>伝票NO <span class="text-destructive">*</span></Label>
                            <Input v-model="workForm.slip_no" maxlength="20" />
                            <p v-if="workForm.errors.slip_no" class="text-xs text-destructive">{{ workForm.errors.slip_no }}</p>
                        </div>
                        <!-- 受注日 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>受注日</Label>
                            <Input type="date" v-model="workForm.order_date" />
                            <p v-if="workForm.errors.order_date" class="text-xs text-destructive">{{ workForm.errors.order_date }}</p>
                        </div>
                        <!-- 受注数 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>受注数</Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.order_qty" />
                            <p v-if="workForm.errors.order_qty" class="text-xs text-destructive">{{ workForm.errors.order_qty }}</p>
                        </div>
                        <!-- 売上日 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>売上日 <span class="text-destructive">*</span></Label>
                            <Input type="date" v-model="workForm.sale_date" />
                            <p v-if="workForm.errors.sale_date" class="text-xs text-destructive">{{ workForm.errors.sale_date }}</p>
                        </div>
                        <!-- 出荷数 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>出荷数 <span class="text-destructive">*</span></Label>
                            <Input type="number" step="0.01" v-model="workForm.ship_qty" />
                            <p v-if="workForm.errors.ship_qty" class="text-xs text-destructive">{{ workForm.errors.ship_qty }}</p>
                        </div>
                        <!-- 赤黒区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>赤黒区分</Label>
                            <Select v-model="workForm.red_black_kbn">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="0">黒伝（0）</SelectItem>
                                    <SelectItem value="2">赤伝（2）</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- ── 価格情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">価格情報</span>
                        </div>

                        <!-- 販売単価 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>販売単価 <span class="text-destructive">*</span></Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.unit_price" />
                            <p v-if="workForm.errors.unit_price" class="text-xs text-destructive">{{ workForm.errors.unit_price }}</p>
                        </div>
                        <!-- 売上原価 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>売上原価</Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.cost_price" />
                            <p v-if="workForm.errors.cost_price" class="text-xs text-destructive">{{ workForm.errors.cost_price }}</p>
                        </div>
                        <!-- 端末価格 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>端末価格</Label>
                            <Input v-model="workForm.terminal_price" maxlength="20" />
                            <p v-if="workForm.errors.terminal_price" class="text-xs text-destructive">{{ workForm.errors.terminal_price }}</p>
                        </div>
                        <!-- 標準小売価格 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>標準小売価格</Label>
                            <Input v-model="workForm.standard_retail_price" maxlength="20" />
                            <p v-if="workForm.errors.standard_retail_price" class="text-xs text-destructive">{{ workForm.errors.standard_retail_price }}</p>
                        </div>
                        <!-- LES比率 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>LES比率</Label>
                            <Input v-model="workForm.les_rate" maxlength="10" />
                            <p v-if="workForm.errors.les_rate" class="text-xs text-destructive">{{ workForm.errors.les_rate }}</p>
                        </div>

                        <!-- ── 得意先・請求情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">得意先・請求情報</span>
                        </div>

                        <!-- 販売店コード（半角） -->
                        <div class="flex flex-col gap-1.5">
                            <Label>販売店CD(半角) <span class="text-destructive">*</span></Label>
                            <Input v-model="workForm.partner_code" maxlength="20" />
                            <p v-if="workForm.errors.partner_code" class="text-xs text-destructive">{{ workForm.errors.partner_code }}</p>
                        </div>
                        <!-- 販売店コード（全角） -->
                        <div class="flex flex-col gap-1.5">
                            <Label>販売店CD(全角)</Label>
                            <Input v-model="workForm.dealer_code" maxlength="20" />
                            <p v-if="workForm.errors.dealer_code" class="text-xs text-destructive">{{ workForm.errors.dealer_code }}</p>
                        </div>
                        <!-- 売上区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>売上区分</Label>
                            <Input v-model="workForm.sale_kbn" maxlength="5" />
                            <p v-if="workForm.errors.sale_kbn" class="text-xs text-destructive">{{ workForm.errors.sale_kbn }}</p>
                        </div>
                        <!-- 請求区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>請求区分</Label>
                            <Input v-model="workForm.invoice_kbn" maxlength="5" />
                            <p v-if="workForm.errors.invoice_kbn" class="text-xs text-destructive">{{ workForm.errors.invoice_kbn }}</p>
                        </div>
                        <!-- 請求月区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>請求月区分</Label>
                            <Input v-model="workForm.invoice_m_kbn" maxlength="5" />
                            <p v-if="workForm.errors.invoice_m_kbn" class="text-xs text-destructive">{{ workForm.errors.invoice_m_kbn }}</p>
                        </div>

                        <!-- ── 出庫・担当情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">出庫・担当情報</span>
                        </div>

                        <!-- 出庫元 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>出庫元</Label>
                            <Input v-model="workForm.dispatch_source" maxlength="20" />
                            <p v-if="workForm.errors.dispatch_source" class="text-xs text-destructive">{{ workForm.errors.dispatch_source }}</p>
                        </div>
                        <!-- 担当コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>担当CD</Label>
                            <Input v-model="workForm.staff_code" maxlength="20" />
                            <p v-if="workForm.errors.staff_code" class="text-xs text-destructive">{{ workForm.errors.staff_code }}</p>
                        </div>
                        <!-- ランクCD -->
                        <div class="flex flex-col gap-1.5">
                            <Label>ランクCD</Label>
                            <Input v-model="workForm.rank_cd" maxlength="5" />
                            <p v-if="workForm.errors.rank_cd" class="text-xs text-destructive">{{ workForm.errors.rank_cd }}</p>
                        </div>
                        <!-- 初回出荷区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>初回区分</Label>
                            <Input v-model="workForm.first_ship_kbn" maxlength="5" />
                            <p v-if="workForm.errors.first_ship_kbn" class="text-xs text-destructive">{{ workForm.errors.first_ship_kbn }}</p>
                        </div>
                        <!-- 内訳コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>内訳CD</Label>
                            <Input v-model="workForm.breakdown_code" maxlength="10" />
                            <p v-if="workForm.errors.breakdown_code" class="text-xs text-destructive">{{ workForm.errors.breakdown_code }}</p>
                        </div>
                        <!-- 公開区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>公開区分</Label>
                            <Input v-model="workForm.open_kbn" maxlength="5" />
                            <p v-if="workForm.errors.open_kbn" class="text-xs text-destructive">{{ workForm.errors.open_kbn }}</p>
                        </div>

                        <!-- ── 商品情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">商品情報</span>
                        </div>

                        <!-- 品目コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>品目CD</Label>
                            <Input v-model="workForm.item_code" maxlength="20" />
                            <p v-if="workForm.errors.item_code" class="text-xs text-destructive">{{ workForm.errors.item_code }}</p>
                        </div>
                        <!-- モデルグループ -->
                        <div class="flex flex-col gap-1.5">
                            <Label>モデルグループ</Label>
                            <Input v-model="workForm.model_group" maxlength="10" />
                            <p v-if="workForm.errors.model_group" class="text-xs text-destructive">{{ workForm.errors.model_group }}</p>
                        </div>
                        <!-- 品名 -->
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>品名</Label>
                            <Input v-model="workForm.item_name" maxlength="200" />
                            <p v-if="workForm.errors.item_name" class="text-xs text-destructive">{{ workForm.errors.item_name }}</p>
                        </div>

                        <!-- ── その他 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">その他</span>
                        </div>

                        <!-- 整備注文NO -->
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>整備注文NO</Label>
                            <Input v-model="workForm.maintenance_no" maxlength="100" />
                            <p v-if="workForm.errors.maintenance_no" class="text-xs text-destructive">{{ workForm.errors.maintenance_no }}</p>
                        </div>
                        <!-- フィラー -->
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>フィラー</Label>
                            <Input v-model="workForm.filler" maxlength="100" />
                            <p v-if="workForm.errors.filler" class="text-xs text-destructive">{{ workForm.errors.filler }}</p>
                        </div>

                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="dialogOpen = false">キャンセル</Button>
                        <Button type="submit" :disabled="workForm.processing">
                            {{ workForm.processing ? '保存中...' : editingId !== null ? '更新' : '追加' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
