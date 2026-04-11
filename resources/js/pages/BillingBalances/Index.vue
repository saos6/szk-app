<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Download,
    Eye,
    Plus,
    Pencil,
    Trash2,
    Columns3,
    Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import * as BillingBalanceController from '@/actions/App/Http/Controllers/BillingBalanceController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface BillingBalance {
    id: number;
    billing_date: string;
    customer: { id: number; code: string; name: string } | null;
    prev_amount: string;
    sales_amount: string;
    tax_amount: string;
    total_amount: string;
    payment_amount: string;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    billingBalances: {
        data: BillingBalance[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        links: PaginationLink[];
        from: number | null;
        to: number | null;
    };
    filters: {
        search: string;
        date_from: string;
        date_to: string;
        sort: string;
        direction: string;
        per_page: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '請求残高マスタ', href: BillingBalanceController.index.url() },
];

const search    = ref(props.filters.search    ?? '');
const dateFrom  = ref(props.filters.date_from ?? '');
const dateTo    = ref(props.filters.date_to   ?? '');
const perPage   = ref(props.filters.per_page  ?? '10');
const sortField = ref(props.filters.sort      ?? 'billing_date');
const sortDir   = ref(props.filters.direction ?? 'desc');

type ColumnKey =
    | 'billing_date'
    | 'customer'
    | 'prev_amount'
    | 'sales_amount'
    | 'tax_amount'
    | 'total_amount'
    | 'payment_amount'
    | 'created_at'
    | 'updated_at';

const COLUMNS_STORAGE_KEY = 'billing-balances.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    billing_date:   { label: '請求日',       visible: true  },
    customer:       { label: '得意先',       visible: true  },
    prev_amount:    { label: '前月繰越額',   visible: true  },
    sales_amount:   { label: '売上金額',     visible: true  },
    tax_amount:     { label: '消費税',       visible: true  },
    total_amount:   { label: '税込金額',     visible: true  },
    payment_amount: { label: '入金額',       visible: true  },
    created_at:     { label: '作成日時',     visible: false },
    updated_at:     { label: '更新日時',     visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (!saved) return defaultColumns;
        const parsed = JSON.parse(saved) as Partial<Record<ColumnKey, boolean>>;
        return (Object.keys(defaultColumns) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = {
                    ...defaultColumns[key],
                    visible: parsed[key] ?? defaultColumns[key].visible,
                };
                return acc;
            },
            {} as Record<ColumnKey, { label: string; visible: boolean }>,
        );
    } catch {
        return defaultColumns;
    }
}

const columns = ref(loadColumns());

watch(
    columns,
    (val) => {
        const visibility = (Object.keys(val) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = val[key].visible;
                return acc;
            },
            {} as Partial<Record<ColumnKey, boolean>>,
        );
        localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
    },
    { deep: true },
);

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter(
        (k) => columns.value[k].visible,
    ),
);

const nonSortable: ColumnKey[] = ['customer'];

function applyFilters() {
    router.get(
        BillingBalanceController.index.url(),
        {
            search:     search.value,
            date_from:  dateFrom.value,
            date_to:    dateTo.value,
            sort:       sortField.value,
            direction:  sortDir.value,
            per_page:   perPage.value,
        },
        { preserveState: true, replace: true },
    );
}

function handleSearch() {
    applyFilters();
}

function toggleSort(field: string) {
    if (nonSortable.includes(field as ColumnKey)) return;
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'asc';
    }
    applyFilters();
}

function handlePerPageChange(value: string) {
    perPage.value = value;
    applyFilters();
}

function exportExcel() {
    const params = new URLSearchParams({
        search:    search.value,
        date_from: dateFrom.value,
        date_to:   dateTo.value,
        sort:      sortField.value,
        direction: sortDir.value,
    });
    window.location.href = `${BillingBalanceController.exportMethod.url()}?${params}`;
}

function deleteBillingBalance(id: number, label: string) {
    if (confirm(`「${label}」を削除してもよろしいですか？`)) {
        router.delete(BillingBalanceController.destroy.url(id));
    }
}

function sortIcon(field: string): 'asc' | 'desc' | 'none' {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

function paginationLabel(label: string): string {
    return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»');
}

function fmt(val: string | null): string {
    if (!val) return '¥0';
    return '¥' + Number(val).toLocaleString('ja-JP');
}

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="請求残高マスタ" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">請求残高マスタ</h1>
                <div class="flex gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Columns3 class="mr-1 h-4 w-4" />列表示
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-44">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="(col, key) in columns"
                                :key="key"
                                :checked="col.visible"
                                @update:checked="
                                    (v) => (columns[key as ColumnKey].visible = v)
                                "
                            >
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="BillingBalanceController.create.url()">
                            <Plus class="mr-1 h-4 w-4" />新規登録
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 検索 -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1">
                    <span class="text-sm text-muted-foreground whitespace-nowrap">請求日：</span>
                    <Input v-model="dateFrom" type="date" class="h-8 w-36" @change="handleSearch" />
                    <span class="text-muted-foreground">〜</span>
                    <Input v-model="dateTo" type="date" class="h-8 w-36" @change="handleSearch" />
                </div>
                <div class="relative max-w-sm flex-1 min-w-48">
                    <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="得意先コード・得意先名で検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button variant="ghost" size="sm" @click="() => { search = ''; dateFrom = ''; dateTo = ''; handleSearch(); }">クリア</Button>
            </div>

            <!-- 件数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    全 <strong class="text-foreground">{{ billingBalances.total }}</strong> 件
                    <template v-if="billingBalances.from && billingBalances.to">
                        （{{ billingBalances.from }}〜{{ billingBalances.to }} 件表示）
                    </template>
                </span>
                <div class="flex items-center gap-2">
                    <span>表示件数：</span>
                    <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="h-8 w-24"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10件</SelectItem>
                            <SelectItem value="25">25件</SelectItem>
                            <SelectItem value="50">50件</SelectItem>
                            <SelectItem value="100">100件</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- テーブル -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <template v-for="(col, key) in columns" :key="key">
                                <th
                                    v-if="col.visible"
                                    class="px-4 py-3 text-left font-medium whitespace-nowrap select-none"
                                    :class="{
                                        'cursor-pointer': !nonSortable.includes(key as ColumnKey),
                                    }"
                                    @click="toggleSort(key)"
                                >
                                    <span class="flex items-center gap-1">
                                        {{ col.label }}
                                        <template v-if="!nonSortable.includes(key as ColumnKey)">
                                            <ArrowUp
                                                v-if="sortIcon(key) === 'asc'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="sortIcon(key) === 'desc'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-40"
                                            />
                                        </template>
                                    </span>
                                </th>
                            </template>
                            <th class="px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="billing in billingBalances.data"
                            :key="billing.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td
                                v-if="columns.billing_date.visible"
                                class="px-4 py-3 whitespace-nowrap"
                            >
                                {{ fmtDate(billing.billing_date) }}
                            </td>
                            <td v-if="columns.customer.visible" class="px-4 py-3">
                                <span v-if="billing.customer">
                                    <span class="font-mono text-xs text-muted-foreground"
                                        >[{{ billing.customer.code }}]</span
                                    >
                                    {{ billing.customer.name }}
                                </span>
                                <span v-else class="text-muted-foreground">—</span>
                            </td>
                            <td
                                v-if="columns.prev_amount.visible"
                                class="px-4 py-3 text-right tabular-nums text-muted-foreground"
                            >
                                {{ fmt(billing.prev_amount) }}
                            </td>
                            <td
                                v-if="columns.sales_amount.visible"
                                class="px-4 py-3 text-right tabular-nums"
                            >
                                {{ fmt(billing.sales_amount) }}
                            </td>
                            <td
                                v-if="columns.tax_amount.visible"
                                class="px-4 py-3 text-right tabular-nums text-muted-foreground"
                            >
                                {{ fmt(billing.tax_amount) }}
                            </td>
                            <td
                                v-if="columns.total_amount.visible"
                                class="px-4 py-3 text-right tabular-nums font-medium"
                            >
                                {{ fmt(billing.total_amount) }}
                            </td>
                            <td
                                v-if="columns.payment_amount.visible"
                                class="px-4 py-3 text-right tabular-nums"
                            >
                                {{ fmt(billing.payment_amount) }}
                            </td>
                            <td
                                v-if="columns.created_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    billing.created_at
                                        ? new Date(billing.created_at).toLocaleString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.updated_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    billing.updated_at
                                        ? new Date(billing.updated_at).toLocaleString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <!-- 参照 -->
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        title="参照"
                                        as-child
                                    >
                                        <Link :href="BillingBalanceController.show.url(billing.id)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <!-- 編集 -->
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        title="編集"
                                        as-child
                                    >
                                        <Link :href="BillingBalanceController.edit.url(billing.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <!-- 削除 -->
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive"
                                        title="削除"
                                        @click="
                                            deleteBillingBalance(
                                                billing.id,
                                                `${fmtDate(billing.billing_date)} ${billing.customer?.name ?? ''}`,
                                            )
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="billingBalances.data.length === 0">
                            <td
                                :colspan="visibleColumns.length + 1"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                データがありません
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページング -->
            <div
                v-if="billingBalances.last_page > 1"
                class="flex items-center justify-center gap-1"
            >
                <template v-for="link in billingBalances.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        :class="{
                            'bg-primary text-primary-foreground hover:bg-primary/90': link.active,
                        }"
                        @click="router.visit(link.url)"
                    >{{ paginationLabel(link.label) }}</Button>
                    <Button v-else variant="outline" size="sm" disabled>{{
                        paginationLabel(link.label)
                    }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
