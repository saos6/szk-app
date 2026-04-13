<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeftRight,
    CheckCircle2,
    FileUp,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import * as PartsSaleImportController from '@/actions/App/Http/Controllers/PartsSaleImportController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    control_code: string | null;
    hinban: string | null;
    slip_no: string | null;
    order_qty: string;
    ship_qty: string;
    sale_date: string | null;
    unit_price: string;
    partner_code: string | null;
    cost_price: string;
    maintenance_no: string | null;
    red_black_kbn: string;
    dispatch_source: string | null;
    staff_code: string | null;
    item_code: string | null;
    item_name: string | null;
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
    processing_ym: '',
    control_code: '',
    hinban: '',
    slip_no: '',
    order_qty: '',
    ship_qty: '',
    sale_date: '',
    unit_price: '',
    partner_code: '',
    cost_price: '',
    maintenance_no: '',
    red_black_kbn: '0',
    dispatch_source: '',
    staff_code: '',
    item_code: '',
    item_name: '',
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
    workForm.processing_ym = work.processing_ym;
    workForm.control_code = work.control_code ?? '';
    workForm.hinban = work.hinban ?? '';
    workForm.slip_no = work.slip_no ?? '';
    workForm.order_qty = work.order_qty ?? '';
    workForm.ship_qty = work.ship_qty ?? '';
    workForm.sale_date = work.sale_date ?? '';
    workForm.unit_price = work.unit_price ?? '';
    workForm.partner_code = work.partner_code ?? '';
    workForm.cost_price = work.cost_price ?? '';
    workForm.maintenance_no = work.maintenance_no ?? '';
    workForm.red_black_kbn = work.red_black_kbn ?? '0';
    workForm.dispatch_source = work.dispatch_source ?? '';
    workForm.staff_code = work.staff_code ?? '';
    workForm.item_code = work.item_code ?? '';
    workForm.item_name = work.item_name ?? '';
    dialogOpen.value = true;
}

function submitWorkForm() {
    if (editingId.value !== null) {
        workForm.put(PartsSaleImportController.update.url(editingId.value), {
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else {
        workForm.post(PartsSaleImportController.store.url(), {
            onSuccess: () => {
                dialogOpen.value = false;
            },
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

function fmtNum(n: string | null): string {
    if (n === null || n === '' || n === undefined) return '—';
    return Number(n).toLocaleString('ja-JP');
}
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
                <Button size="sm" @click="openAddDialog">
                    <Plus class="mr-1 h-4 w-4" />行追加
                </Button>
            </div>

            <!-- テーブル -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                品番
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                伝票NO
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                売上日
                            </th>
                            <th
                                class="px-3 py-2 text-right font-medium whitespace-nowrap"
                            >
                                数量
                            </th>
                            <th
                                class="px-3 py-2 text-right font-medium whitespace-nowrap"
                            >
                                販売単価
                            </th>
                            <th
                                class="px-3 py-2 text-right font-medium whitespace-nowrap"
                            >
                                売上原価
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                販売店CD
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                品名
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                チェック
                            </th>
                            <th
                                class="px-3 py-2 text-left font-medium whitespace-nowrap"
                            >
                                操作
                            </th>
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
                            <td class="px-3 py-2 font-mono text-xs">
                                {{ work.hinban ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ work.slip_no ?? '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                {{ fmtDate(work.sale_date) }}
                            </td>
                            <td
                                class="px-3 py-2 text-right tabular-nums text-xs"
                            >
                                {{ fmtNum(work.quantity) }}
                            </td>
                            <td
                                class="px-3 py-2 text-right tabular-nums text-xs"
                            >
                                {{ fmtNum(work.unit_price) }}
                            </td>
                            <td
                                class="px-3 py-2 text-right tabular-nums text-xs"
                            >
                                {{ fmtNum(work.cost_price) }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ work.partner_code ?? '—' }}
                            </td>
                            <td class="max-w-[180px] truncate px-3 py-2 text-xs">
                                {{ work.item_name ?? '—' }}
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
                                colspan="10"
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
            <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{
                        editingId !== null ? '行を編集' : '行を追加'
                    }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitWorkForm" class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- 処理年月 -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >処理年月
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                type="month"
                                v-model="workForm.processing_ym"
                            />
                            <p
                                v-if="workForm.errors.processing_ym"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.processing_ym }}
                            </p>
                        </div>
                        <!-- 品番 -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >品番
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                v-model="workForm.hinban"
                                maxlength="20"
                                placeholder="13桁品番"
                            />
                            <p
                                v-if="workForm.errors.hinban"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.hinban }}
                            </p>
                        </div>
                        <!-- 伝票NO -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >伝票NO
                                <span class="text-destructive">*</span></Label
                            >
                            <Input v-model="workForm.slip_no" maxlength="20" />
                            <p
                                v-if="workForm.errors.slip_no"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.slip_no }}
                            </p>
                        </div>
                        <!-- 売上日 -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >売上日
                                <span class="text-destructive">*</span></Label
                            >
                            <Input type="date" v-model="workForm.sale_date" />
                            <p
                                v-if="workForm.errors.sale_date"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.sale_date }}
                            </p>
                        </div>
                        <!-- 出荷数 -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >出荷数
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                type="number"
                                step="0.01"
                                v-model="workForm.ship_qty"
                            />
                            <p
                                v-if="workForm.errors.ship_qty"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.ship_qty }}
                            </p>
                        </div>
                        <!-- 赤黒区分 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>赤黒区分</Label>
                            <Select v-model="workForm.red_black_kbn">
                                <SelectTrigger
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="0">黒伝（0）</SelectItem>
                                    <SelectItem value="2">赤伝（2）</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <!-- 販売単価 -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >販売単価
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="workForm.unit_price"
                            />
                            <p
                                v-if="workForm.errors.unit_price"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.unit_price }}
                            </p>
                        </div>
                        <!-- 販売店コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                >販売店コード
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                v-model="workForm.partner_code"
                                maxlength="20"
                            />
                            <p
                                v-if="workForm.errors.partner_code"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.partner_code }}
                            </p>
                        </div>
                        <!-- 売上原価 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>売上原価</Label>
                            <Input
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="workForm.cost_price"
                            />
                            <p
                                v-if="workForm.errors.cost_price"
                                class="text-xs text-destructive"
                            >
                                {{ workForm.errors.cost_price }}
                            </p>
                        </div>
                        <!-- 整備注文NO -->
                        <div class="flex flex-col gap-1.5">
                            <Label>整備注文NO</Label>
                            <Input
                                v-model="workForm.maintenance_no"
                                maxlength="100"
                            />
                        </div>
                        <!-- 出庫元 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>出庫元</Label>
                            <Input
                                v-model="workForm.dispatch_source"
                                maxlength="20"
                            />
                        </div>
                        <!-- 担当 -->
                        <div class="flex flex-col gap-1.5">
                            <Label>担当</Label>
                            <Input
                                v-model="workForm.staff_code"
                                maxlength="20"
                            />
                        </div>
                        <!-- 品目コード -->
                        <div class="flex flex-col gap-1.5">
                            <Label>品目コード</Label>
                            <Input
                                v-model="workForm.item_code"
                                maxlength="20"
                            />
                        </div>
                        <!-- 品名 -->
                        <div class="col-span-2 flex flex-col gap-1.5">
                            <Label>品名</Label>
                            <Input
                                v-model="workForm.item_name"
                                maxlength="200"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="dialogOpen = false"
                            >キャンセル</Button
                        >
                        <Button type="submit" :disabled="workForm.processing">
                            {{
                                workForm.processing
                                    ? '保存中...'
                                    : editingId !== null
                                      ? '更新'
                                      : '追加'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
