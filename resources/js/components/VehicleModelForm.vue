<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface VehicleModelFormData {
    model_code: string;
    color_code: string;
    model_name: string;
    model_abbr: string;
    base_model: string;
    model_name_kanji: string;
    purchase_price: string;
    selling_price: string;
    terminal_price: string;
    standard_retail_price: string;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_number: string;
    tax_type: string | null;
    errors: {
        model_code?: string;
        color_code?: string;
        model_name?: string;
        model_abbr?: string;
        base_model?: string;
        model_name_kanji?: string;
        purchase_price?: string;
        selling_price?: string;
        terminal_price?: string;
        standard_retail_price?: string;
        g1?: string;
        g2?: string;
        g3?: string;
        g4?: string;
        g5?: string;
        order_number?: string;
        tax_type?: string;
    };
    processing: boolean;
}

interface Props {
    form: VehicleModelFormData;
    zeiKbn: Record<string, string>;
    g1Types: Record<string, string>;
    g2Disp: Record<string, string>;
    g3Options: Record<string, string>;
    g4Options: Record<string, string>;
    g5Options: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

defineProps<Props>();
const emit = defineEmits<{ submit: [] }>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
        <!-- 機種コード・色 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="model_code">
                    機種コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="model_code"
                    v-model="form.model_code"
                    placeholder="例：CB400SF"
                    maxlength="8"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.model_code }"
                />
                <InputError :message="form.errors.model_code" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="color_code">
                    色
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="color_code"
                    v-model="form.color_code"
                    placeholder="例：R-001"
                    maxlength="6"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.color_code }"
                />
                <InputError :message="form.errors.color_code" />
            </div>
        </div>

        <!-- 機種名(漢字) -->
        <div class="flex flex-col gap-1.5">
            <Label for="model_name_kanji">機種名（漢字）</Label>
            <Input
                id="model_name_kanji"
                v-model="form.model_name_kanji"
                placeholder="例：ホンダ CB400スーパーフォア"
                maxlength="32"
                :class="{ 'border-destructive': form.errors.model_name_kanji }"
            />
            <InputError :message="form.errors.model_name_kanji" />
        </div>

        <!-- 営業機種記号・機種略称 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="model_name">営業機種記号</Label>
                <Input
                    id="model_name"
                    v-model="form.model_name"
                    placeholder="例：CB400SF-V"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.model_name }"
                />
                <InputError :message="form.errors.model_name" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="model_abbr">機種略称</Label>
                <Input
                    id="model_abbr"
                    v-model="form.model_abbr"
                    placeholder="例：CB400SF"
                    maxlength="18"
                    :class="{ 'border-destructive': form.errors.model_abbr }"
                />
                <InputError :message="form.errors.model_abbr" />
            </div>
        </div>

        <!-- 基本機種・オーダーNo -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="base_model">基本機種</Label>
                <Input
                    id="base_model"
                    v-model="form.base_model"
                    placeholder="例：CB400"
                    maxlength="10"
                    :class="{ 'border-destructive': form.errors.base_model }"
                />
                <InputError :message="form.errors.base_model" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="order_number">オーダーNo</Label>
                <Input
                    id="order_number"
                    v-model="form.order_number"
                    placeholder="例：ORD00001"
                    maxlength="8"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.order_number }"
                />
                <InputError :message="form.errors.order_number" />
            </div>
        </div>

        <!-- 単価情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="purchase_price">仕入単価（税抜）</Label>
                <Input
                    id="purchase_price"
                    v-model="form.purchase_price"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="例：500000"
                    :class="{ 'border-destructive': form.errors.purchase_price }"
                />
                <InputError :message="form.errors.purchase_price" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="selling_price">売上単価（税抜）</Label>
                <Input
                    id="selling_price"
                    v-model="form.selling_price"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="例：650000"
                    :class="{ 'border-destructive': form.errors.selling_price }"
                />
                <InputError :message="form.errors.selling_price" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="terminal_price">末端価格</Label>
                <Input
                    id="terminal_price"
                    v-model="form.terminal_price"
                    type="number"
                    min="0"
                    step="0.01"
                    :class="{ 'border-destructive': form.errors.terminal_price }"
                />
                <InputError :message="form.errors.terminal_price" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="standard_retail_price">標準小売価格</Label>
                <Input
                    id="standard_retail_price"
                    v-model="form.standard_retail_price"
                    type="number"
                    min="0"
                    step="0.01"
                    :class="{ 'border-destructive': form.errors.standard_retail_price }"
                />
                <InputError :message="form.errors.standard_retail_price" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="tax_type">税区分</Label>
                <Select
                    :model-value="form.tax_type ?? '__none__'"
                    @update:model-value="(v) => (form.tax_type = v === '__none__' ? null : v)"
                >
                    <SelectTrigger id="tax_type" :class="{ 'border-destructive': form.errors.tax_type }">
                        <SelectValue placeholder="（未選択）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（未選択）</SelectItem>
                        <SelectItem v-for="(label, value) in zeiKbn" :key="value" :value="value">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.tax_type" />
            </div>
        </div>

        <!-- 区分コード G1〜G5 -->
        <div>
            <p class="mb-2 text-sm font-medium text-muted-foreground">区分コード</p>
            <div class="grid grid-cols-2 gap-5 sm:grid-cols-5">
                <div class="flex flex-col gap-1.5">
                    <Label>タイプ区分（G1）</Label>
                    <Select
                        :model-value="form.g1 ?? '__none__'"
                        @update:model-value="(v) => (form.g1 = v === '__none__' ? null : v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.g1 }">
                            <SelectValue placeholder="（未選択）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">（未選択）</SelectItem>
                            <SelectItem v-for="(label, value) in g1Types" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.g1" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label>排気量区分（G2）</Label>
                    <Select
                        :model-value="form.g2 ?? '__none__'"
                        @update:model-value="(v) => (form.g2 = v === '__none__' ? null : v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.g2 }">
                            <SelectValue placeholder="（未選択）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">（未選択）</SelectItem>
                            <SelectItem v-for="(label, value) in g2Disp" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.g2" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label>G3</Label>
                    <Select
                        :model-value="form.g3 ?? '__none__'"
                        @update:model-value="(v) => (form.g3 = v === '__none__' ? null : v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.g3 }">
                            <SelectValue placeholder="（未選択）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">（未選択）</SelectItem>
                            <SelectItem v-for="(label, value) in g3Options" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.g3" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label>G4</Label>
                    <Select
                        :model-value="form.g4 ?? '__none__'"
                        @update:model-value="(v) => (form.g4 = v === '__none__' ? null : v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.g4 }">
                            <SelectValue placeholder="（未選択）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">（未選択）</SelectItem>
                            <SelectItem v-for="(label, value) in g4Options" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.g4" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label>G5</Label>
                    <Select
                        :model-value="form.g5 ?? '__none__'"
                        @update:model-value="(v) => (form.g5 = v === '__none__' ? null : v)"
                    >
                        <SelectTrigger :class="{ 'border-destructive': form.errors.g5 }">
                            <SelectValue placeholder="（未選択）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">（未選択）</SelectItem>
                            <SelectItem v-for="(label, value) in g5Options" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.g5" />
                </div>
            </div>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
            <Button variant="outline" type="button" as-child>
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
