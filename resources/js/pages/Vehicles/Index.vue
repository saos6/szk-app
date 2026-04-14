<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import { ArrowUpDown, ArrowUp, ArrowDown, Download, Plus, Eye, Pencil, Trash2, Columns3, Search } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import * as VehicleController from '@/actions/App/Http/Controllers/VehicleController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent,
    DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Vehicle {
    id: number;
    model_code: string; frame_number: string;
    model_name: string | null; vehicle_no: string | null;
    owner_name: string | null; owner_kana: string | null;
    color_code: string | null; gender: string | null;
    shop_name: string | null; sale_date: string | null;
    first_reg_date: string | null;
    has_security_reg: boolean; has_theft_insurance: boolean;
    has_warranty: boolean; has_application: boolean; has_dm: boolean;
    purchase_price: string | null; selling_price: string | null;
    created_at: string; updated_at: string;
}

interface PaginationLink { url: string | null; label: string; active: boolean; }

interface Props {
    vehicles: {
        data: Vehicle[]; total: number; per_page: number; current_page: number;
        last_page: number; links: PaginationLink[]; from: number | null; to: number | null;
    };
    filters: { search: string; sort: string; direction: string; per_page: string; };
    genders: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '車両（品番）マスタ', href: VehicleController.index.url() },
];

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'model_code');
const sortDir = ref(props.filters.direction ?? 'asc');

type ColumnKey = 'id' | 'model_code' | 'frame_number' | 'model_name' | 'color_code' | 'vehicle_no'
    | 'owner_name' | 'owner_kana' | 'gender' | 'shop_name' | 'sale_date'
    | 'first_reg_date' | 'purchase_price' | 'selling_price'
    | 'has_security_reg' | 'has_theft_insurance' | 'has_warranty' | 'has_application' | 'has_dm'
    | 'created_at' | 'updated_at';

const COLUMNS_STORAGE_KEY = 'vehicles.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    id:                  { label: 'ID',           visible: false },
    model_code:            { label: '機種コード（商品）',   visible: true  },
    frame_number:            { label: 'フレームNo（品番）',   visible: true  },
    model_name:            { label: '機種名（商品名）',      visible: true  },
    color_code:              { label: '色コード',      visible: false },
    vehicle_no:          { label: '車両番号',      visible: true  },
    owner_name:          { label: '氏名',          visible: true  },
    owner_kana:          { label: '氏名カナ',      visible: false },
    gender:              { label: '性別',          visible: false },
    shop_name:           { label: '販売店名',      visible: true  },
    sale_date:           { label: '売上日',        visible: true  },
    first_reg_date:      { label: '初年度登録日',  visible: false },
    purchase_price:             { label: '仕入単価',      visible: false },
    selling_price:             { label: '売上単価',      visible: false },
    has_security_reg:    { label: 'G防犯登録',     visible: false },
    has_theft_insurance: { label: '盗難保険',      visible: false },
    has_warranty:        { label: '保証書',        visible: false },
    has_application:     { label: '申請書',        visible: false },
    has_dm:              { label: 'DM',            visible: false },
    created_at:          { label: '作成日時',      visible: false },
    updated_at:          { label: '更新日時',      visible: false },
};

const nonSortable: ColumnKey[] = ['has_security_reg', 'has_theft_insurance', 'has_warranty', 'has_application', 'has_dm'];

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
    const v = (Object.keys(val) as ColumnKey[]).reduce((acc, key) => { acc[key] = val[key].visible; return acc; }, {} as Partial<Record<ColumnKey, boolean>>);
    localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(v));
}, { deep: true });

const visibleColumns = computed(() => (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible));

function applyFilters() {
    router.get(VehicleController.index.url(),
        { search: search.value, sort: sortField.value, direction: sortDir.value, per_page: perPage.value },
        { preserveState: true, replace: true });
}
function handleSearch() { applyFilters(); }
function toggleSort(field: string) {
    if (nonSortable.includes(field as ColumnKey)) return;
    if (sortField.value === field) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; }
    else { sortField.value = field; sortDir.value = 'asc'; }
    applyFilters();
}
function handlePerPageChange(v: string) { perPage.value = v; applyFilters(); }
function exportExcel() {
    const params = new URLSearchParams({ search: search.value, sort: sortField.value, direction: sortDir.value });
    window.location.href = `${VehicleController.exportMethod.url()}?${params}`;
}
function deleteVehicle(id: number, label: string) {
    if (confirm(`「${label}」を削除してもよろしいですか？`)) router.delete(VehicleController.destroy.url(id));
}
function sortIcon(field: string): 'asc' | 'desc' | 'none' {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}
function paginationLabel(label: string) { return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»'); }
function formatPrice(val: string | null) { return val ? '¥' + Number(val).toLocaleString('ja-JP') : '—'; }
function formatDate(val: string | null) { return val ? val : '—'; }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="車両（品番）マスタ" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">車両（品番）マスタ</h1>
                <div class="flex gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"><Columns3 class="mr-1 h-4 w-4" />列表示</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem v-for="(col, key) in columns" :key="key"
                                :checked="col.visible" @update:checked="(v) => (columns[key as ColumnKey].visible = v)">
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="VehicleController.create.url()"><Plus class="mr-1 h-4 w-4" />新規登録</Link>
                    </Button>
                </div>
            </div>

            <!-- 検索 -->
            <div class="flex items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="機種コード（商品）・フレームNo（品番）・機種名（商品名）・氏名・販売店で検索..."
                        class="pl-8" @keyup.enter="handleSearch" />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button variant="ghost" size="sm" @click="() => { search = ''; handleSearch(); }">クリア</Button>
            </div>

            <!-- 件数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>全 <strong class="text-foreground">{{ vehicles.total }}</strong> 件
                    <template v-if="vehicles.from && vehicles.to">（{{ vehicles.from }}〜{{ vehicles.to }} 件表示）</template>
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
                                <th v-if="col.visible"
                                    class="px-4 py-3 text-left font-medium whitespace-nowrap select-none"
                                    :class="{ 'cursor-pointer': !nonSortable.includes(key as ColumnKey) }"
                                    @click="toggleSort(key)">
                                    <span class="flex items-center gap-1">
                                        {{ col.label }}
                                        <template v-if="!nonSortable.includes(key as ColumnKey)">
                                            <ArrowUp v-if="sortIcon(key) === 'asc'" class="h-3.5 w-3.5" />
                                            <ArrowDown v-else-if="sortIcon(key) === 'desc'" class="h-3.5 w-3.5" />
                                            <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                        </template>
                                    </span>
                                </th>
                            </template>
                            <th class="sticky right-0 z-10 bg-muted/50 border-l px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in vehicles.data" :key="v.id" class="border-t transition-colors hover:bg-muted/30">
                            <td v-if="columns.id.visible" class="px-4 py-3 text-muted-foreground">{{ v.id }}</td>
                            <td v-if="columns.model_code.visible" class="px-4 py-3 font-mono text-sm font-medium">{{ v.model_code }}</td>
                            <td v-if="columns.frame_number.visible" class="px-4 py-3 font-mono text-sm">{{ v.frame_number }}</td>
                            <td v-if="columns.model_name.visible" class="px-4 py-3 font-medium">{{ v.model_name ?? '—' }}</td>
                            <td v-if="columns.color_code.visible" class="px-4 py-3 font-mono text-muted-foreground">{{ v.color_code ?? '—' }}</td>
                            <td v-if="columns.vehicle_no.visible" class="px-4 py-3 text-muted-foreground">{{ v.vehicle_no ?? '—' }}</td>
                            <td v-if="columns.owner_name.visible" class="px-4 py-3">{{ v.owner_name ?? '—' }}</td>
                            <td v-if="columns.owner_kana.visible" class="px-4 py-3 text-muted-foreground">{{ v.owner_kana ?? '—' }}</td>
                            <td v-if="columns.gender.visible" class="px-4 py-3 text-center text-muted-foreground">
                                {{ v.gender ? (genders[v.gender] ?? v.gender) : '—' }}
                            </td>
                            <td v-if="columns.shop_name.visible" class="px-4 py-3 text-muted-foreground">{{ v.shop_name ?? '—' }}</td>
                            <td v-if="columns.sale_date.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">{{ formatDate(v.sale_date) }}</td>
                            <td v-if="columns.first_reg_date.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">{{ formatDate(v.first_reg_date) }}</td>
                            <td v-if="columns.purchase_price.visible" class="px-4 py-3 text-right tabular-nums">{{ formatPrice(v.purchase_price) }}</td>
                            <td v-if="columns.selling_price.visible" class="px-4 py-3 text-right tabular-nums">{{ formatPrice(v.selling_price) }}</td>
                            <td v-if="columns.has_security_reg.visible" class="px-4 py-3 text-center">
                                <Badge :variant="v.has_security_reg ? 'default' : 'secondary'">{{ v.has_security_reg ? '有' : '無' }}</Badge>
                            </td>
                            <td v-if="columns.has_theft_insurance.visible" class="px-4 py-3 text-center">
                                <Badge :variant="v.has_theft_insurance ? 'default' : 'secondary'">{{ v.has_theft_insurance ? '有' : '無' }}</Badge>
                            </td>
                            <td v-if="columns.has_warranty.visible" class="px-4 py-3 text-center">
                                <Badge :variant="v.has_warranty ? 'default' : 'secondary'">{{ v.has_warranty ? '有' : '無' }}</Badge>
                            </td>
                            <td v-if="columns.has_application.visible" class="px-4 py-3 text-center">
                                <Badge :variant="v.has_application ? 'default' : 'secondary'">{{ v.has_application ? '有' : '無' }}</Badge>
                            </td>
                            <td v-if="columns.has_dm.visible" class="px-4 py-3 text-center">
                                <Badge :variant="v.has_dm ? 'default' : 'secondary'">{{ v.has_dm ? '有' : '無' }}</Badge>
                            </td>
                            <td v-if="columns.created_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ v.created_at ? new Date(v.created_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td v-if="columns.updated_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ v.updated_at ? new Date(v.updated_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td class="px-4 py-3 sticky right-0 z-10 bg-background border-l">
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="VehicleController.show.url(v.id)"><Eye class="h-4 w-4" /></Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="VehicleController.edit.url(v.id)"><Pencil class="h-4 w-4" /></Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="deleteVehicle(v.id, v.model_code + ' / ' + v.frame_number)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="vehicles.data.length === 0">
                            <td :colspan="visibleColumns.length + 1" class="px-4 py-10 text-center text-muted-foreground">
                                データがありません
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページング -->
            <div v-if="vehicles.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in vehicles.links" :key="link.label">
                    <Button v-if="link.url" variant="outline" size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)">{{ paginationLabel(link.label) }}</Button>
                    <Button v-else variant="outline" size="sm" disabled>{{ paginationLabel(link.label) }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
