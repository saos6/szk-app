<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Supplier {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    postal_code: string | null;
    address: string | null;
    phone: string | null;
    fax: string | null;
    email: string | null;
    contact_person: string | null;
    payment_site: number | null;
    remarks: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ supplier: Supplier }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '仕入先マスタ', href: SupplierController.index.url() },
    { title: props.supplier.name, href: SupplierController.show.url(props.supplier.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`仕入先 ${supplier.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">仕入先 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="SupplierController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="SupplierController.replicate.url(supplier.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="SupplierController.edit.url(supplier.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">仕入先コード</dt>
                        <dd class="mt-0.5 font-mono font-medium">{{ supplier.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">仕入先名</dt>
                        <dd class="mt-0.5 font-medium">{{ supplier.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">仕入先名カナ</dt>
                        <dd class="mt-0.5 font-medium">{{ supplier.name_kana ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当者名</dt>
                        <dd class="mt-0.5 font-medium">{{ supplier.contact_person ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">連絡先</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-muted-foreground">郵便番号</dt>
                                <dd class="mt-0.5 font-medium">{{ supplier.postal_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">電話番号</dt>
                                <dd class="mt-0.5 font-medium">{{ supplier.phone ?? '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-muted-foreground">住所</dt>
                                <dd class="mt-0.5 font-medium">{{ supplier.address ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">FAX</dt>
                                <dd class="mt-0.5 font-medium">{{ supplier.fax ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">メールアドレス</dt>
                                <dd class="mt-0.5 font-medium">{{ supplier.email ?? '—' }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">取引条件</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">支払サイト</dt>
                                <dd class="mt-0.5 font-medium">
                                    {{ supplier.payment_site !== null ? `${supplier.payment_site}日` : '—' }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="mt-0.5 whitespace-pre-wrap font-medium">{{ supplier.remarks ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(supplier.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(supplier.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
