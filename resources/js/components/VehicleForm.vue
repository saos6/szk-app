<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface VehicleModelItem {
    kisyu_cd: string;
    iro_cd: string;
    kisyu_nm_h: string | null;
}

interface VehicleFormData {
    kisyu_cd: string;
    frame_no: string;
    name1: string;
    name2: string;
    kisyu_nm: string;
    keishiki: string;
    kisyu_no: string;
    iro_cd: string | null;
    sre_tan: string;
    uri_tan: string;
    maker_code: string;
    unit: string;
    note1: string;
    note2: string;
    note3: string;
    first_reg_date: string;
    second_reg_date: string;
    vehicle_no: string;
    owner_name: string;
    owner_kana: string;
    birth_date: string;
    zip_code: string;
    gender: string | null;
    address1: string;
    address2: string;
    tel: string;
    mobile: string;
    has_security_reg: boolean;
    security_reg_date: string;
    has_theft_insurance: boolean;
    theft_insurance_date: string;
    has_warranty: boolean;
    has_application: boolean;
    has_dm: boolean;
    remarks: string;
    shop_name: string;
    sale_date: string;
    errors: Record<string, string | undefined>;
    processing: boolean;
}

interface Props {
    form: VehicleFormData;
    vehicleModelList: VehicleModelItem[];
    genders: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ submit: [] }>();

// 機種コード一覧（重複排除）
const kisyuCdOptions = computed(() => {
    const seen = new Set<string>();
    return props.vehicleModelList.filter((vm) => {
        if (seen.has(vm.kisyu_cd)) return false;
        seen.add(vm.kisyu_cd);
        return true;
    });
});

// 色コード一覧（選択中の機種コードで絞り込み）
const iroCdOptions = computed(() =>
    props.vehicleModelList.filter((vm) => vm.kisyu_cd === props.form.kisyu_cd),
);

// 機種コード変更時に色コードをリセット
watch(
    () => props.form.kisyu_cd,
    () => {
        props.form.iro_cd = null;
    },
);
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-6">

        <!-- ■ 基本情報 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">基本情報</h2>
            <div class="flex flex-col gap-4">
                <!-- 機種コード・フレームNo -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label>機種コード（商品） <span class="ml-1 text-xs text-destructive">*必須</span></Label>
                        <Select
                            :model-value="form.kisyu_cd || '__none__'"
                            @update:model-value="(v) => (form.kisyu_cd = v === '__none__' ? '' : v)"
                        >
                            <SelectTrigger :class="{ 'border-destructive': form.errors.kisyu_cd }">
                                <SelectValue placeholder="機種コードを選択" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">（未選択）</SelectItem>
                                <SelectItem v-for="vm in kisyuCdOptions" :key="vm.kisyu_cd" :value="vm.kisyu_cd">
                                    {{ vm.kisyu_cd }}{{ vm.kisyu_nm_h ? ` — ${vm.kisyu_nm_h}` : '' }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.kisyu_cd" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="frame_no">フレームNo（品番） <span class="ml-1 text-xs text-destructive">*必須</span></Label>
                        <Input id="frame_no" v-model="form.frame_no" maxlength="10" class="font-mono"
                            :class="{ 'border-destructive': form.errors.frame_no }" />
                        <InputError :message="form.errors.frame_no" />
                    </div>
                </div>
                <!-- 色コード -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <Label>色コード</Label>
                        <Select
                            :model-value="form.iro_cd ?? '__none__'"
                            @update:model-value="(v) => (form.iro_cd = v === '__none__' ? null : v)"
                        >
                            <SelectTrigger :class="{ 'border-destructive': form.errors.iro_cd }">
                                <SelectValue placeholder="（未選択）" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">（未選択）</SelectItem>
                                <SelectItem v-for="vm in iroCdOptions" :key="vm.iro_cd" :value="vm.iro_cd">
                                    {{ vm.iro_cd }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.iro_cd" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="kisyu_no">機種番号</Label>
                        <Input id="kisyu_no" v-model="form.kisyu_no" maxlength="20" class="font-mono"
                            :class="{ 'border-destructive': form.errors.kisyu_no }" />
                        <InputError :message="form.errors.kisyu_no" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="keishiki">形式</Label>
                        <Input id="keishiki" v-model="form.keishiki" maxlength="100"
                            :class="{ 'border-destructive': form.errors.keishiki }" />
                        <InputError :message="form.errors.keishiki" />
                    </div>
                </div>
                <!-- 機種名 -->
                <div class="flex flex-col gap-1.5">
                    <Label for="kisyu_nm">機種名（商品名）</Label>
                    <Input id="kisyu_nm" v-model="form.kisyu_nm" maxlength="1000"
                        :class="{ 'border-destructive': form.errors.kisyu_nm }" />
                    <InputError :message="form.errors.kisyu_nm" />
                </div>
                <!-- 商品名1・2 -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label for="name1">商品名1</Label>
                        <Input id="name1" v-model="form.name1" maxlength="1000"
                            :class="{ 'border-destructive': form.errors.name1 }" />
                        <InputError :message="form.errors.name1" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="name2">商品名2</Label>
                        <Input id="name2" v-model="form.name2" maxlength="1000"
                            :class="{ 'border-destructive': form.errors.name2 }" />
                        <InputError :message="form.errors.name2" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ■ 価格・商品情報 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">価格・商品情報</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="flex flex-col gap-1.5">
                    <Label for="sre_tan">仕入単価（税抜）</Label>
                    <Input id="sre_tan" v-model="form.sre_tan" type="number" min="0" step="0.01"
                        :class="{ 'border-destructive': form.errors.sre_tan }" />
                    <InputError :message="form.errors.sre_tan" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="uri_tan">売上単価（税抜）</Label>
                    <Input id="uri_tan" v-model="form.uri_tan" type="number" min="0" step="0.01"
                        :class="{ 'border-destructive': form.errors.uri_tan }" />
                    <InputError :message="form.errors.uri_tan" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="maker_code">メーカー品番</Label>
                    <Input id="maker_code" v-model="form.maker_code" maxlength="32" class="font-mono"
                        :class="{ 'border-destructive': form.errors.maker_code }" />
                    <InputError :message="form.errors.maker_code" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="unit">単位</Label>
                    <Input id="unit" v-model="form.unit" maxlength="10"
                        :class="{ 'border-destructive': form.errors.unit }" />
                    <InputError :message="form.errors.unit" />
                </div>
            </div>
        </section>

        <!-- ■ 販売・登録情報 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">販売・登録情報</h2>
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label for="shop_name">販売店名</Label>
                        <Input id="shop_name" v-model="form.shop_name" maxlength="1000"
                            :class="{ 'border-destructive': form.errors.shop_name }" />
                        <InputError :message="form.errors.shop_name" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="sale_date">売上日</Label>
                        <Input id="sale_date" v-model="form.sale_date" type="date"
                            :class="{ 'border-destructive': form.errors.sale_date }" />
                        <InputError :message="form.errors.sale_date" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <Label for="first_reg_date">初年度登録日</Label>
                        <Input id="first_reg_date" v-model="form.first_reg_date" type="date"
                            :class="{ 'border-destructive': form.errors.first_reg_date }" />
                        <InputError :message="form.errors.first_reg_date" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="second_reg_date">2回目登録日</Label>
                        <Input id="second_reg_date" v-model="form.second_reg_date" type="date"
                            :class="{ 'border-destructive': form.errors.second_reg_date }" />
                        <InputError :message="form.errors.second_reg_date" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="vehicle_no">車両番号</Label>
                        <Input id="vehicle_no" v-model="form.vehicle_no" maxlength="100"
                            :class="{ 'border-destructive': form.errors.vehicle_no }" />
                        <InputError :message="form.errors.vehicle_no" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ■ 所有者情報 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">所有者情報</h2>
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label for="owner_name">氏名（漢字）</Label>
                        <Input id="owner_name" v-model="form.owner_name" maxlength="200"
                            :class="{ 'border-destructive': form.errors.owner_name }" />
                        <InputError :message="form.errors.owner_name" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="owner_kana">氏名（カナ）</Label>
                        <Input id="owner_kana" v-model="form.owner_kana" maxlength="200"
                            :class="{ 'border-destructive': form.errors.owner_kana }" />
                        <InputError :message="form.errors.owner_kana" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <Label for="birth_date">生年月日</Label>
                        <Input id="birth_date" v-model="form.birth_date" type="date"
                            :class="{ 'border-destructive': form.errors.birth_date }" />
                        <InputError :message="form.errors.birth_date" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>性別</Label>
                        <Select
                            :model-value="form.gender ?? '__none__'"
                            @update:model-value="(v) => (form.gender = v === '__none__' ? null : v)"
                        >
                            <SelectTrigger :class="{ 'border-destructive': form.errors.gender }">
                                <SelectValue placeholder="（未選択）" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">（未選択）</SelectItem>
                                <SelectItem v-for="(label, value) in genders" :key="value" :value="value">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.gender" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="zip_code">郵便番号</Label>
                        <Input id="zip_code" v-model="form.zip_code" maxlength="10" placeholder="000-0000"
                            :class="{ 'border-destructive': form.errors.zip_code }" />
                        <InputError :message="form.errors.zip_code" />
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="address1">住所1</Label>
                    <Input id="address1" v-model="form.address1" maxlength="200"
                        :class="{ 'border-destructive': form.errors.address1 }" />
                    <InputError :message="form.errors.address1" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="address2">住所2</Label>
                    <Input id="address2" v-model="form.address2" maxlength="200"
                        :class="{ 'border-destructive': form.errors.address2 }" />
                    <InputError :message="form.errors.address2" />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label for="tel">連絡先</Label>
                        <Input id="tel" v-model="form.tel" maxlength="20"
                            :class="{ 'border-destructive': form.errors.tel }" />
                        <InputError :message="form.errors.tel" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="mobile">携帯電話</Label>
                        <Input id="mobile" v-model="form.mobile" maxlength="20"
                            :class="{ 'border-destructive': form.errors.mobile }" />
                        <InputError :message="form.errors.mobile" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ■ 各種有無 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">各種有無</h2>
            <div class="flex flex-col gap-4">
                <!-- G防犯登録 -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_security_reg" :checked="form.has_security_reg"
                            @update:checked="(v) => (form.has_security_reg = v)" />
                        <Label for="has_security_reg" class="cursor-pointer">G防犯登録有無</Label>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="security_reg_date">G防犯加入日</Label>
                        <Input id="security_reg_date" v-model="form.security_reg_date" type="date"
                            :class="{ 'border-destructive': form.errors.security_reg_date }" />
                        <InputError :message="form.errors.security_reg_date" />
                    </div>
                </div>
                <!-- 盗難保険 -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_theft_insurance" :checked="form.has_theft_insurance"
                            @update:checked="(v) => (form.has_theft_insurance = v)" />
                        <Label for="has_theft_insurance" class="cursor-pointer">盗難保険有無</Label>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="theft_insurance_date">盗難保険加入日</Label>
                        <Input id="theft_insurance_date" v-model="form.theft_insurance_date" type="date"
                            :class="{ 'border-destructive': form.errors.theft_insurance_date }" />
                        <InputError :message="form.errors.theft_insurance_date" />
                    </div>
                </div>
                <!-- その他フラグ -->
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_warranty" :checked="form.has_warranty"
                            @update:checked="(v) => (form.has_warranty = v)" />
                        <Label for="has_warranty" class="cursor-pointer">保証書登録票有無</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_application" :checked="form.has_application"
                            @update:checked="(v) => (form.has_application = v)" />
                        <Label for="has_application" class="cursor-pointer">申請書有無</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_dm" :checked="form.has_dm"
                            @update:checked="(v) => (form.has_dm = v)" />
                        <Label for="has_dm" class="cursor-pointer">DM発送有無</Label>
                    </div>
                </div>
            </div>
        </section>

        <!-- ■ 特記事項・備考 -->
        <section class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">特記事項・備考</h2>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label for="note1">特記事項1</Label>
                    <Textarea id="note1" v-model="form.note1" rows="2" maxlength="1000"
                        :class="{ 'border-destructive': form.errors.note1 }" />
                    <InputError :message="form.errors.note1" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="note2">特記事項2</Label>
                    <Textarea id="note2" v-model="form.note2" rows="2" maxlength="1000"
                        :class="{ 'border-destructive': form.errors.note2 }" />
                    <InputError :message="form.errors.note2" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="note3">特記事項3</Label>
                    <Textarea id="note3" v-model="form.note3" rows="2" maxlength="1000"
                        :class="{ 'border-destructive': form.errors.note3 }" />
                    <InputError :message="form.errors.note3" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="remarks">備考</Label>
                    <Textarea id="remarks" v-model="form.remarks" rows="3" maxlength="1000"
                        :class="{ 'border-destructive': form.errors.remarks }" />
                    <InputError :message="form.errors.remarks" />
                </div>
            </div>
        </section>

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
