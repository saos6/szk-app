<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDown,
    ArrowLeftRight,
    ArrowUp,
    ArrowUpDown,
    CheckCircle2,
    Columns3,
    Download,
    FileUp,
    Lock,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Trash2,
    X,
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
    monthly_f_type: string | null;
    control_code: string | null;
    office_code: string | null;
    part_number: string | null;
    slip_number: string | null;
    order_qty: string | null;
    order_date_raw: string | null;
    order_date: string | null;
    ship_qty: string;
    sale_date_raw: string | null;
    sale_date: string | null;
    unit_price: string;
    sale_type: string | null;
    discount_rate: string | null;
    partner_code: string | null;
    dealer_code: string | null;
    cost_price: string;
    terminal_price: string | null;
    breakdown_code: string | null;
    maintenance_no: string | null;
    reversal_type: string;
    invoice_type: string | null;
    invoice_monthly_type: string | null;
    dispatch_source: string | null;
    staff_code: string | null;
    rank_code: string | null;
    first_shipment_type: string | null;
    item_code: string | null;
    item_name: string | null;
    open_type: string | null;
    standard_retail_price: string | null;
    model_group: string | null;
    filler: string | null;
    quantity: string;
    model_code: string | null;
    vehicle_code: string | null;
    check_flag: number;
    check_message: string | null;
    converted_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    data: Work[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    works: Paginator;
    processingYm: string;
    summary: { total: number; errors: number; ok: number; converted: number };
    filters: { per_page: string; search: string; sort: string; direction: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '売上取込', href: PartsSaleImportController.index.url() },
];

// ── 列表示設定 ──
type ColumnKey =
    | 'processing_ym'
    | 'monthly_f_type'
    | 'control_code'
    | 'office_code'
    | 'part_number'
    | 'slip_number'
    | 'order_qty'
    | 'order_date'
    | 'ship_qty'
    | 'reversal_type'
    | 'sale_date'
    | 'unit_price'
    | 'sale_type'
    | 'discount_rate'
    | 'partner_code'
    | 'dealer_code'
    | 'cost_price'
    | 'terminal_price'
    | 'breakdown_code'
    | 'maintenance_no'
    | 'invoice_type'
    | 'invoice_monthly_type'
    | 'dispatch_source'
    | 'staff_code'
    | 'rank_code'
    | 'first_shipment_type'
    | 'item_code'
    | 'item_name'
    | 'open_type'
    | 'standard_retail_price'
    | 'model_group'
    | 'filler'
    | 'quantity'
    | 'model_code'
    | 'vehicle_code';

const LS_KEY = 'parts-sale-work.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    processing_ym:         { label: '処理年月',         visible: true  },
    monthly_f_type:         { label: '月次区分',         visible: false },
    control_code:          { label: '制御コード',       visible: false },
    office_code:           { label: '営業所CD',         visible: false },
    part_number:            { label: '品番',             visible: true  },
    slip_number:            { label: '伝票NO',           visible: true  },
    order_qty:             { label: '受注数',           visible: false },
    order_date:            { label: '受注日',           visible: false },
    ship_qty:              { label: '出荷数',           visible: false },
    reversal_type:          { label: '赤黒区分',         visible: false },
    sale_date:             { label: '売上日',           visible: true  },
    unit_price:            { label: '販売単価',         visible: true  },
    sale_type:              { label: '売上区分',         visible: false },
    discount_rate:          { label: 'LES比率',          visible: false },
    partner_code:          { label: '販売店CD',         visible: true  },
    dealer_code:           { label: '販売店CD(全角)',   visible: false },
    cost_price:            { label: '売上原価',         visible: true  },
    terminal_price:        { label: '端末価格',         visible: false },
    breakdown_code:        { label: '内訳CD',           visible: false },
    maintenance_no:        { label: '整備注文NO',       visible: false },
    invoice_type:           { label: '請求区分',         visible: false },
    invoice_monthly_type:   { label: '請求月区分',       visible: false },
    dispatch_source:       { label: '出庫元',           visible: false },
    staff_code:            { label: '担当CD',           visible: false },
    rank_code:              { label: 'ランクCD',         visible: false },
    first_shipment_type:    { label: '初回区分',         visible: false },
    item_code:             { label: '品目CD',           visible: false },
    item_name:             { label: '品名',             visible: true  },
    open_type:              { label: '公開区分',         visible: false },
    standard_retail_price: { label: '標準小売価格',     visible: false },
    model_group:           { label: 'モデルグループ',   visible: false },
    filler:                { label: 'フィラー',         visible: false },
    quantity:              { label: '数量(変換後)',     visible: true  },
    model_code:             { label: '機種CD',           visible: false },
    vehicle_code:           { label: '車体機種CD',       visible: false },
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

// ── 処理年月 / 検索 / ソート / ページング ──
const searchYm  = ref(props.processingYm);
const search    = ref(props.filters.search ?? '');
const sortField = ref(props.filters.sort ?? 'sale_date');
const sortDir   = ref<'asc' | 'desc'>((props.filters.direction as 'asc' | 'desc') ?? 'asc');
const perPage   = ref(props.filters.per_page ?? '50');

// ソート可能列: ColumnKey → DBフィールド名 のマッピング
const sortableColumns: Partial<Record<ColumnKey, string>> = {
    processing_ym: 'processing_ym',
    part_number:   'part_number',
    slip_number:   'slip_number',
    order_date:    'order_date',
    sale_date:     'sale_date',
    ship_qty:      'ship_qty',
    unit_price:    'unit_price',
    cost_price:    'cost_price',
    partner_code:  'partner_code',
    item_name:     'item_name',
    quantity:      'quantity',
};

function navigate(params: Record<string, string>) {
    router.get(PartsSaleImportController.index.url(), params, {
        preserveState: false,
        replace: true,
    });
}

function currentParams(): Record<string, string> {
    return {
        processing_ym: props.processingYm,
        per_page:      perPage.value,
        search:        search.value,
        sort:          sortField.value,
        direction:     sortDir.value,
    };
}

function doSearch() {
    navigate({ ...currentParams(), processing_ym: searchYm.value });
}

function clearSearch() {
    search.value = '';
    navigate({ ...currentParams(), search: '' });
}

function toggleSort(field: string) {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'asc';
    }
    navigate({ ...currentParams(), sort: sortField.value, direction: sortDir.value });
}

function handlePerPageChange(value: string) {
    perPage.value = value;
    navigate({ ...currentParams(), per_page: value });
}

function paginationLabel(label: string): string {
    if (label.includes('Previous') || label === '&laquo; Previous') return '«';
    if (label.includes('Next') || label === 'Next &raquo;') return '»';
    return label;
}

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

// ── 照合フォーム ──
const checkForm = useForm({ processing_ym: props.processingYm });

function doCheck() {
    checkForm.processing_ym = props.processingYm;
    checkForm.post(PartsSaleImportController.check.url());
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

// ── 一括削除フォーム ──
const bulkDestroyForm = useForm({ processing_ym: props.processingYm });

function doBulkDestroy() {
    if (
        !confirm(
            `${fmtYm(props.processingYm)} の部品売上データ ${props.summary.total}件を全て削除します。\n\nよろしいですか？`,
        )
    )
        return;
    bulkDestroyForm.processing_ym = props.processingYm;
    bulkDestroyForm.delete(PartsSaleImportController.bulkDestroy.url());
}

// ── Excel出力 ──
function doExport() {
    const params = new URLSearchParams({ processing_ym: props.processingYm });
    window.location.href = `${PartsSaleImportController.exportMethod.url()}?${params}`;
}

// ── ワーク行 CRUD ──
const dialogOpen = ref(false);
const editingId  = ref<number | null>(null);

const workForm = useForm({
    processing_ym:         '',
    monthly_f_type:         '',
    control_code:          '',
    office_code:           '',
    part_number:            '',
    slip_number:            '',
    order_qty:             '',
    order_date:            '',
    ship_qty:              '',
    reversal_type:          '0',
    sale_date:             '',
    unit_price:            '',
    sale_type:              '',
    discount_rate:          '',
    partner_code:          '',
    dealer_code:           '',
    cost_price:            '',
    terminal_price:        '',
    breakdown_code:        '',
    maintenance_no:        '',
    invoice_type:           '',
    invoice_monthly_type:   '',
    dispatch_source:       '',
    staff_code:            '',
    rank_code:              '',
    first_shipment_type:    '',
    item_code:             '',
    item_name:             '',
    open_type:              '',
    standard_retail_price: '',
    model_group:           '',
    filler:                '',
});

function openAddDialog() {
    editingId.value = null;
    workForm.reset();
    workForm.processing_ym = props.processingYm;
    workForm.reversal_type = '0';
    dialogOpen.value = true;
}

function openEditDialog(work: Work) {
    editingId.value = work.id;
    workForm.processing_ym         = work.processing_ym;
    workForm.monthly_f_type         = work.monthly_f_type         ?? '';
    workForm.control_code          = work.control_code          ?? '';
    workForm.office_code           = work.office_code           ?? '';
    workForm.part_number            = work.part_number            ?? '';
    workForm.slip_number            = work.slip_number            ?? '';
    workForm.order_qty             = work.order_qty             ?? '';
    workForm.order_date            = work.order_date            ?? '';
    workForm.ship_qty              = work.ship_qty              ?? '';
    workForm.reversal_type          = work.reversal_type          ?? '0';
    workForm.sale_date             = work.sale_date             ?? '';
    workForm.unit_price            = work.unit_price            ?? '';
    workForm.sale_type              = work.sale_type              ?? '';
    workForm.discount_rate          = work.discount_rate          ?? '';
    workForm.partner_code          = work.partner_code          ?? '';
    workForm.dealer_code           = work.dealer_code           ?? '';
    workForm.cost_price            = work.cost_price            ?? '';
    workForm.terminal_price        = work.terminal_price        ?? '';
    workForm.breakdown_code        = work.breakdown_code        ?? '';
    workForm.maintenance_no        = work.maintenance_no        ?? '';
    workForm.invoice_type           = work.invoice_type           ?? '';
    workForm.invoice_monthly_type   = work.invoice_monthly_type   ?? '';
    workForm.dispatch_source       = work.dispatch_source       ?? '';
    workForm.staff_code            = work.staff_code            ?? '';
    workForm.rank_code              = work.rank_code              ?? '';
    workForm.first_shipment_type    = work.first_shipment_type    ?? '';
    workForm.item_code             = work.item_code             ?? '';
    workForm.item_name             = work.item_name             ?? '';
    workForm.open_type              = work.open_type              ?? '';
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
            `品番[${work.part_number ?? ''}] 伝票NO[${work.slip_number ?? ''}] を削除してよろしいですか？`,
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
        case 'monthly_f_type':         return work.monthly_f_type         ?? '—';
        case 'control_code':          return work.control_code          ?? '—';
        case 'office_code':           return work.office_code           ?? '—';
        case 'part_number':            return work.part_number                ?? '—';
        case 'slip_number':            return work.slip_number               ?? '—';
        case 'order_qty':             return fmtNum(work.order_qty);
        case 'order_date':            return fmtDate(work.order_date);
        case 'ship_qty':              return fmtNum(work.ship_qty);
        case 'reversal_type':          return work.reversal_type === '2' ? '赤伝(2)' : '黒伝(0)';
        case 'sale_date':             return fmtDate(work.sale_date);
        case 'unit_price':            return fmtNum(work.unit_price);
        case 'sale_type':              return work.sale_type              ?? '—';
        case 'discount_rate':          return work.discount_rate              ?? '—';
        case 'partner_code':          return work.partner_code          ?? '—';
        case 'dealer_code':           return work.dealer_code           ?? '—';
        case 'cost_price':            return fmtNum(work.cost_price);
        case 'terminal_price':        return work.terminal_price        ?? '—';
        case 'breakdown_code':        return work.breakdown_code        ?? '—';
        case 'maintenance_no':        return work.maintenance_no        ?? '—';
        case 'invoice_type':           return work.invoice_type           ?? '—';
        case 'invoice_monthly_type':   return work.invoice_monthly_type         ?? '—';
        case 'dispatch_source':       return work.dispatch_source       ?? '—';
        case 'staff_code':            return work.staff_code            ?? '—';
        case 'rank_code':              return work.rank_code               ?? '—';
        case 'first_shipment_type':    return work.first_shipment_type        ?? '—';
        case 'item_code':             return work.item_code             ?? '—';
        case 'item_name':             return work.item_name             ?? '—';
        case 'open_type':              return work.open_type              ?? '—';
        case 'standard_retail_price': return work.standard_retail_price ?? '—';
        case 'model_group':           return work.model_group           ?? '—';
        case 'filler':                return work.filler                ?? '—';
        case 'quantity':              return fmtNum(work.quantity);
        case 'model_code':             return work.model_code        ?? '—';
        case 'vehicle_code':           return work.vehicle_code      ?? '—';
        default:                      return '—';
    }
}

const numericColumns = new Set<ColumnKey>([
    'order_qty', 'ship_qty', 'unit_price', 'cost_price', 'quantity',
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="売上取込" />
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">売上取込</h1>

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
                        <p v-if="uploadForm.errors.csv_file" class="text-xs text-destructive">
                            {{ uploadForm.errors.csv_file }}
                        </p>
                    </div>

                    <!-- 検索キーワード -->
                    <div class="flex flex-col gap-1.5">
                        <Label>検索キーワード</Label>
                        <div class="relative flex items-center">
                            <Input
                                v-model="search"
                                placeholder="品番・伝票NO・品名・販売店CD"
                                class="w-60 pr-8"
                                @keydown.enter="doSearch"
                            />
                            <button
                                v-if="search"
                                type="button"
                                class="absolute right-2 text-muted-foreground hover:text-foreground"
                                @click="clearSearch"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- ボタン群: 取込 / 検索 / 照合 / 売上変換処理 -->
                    <div class="flex flex-wrap gap-2 pb-0.5">
                        <!-- 取込 -->
                        <Button @click="doUpload" :disabled="uploadForm.processing">
                            <FileUp class="mr-1 h-4 w-4" />
                            {{ uploadForm.processing ? '取込中...' : '取込' }}
                        </Button>

                        <!-- 検索 -->
                        <Button @click="doSearch" variant="secondary">
                            <Search class="mr-1 h-4 w-4" />検索
                        </Button>

                        <!-- 照合 -->
                        <Button
                            @click="doCheck"
                            :disabled="checkForm.processing || summary.total === 0"
                            variant="outline"
                            class="border-blue-400 text-blue-700 hover:bg-blue-50 disabled:opacity-50"
                        >
                            <RefreshCw class="mr-1 h-4 w-4" :class="{ 'animate-spin': checkForm.processing }" />
                            {{ checkForm.processing ? '照合中...' : '照合' }}
                        </Button>

                        <!-- 売上変換処理 -->
                        <Button
                            @click="doConvert"
                            :disabled="convertForm.processing || summary.total === 0 || summary.errors > 0 || summary.converted > 0"
                            :title="summary.converted > 0 ? '変換済みです。再度変換するには取込をやり直してください。' : ''"
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
                    <template v-if="search">
                        （検索中: <strong class="text-foreground">{{ works.total }}</strong> 件一致）
                    </template>
                </span>
                <span class="flex items-center gap-1 text-green-700">
                    <CheckCircle2 class="h-4 w-4" />
                    正常: <strong>{{ summary.ok }}</strong> 件
                </span>
                <span v-if="summary.errors > 0" class="flex items-center gap-1 text-destructive">
                    <AlertCircle class="h-4 w-4" />
                    エラー: <strong>{{ summary.errors }}</strong> 件（照合または修正後に売上変換を実行してください）
                </span>
                <span v-if="summary.converted > 0" class="flex items-center gap-1 font-medium text-blue-700">
                    <Lock class="h-4 w-4" />
                    変換済み: <strong>{{ summary.converted }}</strong> 件（売上変換処理は完了しています）
                </span>
            </div>

            <!-- テーブルヘッダー行 -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span class="font-medium">{{ fmtYm(processingYm) }} のデータ</span>
                    <!-- 件数/ページ -->
                    <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="h-8 w-24">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10件</SelectItem>
                            <SelectItem value="25">25件</SelectItem>
                            <SelectItem value="50">50件</SelectItem>
                            <SelectItem value="100">100件</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Excel出力 -->
                    <Button variant="outline" size="sm" @click="doExport" :disabled="summary.total === 0">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>
                    <!-- 一括削除 -->
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="bulkDestroyForm.processing || summary.total === 0"
                        class="border-destructive text-destructive hover:bg-destructive/10 disabled:opacity-50"
                        @click="doBulkDestroy"
                    >
                        <Trash2 class="mr-1 h-4 w-4" />
                        {{ bulkDestroyForm.processing ? '削除中...' : '一括削除' }}
                    </Button>
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
                    <!-- 行追加 -->
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
                                :class="[
                                    numericColumns.has(key) ? 'text-right' : 'text-left',
                                    sortableColumns[key] ? 'cursor-pointer select-none hover:bg-muted' : '',
                                ]"
                                @click="sortableColumns[key] && toggleSort(sortableColumns[key]!)"
                            >
                                <span class="inline-flex items-center gap-1">
                                    {{ columns[key].label }}
                                    <template v-if="sortableColumns[key]">
                                        <ArrowUp
                                            v-if="sortField === sortableColumns[key] && sortDir === 'asc'"
                                            class="h-3.5 w-3.5 text-primary"
                                        />
                                        <ArrowDown
                                            v-else-if="sortField === sortableColumns[key] && sortDir === 'desc'"
                                            class="h-3.5 w-3.5 text-primary"
                                        />
                                        <ArrowUpDown v-else class="h-3.5 w-3.5 text-muted-foreground/40" />
                                    </template>
                                </span>
                            </th>
                            <th
                                class="cursor-pointer select-none whitespace-nowrap px-3 py-2 text-left font-medium hover:bg-muted"
                                @click="toggleSort('check_flag')"
                            >
                                <span class="inline-flex items-center gap-1">
                                    チェック
                                    <ArrowUp v-if="sortField === 'check_flag' && sortDir === 'asc'" class="h-3.5 w-3.5 text-primary" />
                                    <ArrowDown v-else-if="sortField === 'check_flag' && sortDir === 'desc'" class="h-3.5 w-3.5 text-primary" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 text-muted-foreground/40" />
                                </span>
                            </th>
                            <th class="sticky right-0 z-10 bg-muted/50 border-l whitespace-nowrap px-3 py-2 text-left font-medium">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="work in works.data"
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
                                    key === 'part_number' ? 'font-mono' : '',
                                    key === 'item_name' ? 'max-w-[160px] truncate' : '',
                                ]"
                            >
                                {{ getCellValue(key, work) }}
                            </td>
                            <td class="px-3 py-2">
                                <span
                                    v-if="work.converted_at"
                                    class="flex items-center gap-1 text-xs font-medium text-blue-700"
                                    :title="`売上変換済み: ${work.converted_at}`"
                                >
                                    <Lock class="h-3.5 w-3.5" />変換済み
                                </span>
                                <span
                                    v-else-if="work.check_flag === 0"
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
                                    <span class="max-w-[200px] truncate">{{ work.check_message }}</span>
                                </span>
                            </td>
                            <td class="px-3 py-2 sticky right-0 z-10 bg-background border-l">
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
                        <tr v-if="works.data.length === 0">
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

            <!-- ページング -->
            <div v-if="works.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in works.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)"
                    >{{ paginationLabel(link.label) }}</Button>
                    <Button v-else variant="outline" size="sm" disabled>
                        {{ paginationLabel(link.label) }}
                    </Button>
                </template>
            </div>

            <!-- ページ情報 -->
            <p v-if="summary.total > 0" class="text-center text-xs text-muted-foreground">
                {{ summary.total }}件中 {{ (works.current_page - 1) * works.per_page + 1 }}〜{{ Math.min(works.current_page * works.per_page, summary.total) }}件表示
            </p>
        </div>

        <!-- 追加・編集ダイアログ -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-h-[90vh] max-w-4xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ editingId !== null ? '行を編集' : '行を追加' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitWorkForm" class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-4">

                        <!-- ── 基本情報 ── -->
                        <div class="col-span-full border-b pb-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">基本情報</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>処理年月 <span class="text-destructive">*</span></Label>
                            <Input type="month" v-model="workForm.processing_ym" />
                            <p v-if="workForm.errors.processing_ym" class="text-xs text-destructive">{{ workForm.errors.processing_ym }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>月次区分</Label>
                            <Input v-model='workForm.monthly_f_type' maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>制御コード</Label>
                            <Input v-model="workForm.control_code" maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>営業所CD</Label>
                            <Input v-model="workForm.office_code" maxlength="10" />
                        </div>

                        <!-- ── 伝票・受注情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">伝票・受注情報</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>品番 <span class="text-destructive">*</span></Label>
                            <Input v-model='workForm.part_number' maxlength="20" placeholder="13桁品番" class="font-mono" />
                            <p v-if="workForm.errors.part_number" class="text-xs text-destructive">{{ workForm.errors.part_number }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>伝票NO <span class="text-destructive">*</span></Label>
                            <Input v-model='workForm.slip_number' maxlength="20" />
                            <p v-if="workForm.errors.slip_number" class="text-xs text-destructive">{{ workForm.errors.slip_number }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>受注日</Label>
                            <Input type="date" v-model="workForm.order_date" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>受注数</Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.order_qty" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>売上日 <span class="text-destructive">*</span></Label>
                            <Input type="date" v-model="workForm.sale_date" />
                            <p v-if="workForm.errors.sale_date" class="text-xs text-destructive">{{ workForm.errors.sale_date }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>出荷数 <span class="text-destructive">*</span></Label>
                            <Input type="number" step="0.01" v-model="workForm.ship_qty" />
                            <p v-if="workForm.errors.ship_qty" class="text-xs text-destructive">{{ workForm.errors.ship_qty }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>赤黒区分</Label>
                            <Select v-model='workForm.reversal_type'>
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
                        <div class="flex flex-col gap-1.5">
                            <Label>販売単価 <span class="text-destructive">*</span></Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.unit_price" />
                            <p v-if="workForm.errors.unit_price" class="text-xs text-destructive">{{ workForm.errors.unit_price }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>売上原価</Label>
                            <Input type="number" step="0.01" min="0" v-model="workForm.cost_price" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>端末価格</Label>
                            <Input v-model="workForm.terminal_price" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>標準小売価格</Label>
                            <Input v-model="workForm.standard_retail_price" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>LES比率</Label>
                            <Input v-model='workForm.discount_rate' maxlength="10" />
                        </div>

                        <!-- ── 得意先・請求情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">得意先・請求情報</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>販売店CD(半角) <span class="text-destructive">*</span></Label>
                            <Input v-model="workForm.partner_code" maxlength="20" />
                            <p v-if="workForm.errors.partner_code" class="text-xs text-destructive">{{ workForm.errors.partner_code }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>販売店CD(全角)</Label>
                            <Input v-model="workForm.dealer_code" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>売上区分</Label>
                            <Input v-model='workForm.sale_type' maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>請求区分</Label>
                            <Input v-model='workForm.invoice_type' maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>請求月区分</Label>
                            <Input v-model='workForm.invoice_monthly_type' maxlength="5" />
                        </div>

                        <!-- ── 出庫・担当情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">出庫・担当情報</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>出庫元</Label>
                            <Input v-model="workForm.dispatch_source" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>担当CD</Label>
                            <Input v-model="workForm.staff_code" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>ランクCD</Label>
                            <Input v-model='workForm.rank_code' maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>初回区分</Label>
                            <Input v-model='workForm.first_shipment_type' maxlength="5" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>内訳CD</Label>
                            <Input v-model="workForm.breakdown_code" maxlength="10" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>公開区分</Label>
                            <Input v-model='workForm.open_type' maxlength="5" />
                        </div>

                        <!-- ── 商品情報 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">商品情報</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>品目CD</Label>
                            <Input v-model="workForm.item_code" maxlength="20" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label>モデルグループ</Label>
                            <Input v-model="workForm.model_group" maxlength="10" />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>品名</Label>
                            <Input v-model="workForm.item_name" maxlength="200" />
                        </div>

                        <!-- ── その他 ── -->
                        <div class="col-span-full border-b pb-1 pt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">その他</span>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>整備注文NO</Label>
                            <Input v-model="workForm.maintenance_no" maxlength="100" />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>フィラー</Label>
                            <Input v-model="workForm.filler" maxlength="100" />
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
