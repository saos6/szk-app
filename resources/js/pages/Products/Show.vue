<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import * as ProductController from '@/actions/App/Http/Controllers/ProductController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    category: string | null;
    spec: string | null;
    maker: string | null;
    unit: string | null;
    price: string;
    cost: string;
    tax_rate: string;
    has_stock: boolean;
    status: string;
    remarks: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    product: Product;
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '商品マスタ', href: ProductController.index.url() },
    { title: props.product.name, href: ProductController.show.url(props.product.id) },
];

function fmt(val: string | number): string {
    return '¥' + Number(val).toLocaleString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`商品 ${product.name}`" />
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-2xl font-bold">商品 参照</h1>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="ProductController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="ProductController.replicate.url(product.id)">
                            <Copy class="mr-1 h-4 w-4" />複製
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="ProductController.edit.url(product.id)">
                            <Pencil class="mr-1 h-4 w-4" />編集
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border p-6">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">商品コード</dt>
                        <dd class="mt-0.5 font-mono font-medium">{{ product.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">商品名</dt>
                        <dd class="mt-0.5 font-medium">{{ product.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">商品名カナ</dt>
                        <dd class="mt-0.5 font-medium">{{ product.name_kana ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">カテゴリ</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ product.category ? categories[product.category] ?? product.category : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">規格・仕様</dt>
                        <dd class="mt-0.5 font-medium">{{ product.spec ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">メーカー</dt>
                        <dd class="mt-0.5 font-medium">{{ product.maker ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">単位</dt>
                        <dd class="mt-0.5 font-medium">{{ product.unit ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">ステータス</dt>
                        <dd class="mt-0.5 font-medium">{{ statuses[product.status] ?? product.status }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">在庫管理</dt>
                        <dd class="mt-0.5 font-medium">{{ product.has_stock ? 'あり' : 'なし' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <h2 class="mb-3 text-sm font-semibold text-muted-foreground">価格</h2>
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-muted-foreground">売価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(product.price) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">原価</dt>
                                <dd class="mt-0.5 tabular-nums font-medium">{{ fmt(product.cost) }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">税率</dt>
                                <dd class="mt-0.5 font-medium">{{ taxRates[product.tax_rate] ?? product.tax_rate }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="mt-0.5 whitespace-pre-wrap font-medium">{{ product.remarks ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t pt-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs text-muted-foreground">
                            <div>
                                <dt>作成日時</dt>
                                <dd>{{ new Date(product.created_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                            <div>
                                <dt>更新日時</dt>
                                <dd>{{ new Date(product.updated_at).toLocaleString('ja-JP') }}</dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
