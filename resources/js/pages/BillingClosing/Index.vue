<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileDown, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import * as BillingClosingController from '@/actions/App/Http/Controllers/BillingClosingController';

interface ResultRow {
    customer_code: string;
    customer_name: string;
    prev_amount: number;
    sales_amount: number;
    tax_amount: number;
    total_amount: number;
    payment_amount: number;
    balance_amount: number;
    sales_count: number;
    payments_count: number;
    closing_start_date: string | null;
    error: string | null;
}

interface CancelRow {
    billing_number: string;
    customer_code: string;
    customer_name: string;
    billing_date: string;
    balance_amount: number;
    cancelable: boolean;
    newer_number: string | null;
}

const props = defineProps<{
    defaultClosingDate: string;
    results?: ResultRow[];
    cancelPreview?: CancelRow[];
    mode?: 'aggregate' | 'confirm' | 'cancel';
    closingDate?: string;
    fromCode?: string;
    toCode?: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: '請求締め処理', href: BillingClosingController.index.url() },
        ],
    },
});

const form = useForm({
    closing_date: props.closingDate ?? props.defaultClosingDate,
    from_code: props.fromCode ?? '',
    to_code: props.toCode ?? '',
    mode: (props.mode === 'confirm' || props.mode === 'cancel' ? props.mode : 'aggregate') as 'aggregate' | 'confirm' | 'cancel',
});

const confirming = ref(false);
const cancelling = ref(false);

function execute() {
    form.post(BillingClosingController.execute.url());
}

function confirmExecute() {
    if (!confirm(`締め日 ${form.closing_date} の確定処理を実行します。よろしいですか？`)) return;
    confirming.value = true;
    const f = useForm({ ...form.data(), mode: 'confirm' });
    f.post(BillingClosingController.execute.url(), {
        onFinish: () => { confirming.value = false; },
    });
}

function cancelExecute() {
    if (!confirm(`締め日 ${form.closing_date} の取消処理を実行します。よろしいですか？`)) return;
    cancelling.value = true;
    const f = useForm({ ...form.data(), mode: 'cancel' });
    f.post(BillingClosingController.execute.url(), {
        onFinish: () => { cancelling.value = false; },
    });
}

function downloadPdf() {
    const params = new URLSearchParams({
        closing_date: form.closing_date,
        from_code: form.from_code,
        to_code: form.to_code,
        mode: 'aggregate',
    });
    window.open(BillingClosingController.pdf.url() + '?' + params.toString(), '_blank');
}

function fmtAmount(val: number): string {
    return '¥' + Math.round(val).toLocaleString('ja-JP');
}

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}

const hasResults = computed(() => props.results && props.results.length > 0);
const hasCancelPreview = computed(() => props.cancelPreview && props.cancelPreview.length > 0);
const cancelableCount = computed(() => props.cancelPreview?.filter(r => r.cancelable).length ?? 0);
const errorRows = computed(() => props.results?.filter(r => r.error) ?? []);
const successRows = computed(() => props.results?.filter(r => !r.error) ?? []);
</script>

<template>
    <Head title="請求締め処理" />

    <div class="flex flex-col gap-6 p-4">

        <!-- 条件フォーム -->
        <div class="rounded-xl border bg-card p-6">
            <h1 class="mb-5 text-lg font-semibold">請求締め処理</h1>
            <form @submit.prevent="execute" class="flex flex-wrap items-end gap-4">
                <!-- 締め日 -->
                <div class="flex flex-col gap-1.5">
                    <Label>締め日</Label>
                    <Input v-model="form.closing_date" type="date" class="w-44" />
                </div>

                <!-- 得意先コード範囲 -->
                <div class="flex flex-col gap-1.5">
                    <Label>得意先コード</Label>
                    <div class="flex items-center gap-2">
                        <Input v-model="form.from_code" type="text" placeholder="（例: C001）" class="w-32" />
                        <span class="text-muted-foreground">〜</span>
                        <Input v-model="form.to_code" type="text" placeholder="（例: C999）" class="w-32" />
                    </div>
                </div>

                <!-- 処理区分 -->
                <div class="flex flex-col gap-1.5">
                    <Label>処理区分</Label>
                    <div class="flex items-center gap-4 py-2">
                        <label class="flex cursor-pointer items-center gap-1.5 text-sm">
                            <input v-model="form.mode" type="radio" value="aggregate" class="accent-primary" />
                            集計
                        </label>
                        <label class="flex cursor-pointer items-center gap-1.5 text-sm">
                            <input v-model="form.mode" type="radio" value="confirm" class="accent-primary" />
                            確定
                        </label>
                        <label class="flex cursor-pointer items-center gap-1.5 text-sm">
                            <input v-model="form.mode" type="radio" value="cancel" class="accent-primary" />
                            取消
                        </label>
                    </div>
                </div>

                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    実行
                </Button>
            </form>
        </div>

        <!-- 集計結果 -->
        <template v-if="hasResults && mode === 'aggregate'">
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <div>
                        <h2 class="font-semibold">集計結果</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            締め日: {{ fmtDate(closingDate!) }} ／ {{ successRows.length }} 件
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="downloadPdf">
                            <FileDown class="mr-1.5 h-4 w-4" />請求書PDF（一括）
                        </Button>
                        <Button size="sm" @click="confirmExecute" :disabled="confirming || successRows.length === 0">
                            <Loader2 v-if="confirming" class="mr-1.5 h-4 w-4 animate-spin" />
                            この内容で確定する
                        </Button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">CD</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">集計期間</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">前回繰越</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">売上(税抜)</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">消費税</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">請求合計</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">入金</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">残高</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in results"
                                :key="row.customer_code"
                                class="border-t transition-colors hover:bg-muted/30"
                                :class="row.error ? 'bg-destructive/5' : ''"
                            >
                                <td class="px-4 py-2.5 font-mono text-xs text-muted-foreground">{{ row.customer_code }}</td>
                                <td class="px-4 py-2.5">{{ row.customer_name }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                                    {{ fmtDate(row.closing_start_date) }} 〜 {{ fmtDate(closingDate!) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ fmtAmount(row.prev_amount) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ fmtAmount(row.sales_amount) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ fmtAmount(row.tax_amount) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium">{{ fmtAmount(row.total_amount) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-blue-600">△{{ fmtAmount(row.payment_amount) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-bold"
                                    :class="row.balance_amount < 0 ? 'text-destructive' : ''">
                                    {{ fmtAmount(row.balance_amount) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/20">
                            <tr>
                                <td colspan="3" class="px-4 py-2.5 text-sm font-medium text-muted-foreground">合計</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium">
                                    {{ fmtAmount(results!.reduce((s, r) => s + r.prev_amount, 0)) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium">
                                    {{ fmtAmount(results!.reduce((s, r) => s + r.sales_amount, 0)) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium">
                                    {{ fmtAmount(results!.reduce((s, r) => s + r.tax_amount, 0)) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-bold">
                                    {{ fmtAmount(results!.reduce((s, r) => s + r.total_amount, 0)) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium text-blue-600">
                                    △{{ fmtAmount(results!.reduce((s, r) => s + r.payment_amount, 0)) }}
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-bold">
                                    {{ fmtAmount(results!.reduce((s, r) => s + r.balance_amount, 0)) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </template>

        <!-- 取消プレビュー -->
        <template v-if="hasCancelPreview && mode === 'cancel'">
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <div>
                        <h2 class="font-semibold">取消対象</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            締め日: {{ fmtDate(closingDate!) }} ／ 取消可能 {{ cancelableCount }} 件
                        </p>
                    </div>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="cancelExecute"
                        :disabled="cancelling || cancelableCount === 0"
                    >
                        <Loader2 v-if="cancelling" class="mr-1.5 h-4 w-4 animate-spin" />
                        取消を実行する
                    </Button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">請求番号</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">CD</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                                <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">締め日</th>
                                <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">残高</th>
                                <th class="px-4 py-2.5 text-center font-medium text-muted-foreground">状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in cancelPreview"
                                :key="row.billing_number"
                                class="border-t transition-colors"
                                :class="row.cancelable ? 'hover:bg-muted/30' : 'opacity-50'"
                            >
                                <td class="px-4 py-2.5 font-mono text-xs">{{ row.billing_number }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs text-muted-foreground">{{ row.customer_code }}</td>
                                <td class="px-4 py-2.5">{{ row.customer_name }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ fmtDate(row.billing_date) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium">{{ fmtAmount(row.balance_amount) }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <Badge v-if="row.cancelable" variant="destructive" class="text-xs">取消可</Badge>
                                    <span v-else class="text-xs text-muted-foreground" :title="`後続: ${row.newer_number}`">
                                        取消不可
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- 空メッセージ -->
        <template v-if="(mode === 'aggregate' && results && results.length === 0) ||
                        (mode === 'cancel' && cancelPreview && cancelPreview.length === 0)">
            <div class="rounded-xl border bg-card px-5 py-12 text-center text-muted-foreground">
                対象データが見つかりませんでした
            </div>
        </template>

    </div>
</template>
