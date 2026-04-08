<script setup lang="ts">
import { ChevronsUpDown, Check } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';
import { cn } from '@/lib/utils';

interface Option {
    value: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        options: Option[];
        modelValue: string;
        placeholder?: string;
        class?: string;
        disabled?: boolean;
    }>(),
    {
        placeholder: '選択または検索...',
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const wrapperRef = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const open = ref(false);
const searchText = ref('');
const activeIndex = ref(-1);
const dropdownStyle = ref<Record<string, string>>({});

const selectedLabel = computed(
    () => props.options.find((o) => o.value === props.modelValue)?.label ?? '',
);

const filtered = computed(() => {
    const q = searchText.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(
        (o) =>
            o.label.toLowerCase().includes(q) ||
            o.value.toLowerCase().includes(q),
    );
});

function updatePosition() {
    if (!wrapperRef.value) return;
    const rect = wrapperRef.value.getBoundingClientRect();
    dropdownStyle.value = {
        top: `${rect.bottom + window.scrollY + 4}px`,
        left: `${rect.left + window.scrollX}px`,
        width: `${rect.width}px`,
    };
}

function openDropdown() {
    if (props.disabled) return;
    searchText.value = '';
    activeIndex.value = -1;
    updatePosition();
    open.value = true;
}

function closeDropdown() {
    open.value = false;
    searchText.value = '';
    activeIndex.value = -1;
}

function select(opt: Option) {
    emit('update:modelValue', opt.value);
    closeDropdown();
}

function onFocus() {
    openDropdown();
}

function onInput(e: Event) {
    searchText.value = (e.target as HTMLInputElement).value;
    activeIndex.value = -1;
    if (!open.value) {
        updatePosition();
        open.value = true;
    }
}

function onBlur() {
    setTimeout(closeDropdown, 150);
}

function toggle() {
    if (open.value) {
        closeDropdown();
    } else {
        openDropdown();
        nextTick(() => inputRef.value?.focus());
    }
}

function onKeydown(e: KeyboardEvent) {
    const len = filtered.value.length;

    if (!open.value) {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            openDropdown();
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = activeIndex.value < len - 1 ? activeIndex.value + 1 : 0;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = activeIndex.value > 0 ? activeIndex.value - 1 : len - 1;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeIndex.value >= 0 && filtered.value[activeIndex.value]) {
            select(filtered.value[activeIndex.value]);
        }
    } else if (e.key === 'Escape') {
        closeDropdown();
    }
}
</script>

<template>
    <div ref="wrapperRef" :class="cn('relative', props.class)">
        <div class="relative flex items-center">
            <input
                ref="inputRef"
                :value="open ? searchText : selectedLabel"
                :placeholder="
                    open
                        ? selectedLabel || placeholder
                        : (modelValue ? selectedLabel : placeholder)
                "
                :disabled="disabled"
                class="file:text-foreground placeholder:text-muted-foreground dark:bg-input/30 border-input focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 pr-8 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50"
                autocomplete="off"
                @focus="onFocus"
                @input="onInput"
                @blur="onBlur"
                @keydown="onKeydown"
            />
            <button
                type="button"
                :disabled="disabled"
                class="absolute right-2 text-muted-foreground hover:text-foreground disabled:pointer-events-none disabled:opacity-50"
                tabindex="-1"
                @mousedown.prevent
                @click="toggle"
            >
                <ChevronsUpDown class="h-4 w-4" />
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="open"
                class="absolute z-[200] max-h-60 overflow-y-auto rounded-md border bg-popover text-popover-foreground shadow-md"
                :style="dropdownStyle"
            >
                <div
                    v-if="filtered.length === 0"
                    class="px-3 py-4 text-center text-sm text-muted-foreground"
                >
                    見つかりません
                </div>
                <div
                    v-for="(opt, i) in filtered"
                    :key="opt.value"
                    class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm select-none"
                    :class="{
                        'bg-accent text-accent-foreground': i === activeIndex,
                        'hover:bg-accent hover:text-accent-foreground': i !== activeIndex,
                    }"
                    @mousedown.prevent
                    @click="select(opt)"
                >
                    <Check
                        class="h-4 w-4 shrink-0"
                        :class="
                            opt.value === modelValue
                                ? 'opacity-100'
                                : 'opacity-0'
                        "
                    />
                    {{ opt.label }}
                </div>
            </div>
        </Teleport>
    </div>
</template>
