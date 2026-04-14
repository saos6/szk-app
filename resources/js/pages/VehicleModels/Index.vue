<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Download,
    Plus,
    Eye,
    Pencil,
    Trash2,
    Columns3,
    Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import * as VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
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

interface VehicleModel {
    id: number;
    model_code: string;
    color_code: string;
    model_name: string | null;
    model_abbr: string | null;
    base_model: string | null;
    model_name_kanji: string | null;
    purchase_price: string | null;
    selling_price: string | null;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_number: string | null;
    tax_type: number | null;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    vehicleModels: {
        data: VehicleModel[];
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
    zeiKbn: Record<string, string>;
    g1Types: Record<string, string>;
    g2Disp: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '機種商品', href: VehicleModelController.index.url() },
];

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'model_code');
const sortDir = ref(props.filters.direction ?? 'asc');

type ColumnKey =
    | 'id'
    | 'model_code'
    | 'color_code'
    | 'model_name_kanji'
    | 'model_name'
    | 'model_abbr'
    | 'base_model'
    | 'purchase_price'
    | 'selling_price'
    | 'g1'
    | 'g2'
    | 'g3'
    | 'g4'
    | 'g5'
    | 'order_number'
    | 'tax_type'
    | 'created_at'
    | 'updated_at';

const COLUMNS_STORAGE_KEY = 'vehicle-models.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    id:         { label: 'ID',            visible: false },
    model_code:   { label: '機種商品コード',    visible: true  },
    color_code:     { label: '色',      visible: true  },
    model_name_kanji: { label: '機種名(漢字)',  visible: true  },
    model_name:   { label: '営業機種記号',  visible: true  },
    model_abbr: { label: '機種略称',      visible: true  },
    base_model:      { label: '基本機種',      visible: false },
    purchase_price:    { label: '仕入単価(税抜)', visible: true  },
    selling_price:    { label: '売上単価(税抜)', visible: true  },
    g1:         { label: 'タイプ区分(G1)', visible: true  },
    g2:         { label: '排気量区分(G2)', visible: true  },
    g3:         { label: 'G3',            visible: false },
    g4:         { label: 'G4',            visible: false },
    g5:         { label: 'G5',            visible: false },
    order_number:   { label: 'オーダーNo',    visible: false },
    tax_type:    { label: '税区分',        visible: false },
    created_at: { label: '作成日時',      visible: false },
    updated_at: { label: '更新日時',      visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (!saved) return defaultColumns;
        const parsed = JSON.parse(saved) as Partial<Record<ColumnKey, boolean>>;
        return (Object.keys(defaultColumns) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = { ...defaultColumns[key], visible: parsed[key] ?? defaultColumns[key].visible };
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
            (acc, key) => { acc[key] = val[key].visible; return acc; },
            {} as Partial<Record<ColumnKey, boolean>>,
        );
        localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
    },
    { deep: true },
);

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible),
);

function applyFilters() {
    router.get(
        VehicleModelController.index.url(),
        { search: search.value, sort: sortField.value, direction: sortDir.value, per_page: perPage.value },
        { preserveState: true, replace: true },
    );
}

function handleSearch() { applyFilters(); }

function toggleSort(field: string) {
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
    const params = new URLSearchParams({ search: search.value, sort: sortField.value, direction: sortDir.value });
    window.location.href = `${VehicleModelController.exportMethod.url()}?${params}`;
}

function deleteVehicleModel(id: number, label: string) {
    if (confirm(`「${label}」を削除してもよろしいですか？`)) {
        router.delete(VehicleModelController.destroy.url(id));
    }
}

function sortIcon(field: string): 'asc' | 'desc' | 'none' {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

function paginationLabel(label: string): string {
    return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»');
}

function formatPrice(val: string | null): string {
    if (val === null || val === '') return '—';
    return '¥' + Number(val).toLocaleString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="機種商品" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">機種商品</h1>
                <div class="flex gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"><Columns3 class="mr-1 h-4 w-4" />列表示</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="(col, key) in columns"
                                :key="key"
                                :checked="col.visible"
                                @update:checked="(v) => (columns[key as ColumnKey].visible = v)"
                            >
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="VehicleModelController.create.url()">
                            <Plus class="mr-1 h-4 w-4" />新規登録
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 検索 -->
            <div class="flex items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="機種商品コード・色・機種名で検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button variant="ghost" size="sm" @click="() => { search = ''; handleSearch(); }">クリア</Button>
            </div>

            <!-- 件数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    全 <strong class="text-foreground">{{ vehicleModels.total }}</strong> 件
                    <template v-if="vehicleModels.from && vehicleModels.to">
                        （{{ vehicleModels.from }}〜{{ vehicleModels.to }} 件表示）
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
                                    class="cursor-pointer px-4 py-3 text-left font-medium whitespace-nowrap select-none"
                                    @click="toggleSort(key)"
                                >
                                    <span class="flex items-center gap-1">
                                        {{ col.label }}
                                        <ArrowUp v-if="sortIcon(key) === 'asc'" class="h-3.5 w-3.5" />
                                        <ArrowDown v-else-if="sortIcon(key) === 'desc'" class="h-3.5 w-3.5" />
                                        <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                    </span>
                                </th>
                            </template>
                            <th class="sticky right-0 z-10 bg-muted/50 border-l px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="vm in vehicleModels.data"
                            :key="vm.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td v-if="columns.id.visible" class="px-4 py-3 text-muted-foreground">{{ vm.id }}</td>
                            <td v-if="columns.model_code.visible" class="px-4 py-3 font-mono text-sm font-medium">{{ vm.model_code }}</td>
                            <td v-if="columns.color_code.visible" class="px-4 py-3 font-mono text-sm">{{ vm.color_code }}</td>
                            <td v-if="columns.model_name_kanji.visible" class="px-4 py-3 font-medium">{{ vm.model_name_kanji ?? '—' }}</td>
                            <td v-if="columns.model_name.visible" class="px-4 py-3 text-muted-foreground">{{ vm.model_name ?? '—' }}</td>
                            <td v-if="columns.model_abbr.visible" class="px-4 py-3 text-muted-foreground">{{ vm.model_abbr ?? '—' }}</td>
                            <td v-if="columns.base_model.visible" class="px-4 py-3 text-muted-foreground">{{ vm.base_model ?? '—' }}</td>
                            <td v-if="columns.purchase_price.visible" class="px-4 py-3 text-right tabular-nums">{{ formatPrice(vm.purchase_price) }}</td>
                            <td v-if="columns.selling_price.visible" class="px-4 py-3 text-right tabular-nums">{{ formatPrice(vm.selling_price) }}</td>
                            <td v-if="columns.g1.visible" class="px-4 py-3 text-center text-muted-foreground">{{ vm.g1 ? (g1Types[vm.g1] ?? vm.g1) : '—' }}</td>
                            <td v-if="columns.g2.visible" class="px-4 py-3 text-center text-muted-foreground">{{ vm.g2 ? (g2Disp[vm.g2] ?? vm.g2) : '—' }}</td>
                            <td v-if="columns.g3.visible" class="px-4 py-3 text-center font-mono text-muted-foreground">{{ vm.g3 ?? '—' }}</td>
                            <td v-if="columns.g4.visible" class="px-4 py-3 text-center font-mono text-muted-foreground">{{ vm.g4 ?? '—' }}</td>
                            <td v-if="columns.g5.visible" class="px-4 py-3 text-center font-mono text-muted-foreground">{{ vm.g5 ?? '—' }}</td>
                            <td v-if="columns.order_number.visible" class="px-4 py-3 font-mono text-muted-foreground">{{ vm.order_number ?? '—' }}</td>
                            <td v-if="columns.tax_type.visible" class="px-4 py-3 text-center text-muted-foreground">{{ vm.tax_type != null ? (zeiKbn[vm.tax_type] ?? vm.tax_type) : '—' }}</td>
                            <td v-if="columns.created_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ vm.created_at ? new Date(vm.created_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td v-if="columns.updated_at.visible" class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ vm.updated_at ? new Date(vm.updated_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td class="px-4 py-3 sticky right-0 z-10 bg-background border-l">
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="VehicleModelController.show.url(vm.id)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="VehicleModelController.edit.url(vm.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="deleteVehicleModel(vm.id, vm.model_code + ' / ' + vm.color_code)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="vehicleModels.data.length === 0">
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
            <div v-if="vehicleModels.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in vehicleModels.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)"
                    >{{ paginationLabel(link.label) }}</Button>
                    <Button v-else variant="outline" size="sm" disabled>{{ paginationLabel(link.label) }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
