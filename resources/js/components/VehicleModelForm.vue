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
    kisyu_cd: string;
    iro_cd: string;
    kisyu_nm: string;
    kisyu_nm_r: string;
    kihon: string;
    kisyu_nm_h: string;
    sre_tan: string;
    uri_tan: string;
    terminal_price: string;
    standard_retail_price: string;
    g1: string | null;
    g2: string | null;
    g3: string | null;
    g4: string | null;
    g5: string | null;
    order_no: string;
    zei_kbn: string | null;
    errors: {
        kisyu_cd?: string;
        iro_cd?: string;
        kisyu_nm?: string;
        kisyu_nm_r?: string;
        kihon?: string;
        kisyu_nm_h?: string;
        sre_tan?: string;
        uri_tan?: string;
        terminal_price?: string;
        standard_retail_price?: string;
        g1?: string;
        g2?: string;
        g3?: string;
        g4?: string;
        g5?: string;
        order_no?: string;
        zei_kbn?: string;
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
        <!-- 機種コード・色コード -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="kisyu_cd">
                    機種コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="kisyu_cd"
                    v-model="form.kisyu_cd"
                    placeholder="例：CB400SF"
                    maxlength="8"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.kisyu_cd }"
                />
                <InputError :message="form.errors.kisyu_cd" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="iro_cd">
                    色コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="iro_cd"
                    v-model="form.iro_cd"
                    placeholder="例：R-001"
                    maxlength="6"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.iro_cd }"
                />
                <InputError :message="form.errors.iro_cd" />
            </div>
        </div>

        <!-- 機種名(漢字) -->
        <div class="flex flex-col gap-1.5">
            <Label for="kisyu_nm_h">機種名（漢字）</Label>
            <Input
                id="kisyu_nm_h"
                v-model="form.kisyu_nm_h"
                placeholder="例：ホンダ CB400スーパーフォア"
                maxlength="32"
                :class="{ 'border-destructive': form.errors.kisyu_nm_h }"
            />
            <InputError :message="form.errors.kisyu_nm_h" />
        </div>

        <!-- 営業機種記号・機種略称 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="kisyu_nm">営業機種記号</Label>
                <Input
                    id="kisyu_nm"
                    v-model="form.kisyu_nm"
                    placeholder="例：CB400SF-V"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.kisyu_nm }"
                />
                <InputError :message="form.errors.kisyu_nm" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="kisyu_nm_r">機種略称</Label>
                <Input
                    id="kisyu_nm_r"
                    v-model="form.kisyu_nm_r"
                    placeholder="例：CB400SF"
                    maxlength="18"
                    :class="{ 'border-destructive': form.errors.kisyu_nm_r }"
                />
                <InputError :message="form.errors.kisyu_nm_r" />
            </div>
        </div>

        <!-- 基本機種・オーダーNo -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="kihon">基本機種</Label>
                <Input
                    id="kihon"
                    v-model="form.kihon"
                    placeholder="例：CB400"
                    maxlength="10"
                    :class="{ 'border-destructive': form.errors.kihon }"
                />
                <InputError :message="form.errors.kihon" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="order_no">オーダーNo</Label>
                <Input
                    id="order_no"
                    v-model="form.order_no"
                    placeholder="例：ORD00001"
                    maxlength="8"
                    class="font-mono"
                    :class="{ 'border-destructive': form.errors.order_no }"
                />
                <InputError :message="form.errors.order_no" />
            </div>
        </div>

        <!-- 単価情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="sre_tan">仕入単価（税抜）</Label>
                <Input
                    id="sre_tan"
                    v-model="form.sre_tan"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="例：500000"
                    :class="{ 'border-destructive': form.errors.sre_tan }"
                />
                <InputError :message="form.errors.sre_tan" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="uri_tan">売上単価（税抜）</Label>
                <Input
                    id="uri_tan"
                    v-model="form.uri_tan"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="例：650000"
                    :class="{ 'border-destructive': form.errors.uri_tan }"
                />
                <InputError :message="form.errors.uri_tan" />
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
                <Label for="zei_kbn">税区分</Label>
                <Select
                    :model-value="form.zei_kbn ?? '__none__'"
                    @update:model-value="(v) => (form.zei_kbn = v === '__none__' ? null : v)"
                >
                    <SelectTrigger id="zei_kbn" :class="{ 'border-destructive': form.errors.zei_kbn }">
                        <SelectValue placeholder="（未選択）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（未選択）</SelectItem>
                        <SelectItem v-for="(label, value) in zeiKbn" :key="value" :value="value">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.zei_kbn" />
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
