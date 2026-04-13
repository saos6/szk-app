<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    code: string;
    partner_code: string | null;
    name: string;
    name_kana: string | null;
    postal_code: string | null;
    address: string | null;
    phone: string | null;
    fax: string | null;
    email: string | null;
    employee: { id: number; code: string; name: string } | null;
    closing_day: number | null;
    payment_cycle: string | null;
    payment_day: number | null;
    remarks: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    customer: Customer;
    paymentCycles: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '得意先マスタ', href: CustomerController.index.url() },
    { title: props.customer.name, href: CustomerController.show.url(props.customer.id) },
];

function dayLabel(day: number | null): string {
    if (day === null) return '—';
    return day === 31 ? '末日' : `${day}日`;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`得意先 ${customer.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">得意先 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="CustomerController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="CustomerController.replicate.url(customer.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="CustomerController.edit.url(customer.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">得意先コード</dt>
                        <dd class="mt-0.5 font-mono font-medium">{{ customer.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">相手先コード</dt>
                        <dd class="mt-0.5 font-mono font-medium">{{ customer.partner_code ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">得意先名</dt>
                        <dd class="mt-0.5 font-medium">{{ customer.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">得意先名カナ</dt>
                        <dd class="mt-0.5 font-medium">{{ customer.name_kana ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当社員</dt>
                        <dd class="mt-0.5 font-medium">
                            <span v-if="customer.employee">
                                <span class="font-mono text-xs text-muted-foreground">[{{ customer.employee.code }}]</span>
                                {{ customer.employee.name }}
                            </span>
                            <span v-else>—</span>
                        </dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">連絡先</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-muted-foreground">郵便番号</dt>
                                <dd class="mt-0.5 font-medium">{{ customer.postal_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">電話番号</dt>
                                <dd class="mt-0.5 font-medium">{{ customer.phone ?? '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-muted-foreground">住所</dt>
                                <dd class="mt-0.5 font-medium">{{ customer.address ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">FAX</dt>
                                <dd class="mt-0.5 font-medium">{{ customer.fax ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">メールアドレス</dt>
                                <dd class="mt-0.5 font-medium">{{ customer.email ?? '—' }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">支払条件</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">締め日</dt>
                                <dd class="mt-0.5 font-medium">{{ dayLabel(customer.closing_day) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">支払いサイクル</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ customer.payment_cycle ? paymentCycles[customer.payment_cycle] : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">支払日</dt>
                                <dd class="mt-0.5 font-medium">{{ dayLabel(customer.payment_day) }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="mt-0.5 whitespace-pre-wrap font-medium">{{ customer.remarks ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(customer.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(customer.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
