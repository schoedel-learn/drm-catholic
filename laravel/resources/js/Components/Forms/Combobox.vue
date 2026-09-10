<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Object],
        default: null,
    },
    options: {
        type: Array,
        required: true,
        // Array of { value: any, label: string, description?: string }
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Search...',
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    valueKey: {
        type: String,
        default: 'value',
    },
    labelKey: {
        type: String,
        default: 'label',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref(null);
const highlightedIndex = ref(0);

const getOptionValue = (option) => {
    return typeof option === 'object' ? option[props.valueKey] : option;
};

const getOptionLabel = (option) => {
    return typeof option === 'object' ? option[props.labelKey] : option;
};

const selectedOption = computed(() => {
    return props.options.find((opt) => getOptionValue(opt) === props.modelValue);
});

const displayValue = computed(() => {
    if (selectedOption.value) {
        return getOptionLabel(selectedOption.value);
    }
    return '';
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;

    const query = searchQuery.value.toLowerCase();
    return props.options.filter((opt) => getOptionLabel(opt).toLowerCase().includes(query));
});

const selectOption = (option) => {
    emit('update:modelValue', getOptionValue(option));
    isOpen.value = false;
    searchQuery.value = '';
};

const handleKeydown = (e) => {
    if (!isOpen.value) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1);
            break;
        case 'ArrowUp':
            e.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
            break;
        case 'Enter':
            e.preventDefault();
            if (filteredOptions.value[highlightedIndex.value]) {
                selectOption(filteredOptions.value[highlightedIndex.value]);
            }
            break;
        case 'Escape':
            isOpen.value = false;
            break;
    }
};

const toggleOpen = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        highlightedIndex.value = 0;
        setTimeout(() => searchInput.value?.focus(), 0);
    }
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.combobox-container')) {
        isOpen.value = false;
    }
};

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', handleClickOutside);
    } else {
        document.removeEventListener('click', handleClickOutside);
        searchQuery.value = '';
    }
});

watch(searchQuery, () => {
    highlightedIndex.value = 0;
});
</script>

<template>
    <div class="combobox-container relative" @keydown="handleKeydown">
        <!-- Label -->
        <label v-if="label" class="mb-2 block text-sm font-medium text-slate-300">
            {{ label }}
        </label>

        <!-- Trigger -->
        <button
            type="button"
            @click="toggleOpen"
            :disabled="disabled"
            :class="[
                'flex w-full items-center justify-between gap-3 rounded-xl border bg-white/5 px-4 py-3 text-left transition-all duration-200',
                error
                    ? 'border-rose-500/50 focus:border-rose-500'
                    : isOpen
                      ? 'border-indigo-500 ring-2 ring-indigo-500/20'
                      : 'border-white/10 hover:border-white/20',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
        >
            <span :class="displayValue ? 'text-white' : 'text-slate-500'">
                {{ displayValue || placeholder }}
            </span>
            <svg
                :class="['h-5 w-5 text-slate-400 transition-transform', isOpen ? 'rotate-180' : '']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Error -->
        <p v-if="error" class="mt-2 text-sm text-rose-400">{{ error }}</p>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-white/10 bg-slate-800 shadow-2xl shadow-black/50"
        >
            <!-- Search Input -->
            <div v-if="searchable" class="border-b border-white/10 p-2">
                <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="text"
                    :placeholder="placeholder"
                    class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                />
            </div>

            <!-- Options -->
            <ul class="max-h-60 overflow-auto py-1">
                <li
                    v-for="(option, index) in filteredOptions"
                    :key="getOptionValue(option)"
                    @click="selectOption(option)"
                    :class="[
                        'cursor-pointer px-4 py-2.5 transition-colors',
                        index === highlightedIndex ? 'bg-indigo-500/20' : 'hover:bg-white/5',
                        getOptionValue(option) === modelValue ? 'text-indigo-400' : 'text-white',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ getOptionLabel(option) }}</span>
                        <svg
                            v-if="getOptionValue(option) === modelValue"
                            class="h-4 w-4 text-indigo-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p v-if="option.description" class="mt-0.5 text-xs text-slate-500">
                        {{ option.description }}
                    </p>
                </li>

                <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-center text-sm text-slate-500">
                    No options found
                </li>
            </ul>
        </div>
    </div>
</template>
