<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface WarehouseFormData {
    code: string;
    name: string;
    errors: {
        code?: string;
        name?: string;
    };
    processing: boolean;
}

interface Props {
    form: WarehouseFormData;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
    isEdit?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{ submit: [] }>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
        <!-- 倉庫コード -->
        <div class="flex flex-col gap-1.5">
            <Label for="code">
                倉庫コード
                <span v-if="!isEdit" class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="code"
                v-model="form.code"
                placeholder="例：WH001"
                maxlength="20"
                :disabled="isEdit"
                :class="{
                    'border-destructive': form.errors.code,
                    'bg-muted/40 cursor-not-allowed': isEdit,
                }"
            />
            <InputError :message="form.errors.code" />
        </div>

        <!-- 倉庫名 -->
        <div class="flex flex-col gap-1.5">
            <Label for="name">
                倉庫名
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="name"
                v-model="form.name"
                placeholder="例：第1倉庫"
                maxlength="100"
                :class="{ 'border-destructive': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
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
