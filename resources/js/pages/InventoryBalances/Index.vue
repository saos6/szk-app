<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpDown, ArrowUp, ArrowDown,
    Download, Plus, Eye, Pencil, Trash2, Columns3, Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import * as InventoryBalanceController from '@/actions/App/Http/Controllers/InventoryBalanceController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent,
    DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface InventoryBalance {
    id: number;
    stock_ym: string;
    warehouse_code: string;
    model_code: string;
    frame_number: string;
    prev_stock: number;
    in_stock: number;
    out_stock: number;
    current_stock: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink { url: string | null; label: string; active: boolean; }

interface Props {
    inventoryBalances: {
        data: InventoryBalance[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        links: PaginationLink[];
        from: number | null;
        to: number | null;
    };
    filters: { search: string; ym_from: string; ym_to: string; sort: string; direction: string; per_page: string; };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '在庫残高マスタ', href: InventoryBalanceController.index.url() },
];

const search  = ref(props.filters.search ?? '');
const ymFrom  = ref(props.filters.ym_from ?? '');
const ymTo    = ref(props.filters.ym_to ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'stock_ym');
const sortDir   = ref(props.filters.direction ?? 'desc');

type ColumnKey = 'stock_ym' | 'warehouse_code' | 'model_code' | 'frame_number' | 'prev_stock' | 'in_stock' | 'out_stock' | 'current_stock' | 'created_at' | 'updated_at';

const nonSortable: ColumnKey[] = ['current_stock'];

const COLUMNS_STORAGE_KEY = 'inventory-balances.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    stock_ym:            { label: '年月',           visible: true  },
    warehouse_code:      { label: '倉庫コード',     visible: true  },
    model_code:  { label: '機種コード（商品）',     visible: true  },
    frame_number:            { label: 'フレームNo（品番）',      visible: true  },
    prev_stock:          { label: '前月繰越',        visible: true  },
    in_stock:            { label: '当月入庫',        visible: true  },
    out_stock:           { label: '当月出庫',        visible: true  },
    current_stock:       { label: '当月在庫数',      visible: true  },
    created_at:          { label: '作成日時',        visible: false },
    updated_at:          { label: '更新日時',        visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (!saved) return defaultColumns;
        const parsed = JSON.parse(saved) as Partial<Record<ColumnKey, boolean>>;
        return (Object.keys(defaultColumns) as ColumnKey[]).reduce((acc, key) => {
            acc[key] = { ...defaultColumns[key], visible: parsed[key] ?? defaultColumns[key].visible };
            return acc;
        }, {} as Record<ColumnKey, { label: string; visible: boolean }>);
    } catch { return defaultColumns; }
}

const columns = ref(loadColumns());

watch(columns, (val) => {
    const visibility = (Object.keys(val) as ColumnKey[]).reduce((acc, key) => {
        acc[key] = val[key].visible; return acc;
    }, {} as Partial<Record<ColumnKey, boolean>>);
    localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
}, { deep: true });

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible),
);

function applyFilters() {
    router.get(
        InventoryBalanceController.index.url(),
        { search: search.value, ym_from: ymFrom.value, ym_to: ymTo.value, sort: sortField.value, direction: sortDir.value, per_page: perPage.value },
        { preserveState: true, replace: true },
    );
}

function handleSearch() { applyFilters(); }

function toggleSort(field: string) {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'desc';
    }
    applyFilters();
}

function handlePerPageChange(value: string) { perPage.value = value; applyFilters(); }

function exportExcel() {
    const params = new URLSearchParams({ search: search.value, ym_from: ymFrom.value, ym_to: ymTo.value, sort: sortField.value, direction: sortDir.value });
    window.location.href = `${InventoryBalanceController.exportMethod.url()}?${params}`;
}

function deleteRecord(id: number, label: string) {
    if (confirm(`「${label}」を削除してもよろしいですか？`)) {
        router.delete(InventoryBalanceController.destroy.url(id));
    }
}

function sortIcon(field: string) {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

function paginationLabel(label: string): string {
    return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="在庫残高マスタ" />

        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">在庫残高マスタ</h1>
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
                                @update:checked="(v: boolean) => (columns[key as ColumnKey].visible = v)"
                            >{{ col.label }}</DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>

                    <Button size="sm" as-child>
                        <Link :href="InventoryBalanceController.create.url()">
                            <Plus class="mr-1 h-4 w-4" />新規登録
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 検索バー -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1">
                    <span class="text-sm text-muted-foreground whitespace-nowrap">年月：</span>
                    <Input v-model="ymFrom" type="month" class="h-8 w-36" @change="handleSearch" />
                    <span class="text-muted-foreground">〜</span>
                    <Input v-model="ymTo" type="month" class="h-8 w-36" @change="handleSearch" />
                </div>
                <div class="relative max-w-sm flex-1 min-w-48">
                    <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="倉庫・機種（商品）・フレームNo（品番）で検索..." class="pl-8" @keyup.enter="handleSearch" />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button variant="ghost" size="sm" @click="() => { search = ''; ymFrom = ''; ymTo = ''; handleSearch(); }">クリア</Button>
            </div>

            <!-- 件数・表示数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    全 <strong class="text-foreground">{{ inventoryBalances.total }}</strong> 件
                    <template v-if="inventoryBalances.from && inventoryBalances.to">
                        （{{ inventoryBalances.from }}〜{{ inventoryBalances.to }} 件表示）
                    </template>
                </span>
                <div class="flex items-center gap-2">
                    <span>表示件数：</span>
                    <Select :model-value="perPage" @update:model-value="(v) => handlePerPageChange(String(v))">
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
                            <th v-for="col in (['stock_ym', 'warehouse_code', 'model_code', 'frame_number', 'prev_stock', 'in_stock', 'out_stock', 'current_stock', 'created_at', 'updated_at'] as ColumnKey[])"
                                :key="col"
                                v-show="columns[col].visible"
                                :class="['px-4 py-3 text-left font-medium whitespace-nowrap select-none', nonSortable.includes(col) ? '' : 'cursor-pointer']"
                                @click="nonSortable.includes(col) ? undefined : toggleSort(col)"
                            >
                                <span class="flex items-center gap-1">
                                    {{ columns[col].label }}
                                    <ArrowUp v-if="sortIcon(col) === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon(col) === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else-if="!nonSortable.includes(col)" class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th class="sticky right-0 z-10 bg-muted/50 border-l px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in inventoryBalances.data"
                            :key="row.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td v-show="columns.stock_ym.visible" class="px-4 py-3 font-medium">{{ row.stock_ym }}</td>
                            <td v-show="columns.warehouse_code.visible" class="px-4 py-3">{{ row.warehouse_code }}</td>
                            <td v-show="columns.model_code.visible" class="px-4 py-3">{{ row.model_code }}</td>
                            <td v-show="columns.frame_number.visible" class="px-4 py-3">{{ row.frame_number }}</td>
                            <td v-show="columns.prev_stock.visible" class="px-4 py-3 text-right">{{ row.prev_stock.toLocaleString() }}</td>
                            <td v-show="columns.in_stock.visible" class="px-4 py-3 text-right">{{ row.in_stock.toLocaleString() }}</td>
                            <td v-show="columns.out_stock.visible" class="px-4 py-3 text-right">{{ row.out_stock.toLocaleString() }}</td>
                            <td v-show="columns.current_stock.visible" class="px-4 py-3 text-right font-semibold">{{ row.current_stock.toLocaleString() }}</td>
                            <td v-show="columns.created_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ row.created_at ? new Date(row.created_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td v-show="columns.updated_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ row.updated_at ? new Date(row.updated_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td class="px-4 py-3 sticky right-0 z-10 bg-background border-l">
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="InventoryBalanceController.show.url(row.id)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="InventoryBalanceController.edit.url(row.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="deleteRecord(row.id, `${row.stock_ym} ${row.warehouse_code} ${row.model_code}`)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="inventoryBalances.data.length === 0">
                            <td :colspan="visibleColumns.length + 1" class="px-4 py-10 text-center text-muted-foreground">
                                データがありません
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページング -->
            <div v-if="inventoryBalances.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in inventoryBalances.links" :key="link.label">
                    <Button v-if="link.url" variant="outline" size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)">
                        {{ paginationLabel(link.label) }}
                    </Button>
                    <Button v-else variant="outline" size="sm" disabled>{{ paginationLabel(link.label) }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
