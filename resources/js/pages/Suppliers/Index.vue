<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpDown, ArrowUp, ArrowDown,
    Download, Plus, Eye, Pencil, Trash2, Columns3, Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
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

interface Supplier {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    phone: string | null;
    contact_person: string | null;
    payment_site: number | null;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    suppliers: {
        data: Supplier[];
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
        sort: string;
        direction: string;
        per_page: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入先マスタ', href: SupplierController.index.url() },
];

const search    = ref(props.filters.search ?? '');
const perPage   = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'code');
const sortDir   = ref(props.filters.direction ?? 'asc');

type ColumnKey =
    | 'id' | 'code' | 'name' | 'name_kana' | 'phone'
    | 'contact_person' | 'payment_site' | 'created_at' | 'updated_at';

const COLUMNS_STORAGE_KEY = 'suppliers.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    id:             { label: 'ID',          visible: false },
    code:           { label: '仕入先コード', visible: true  },
    name:           { label: '仕入先名',     visible: true  },
    name_kana:      { label: '仕入先名カナ', visible: false },
    phone:          { label: '電話番号',     visible: true  },
    contact_person: { label: '担当者名',     visible: true  },
    payment_site:   { label: '支払サイト',   visible: true  },
    created_at:     { label: '作成日時',     visible: false },
    updated_at:     { label: '更新日時',     visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (saved) {
            const parsed = JSON.parse(saved);
            const merged = { ...defaultColumns };
            for (const key of Object.keys(merged) as ColumnKey[]) {
                if (key in parsed) merged[key].visible = parsed[key];
            }
            return merged;
        }
    } catch {}
    return { ...defaultColumns };
}

const columns = ref(loadColumns());

function saveColumns() {
    const visibility: Record<string, boolean> = {};
    for (const [key, val] of Object.entries(columns.value)) {
        visibility[key] = val.visible;
    }
    localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
}

const nonSortable: ColumnKey[] = [];

function isSortable(key: ColumnKey): boolean {
    return !nonSortable.includes(key);
}

function doSearch() {
    router.get(SupplierController.index.url(), {
        search: search.value,
        sort: sortField.value,
        direction: sortDir.value,
        per_page: perPage.value,
    }, { preserveState: true, replace: true });
}

function sortBy(field: ColumnKey) {
    if (!isSortable(field)) return;
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'asc';
    }
    doSearch();
}

function goToPage(url: string | null) {
    if (!url) return;
    router.get(url, {
        search: search.value,
        sort: sortField.value,
        direction: sortDir.value,
        per_page: perPage.value,
    }, { preserveState: true });
}

watch(perPage, () => doSearch());

let searchTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(doSearch, 400);
});

const deleteTarget = ref<Supplier | null>(null);
function confirmDelete(supplier: Supplier) { deleteTarget.value = supplier; }
function cancelDelete() { deleteTarget.value = null; }
function executeDelete() {
    if (!deleteTarget.value) return;
    router.delete(SupplierController.destroy.url(deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
}

function exportExcel() {
    const params = new URLSearchParams({
        search:    search.value,
        sort:      sortField.value,
        direction: sortDir.value,
    });
    window.location.href = SupplierController.exportMethod.url() + '?' + params.toString();
}

const visibleCount = computed(() =>
    Object.values(columns.value).filter(c => c.visible).length
);

function SortIcon(key: ColumnKey) {
    if (!isSortable(key)) return null;
    if (sortField.value !== key) return ArrowUpDown;
    return sortDir.value === 'asc' ? ArrowUp : ArrowDown;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="仕入先マスタ" />
        <div class="flex flex-col gap-4 p-4">

            <!-- ツールバー -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <!-- 検索 -->
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="コード・名称・電話番号で検索…"
                            class="w-72 pl-8"
                        />
                    </div>
                    <!-- 件数 -->
                    <Select v-model="perPage">
                        <SelectTrigger class="w-24">
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

                <div class="flex flex-wrap items-center gap-2">
                    <!-- 列表示 -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Columns3 class="mr-1.5 h-4 w-4" />列 ({{ visibleCount }})
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="(col, key) in columns"
                                :key="key"
                                :checked="col.visible"
                                @update:checked="(v) => { columns[key as ColumnKey].visible = v; saveColumns(); }"
                            >
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <!-- Excel出力 -->
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1.5 h-4 w-4" />Excel出力
                    </Button>
                    <!-- 新規登録 -->
                    <Button size="sm" as-child>
                        <Link :href="SupplierController.create.url()">
                            <Plus class="mr-1.5 h-4 w-4" />新規登録
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- テーブル -->
            <div class="rounded-md border">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50">
                            <tr>
                                <th
                                    v-for="(col, key) in columns"
                                    v-show="col.visible"
                                    :key="key"
                                    class="px-4 py-3 text-left font-medium text-muted-foreground whitespace-nowrap"
                                    :class="isSortable(key as ColumnKey) ? 'cursor-pointer select-none hover:text-foreground' : ''"
                                    @click="sortBy(key as ColumnKey)"
                                >
                                    <span class="inline-flex items-center gap-1">
                                        {{ col.label }}
                                        <component
                                            :is="SortIcon(key as ColumnKey)"
                                            v-if="isSortable(key as ColumnKey)"
                                            class="h-3.5 w-3.5"
                                        />
                                    </span>
                                </th>
                                <th class="sticky right-0 z-10 bg-muted/50 border-l px-4 py-3 text-right font-medium text-muted-foreground">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="supplier in suppliers.data"
                                :key="supplier.id"
                                class="border-t transition-colors hover:bg-muted/30"
                            >
                                <td v-show="columns.id.visible" class="px-4 py-3 text-muted-foreground text-xs">{{ supplier.id }}</td>
                                <td v-show="columns.code.visible" class="px-4 py-3 font-mono font-medium">{{ supplier.code }}</td>
                                <td v-show="columns.name.visible" class="px-4 py-3 font-medium">{{ supplier.name }}</td>
                                <td v-show="columns.name_kana.visible" class="px-4 py-3 text-muted-foreground">{{ supplier.name_kana ?? '—' }}</td>
                                <td v-show="columns.phone.visible" class="px-4 py-3 text-muted-foreground">{{ supplier.phone ?? '—' }}</td>
                                <td v-show="columns.contact_person.visible" class="px-4 py-3 text-muted-foreground">{{ supplier.contact_person ?? '—' }}</td>
                                <td v-show="columns.payment_site.visible" class="px-4 py-3 text-muted-foreground">
                                    {{ supplier.payment_site !== null ? `${supplier.payment_site}日` : '—' }}
                                </td>
                                <td v-show="columns.created_at.visible" class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ new Date(supplier.created_at).toLocaleString('ja-JP') }}
                                </td>
                                <td v-show="columns.updated_at.visible" class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ new Date(supplier.updated_at).toLocaleString('ja-JP') }}
                                </td>
                                <td class="px-4 py-3 sticky right-0 z-10 bg-background border-l">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="SupplierController.show.url(supplier.id)">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="SupplierController.edit.url(supplier.id)">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-7 w-7 text-destructive hover:text-destructive"
                                            @click="confirmDelete(supplier)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="suppliers.data.length === 0">
                                <td :colspan="visibleCount + 1" class="px-4 py-10 text-center text-muted-foreground">
                                    データが見つかりませんでした
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ページネーション -->
                <div class="flex items-center justify-between border-t px-4 py-3 text-sm text-muted-foreground">
                    <span>
                        {{ suppliers.total }} 件中
                        {{ suppliers.from ?? 0 }}〜{{ suppliers.to ?? 0 }} 件を表示
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <Button
                            v-for="link in suppliers.links"
                            :key="link.label"
                            variant="outline"
                            size="sm"
                            class="min-w-8 px-2 py-1 text-xs"
                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                            :disabled="!link.url"
                            @click="goToPage(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- 削除確認ダイアログ -->
            <div
                v-if="deleteTarget"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="cancelDelete"
            >
                <div class="w-full max-w-sm rounded-xl border bg-card p-6 shadow-lg">
                    <h2 class="mb-2 text-lg font-semibold">削除の確認</h2>
                    <p class="mb-6 text-sm text-muted-foreground">
                        「{{ deleteTarget.name }}」を削除します。この操作は取り消せません。
                    </p>
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="cancelDelete">キャンセル</Button>
                        <Button variant="destructive" @click="executeDelete">削除する</Button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
