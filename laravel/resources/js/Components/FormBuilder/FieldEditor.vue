<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    field: Object,
    fields: Array,
    crmOptions: Array,
});

const emit = defineEmits(['update', 'close']);

// Local copy for editing
const localField = ref(JSON.parse(JSON.stringify(props.field)));
const activeTab = ref('general');

// Sync changes back
watch(
    localField,
    (newValue) => {
        emit('update', JSON.parse(JSON.stringify(newValue)));
    },
    { deep: true },
);

watch(
    () => props.field,
    (newField) => {
        localField.value = JSON.parse(JSON.stringify(newField));
    },
    { deep: true },
);

// Other fields for conditional logic
const otherFields = computed(() => {
    return props.fields.filter((f) => f.id !== props.field.id);
});

// Options management for select/radio/checkbox_group
const addOption = () => {
    if (!localField.value.options) {
        localField.value.options = [];
    }
    const num = localField.value.options.length + 1;
    localField.value.options.push({ value: `option_${num}`, label: `Option ${num}` });
};

const removeOption = (index) => {
    localField.value.options.splice(index, 1);
};

// Conditional logic toggle
const hasConditional = computed({
    get: () => !!localField.value.conditional?.field_key,
    set: (value) => {
        if (value) {
            localField.value.conditional = { field_key: '', operator: 'equals', value: '' };
        } else {
            localField.value.conditional = null;
        }
    },
});

const operators = [
    { value: 'equals', label: 'Equals' },
    { value: 'not_equals', label: 'Does not equal' },
    { value: 'contains', label: 'Contains' },
    { value: 'is_empty', label: 'Is empty' },
    { value: 'is_not_empty', label: 'Is not empty' },
    { value: 'greater_than', label: 'Greater than' },
    { value: 'less_than', label: 'Less than' },
];
</script>

<template>
    <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-800/50">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-white/10 p-4">
            <h3 class="text-sm font-semibold text-white">Edit Field</h3>
            <button @click="emit('close')" class="p-1 text-slate-400 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-white/10">
            <button
                v-for="tab in ['general', 'validation', 'logic', 'mapping']"
                :key="tab"
                @click="activeTab = tab"
                :class="[
                    'flex-1 px-3 py-2 text-xs font-medium transition-colors',
                    activeTab === tab
                        ? 'border-b-2 border-indigo-400 text-indigo-400'
                        : 'text-slate-500 hover:text-white',
                ]"
            >
                {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 space-y-4 overflow-y-auto p-4">
            <!-- General Tab -->
            <template v-if="activeTab === 'general'">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-400">Label</label>
                    <input
                        v-model="localField.label"
                        type="text"
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-400">Field Key</label>
                    <input
                        v-model="localField.key"
                        type="text"
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                    <p class="mt-1 text-xs text-slate-500">Unique identifier for this field</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-400">Placeholder</label>
                    <input
                        v-model="localField.placeholder"
                        type="text"
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-400">Help Text</label>
                    <textarea
                        v-model="localField.help_text"
                        rows="2"
                        class="w-full resize-none rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    ></textarea>
                </div>

                <!-- Options for select/radio/checkbox_group -->
                <div v-if="['select', 'radio', 'checkbox_group'].includes(localField.type)">
                    <label class="mb-2 block text-xs font-medium text-slate-400">Options</label>
                    <div class="space-y-2">
                        <div v-for="(option, index) in localField.options" :key="index" class="flex gap-2">
                            <input
                                v-model="option.label"
                                type="text"
                                placeholder="Label"
                                class="flex-1 rounded border border-white/10 bg-white/5 px-2 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                            <input
                                v-model="option.value"
                                type="text"
                                placeholder="Value"
                                class="w-24 rounded border border-white/10 bg-white/5 px-2 py-1.5 font-mono text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                            <button @click="removeOption(index)" class="p-1.5 text-slate-400 hover:text-rose-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button @click="addOption" class="mt-2 text-xs text-indigo-400 hover:text-indigo-300">
                        + Add Option
                    </button>
                </div>
            </template>

            <!-- Validation Tab -->
            <template v-if="activeTab === 'validation'">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        v-model="localField.validation.required"
                        class="h-4 w-4 rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-white">Required</span>
                </label>

                <div v-if="localField.type === 'number'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-400">Min Value</label>
                            <input
                                v-model.number="localField.validation.min"
                                type="number"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-400">Max Value</label>
                            <input
                                v-model.number="localField.validation.max"
                                type="number"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="['text', 'textarea'].includes(localField.type)">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-400">Min Length</label>
                            <input
                                v-model.number="localField.validation.minLength"
                                type="number"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-400">Max Length</label>
                            <input
                                v-model.number="localField.validation.maxLength"
                                type="number"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Logic Tab (Conditional) -->
            <template v-if="activeTab === 'logic'">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        v-model="hasConditional"
                        class="h-4 w-4 rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-white">Show conditionally</span>
                </label>

                <div v-if="hasConditional" class="mt-3 space-y-3 rounded-lg border border-white/10 bg-white/5 p-3">
                    <p class="text-xs text-slate-400">Show this field when:</p>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-400">Field</label>
                        <select
                            v-model="localField.conditional.field_key"
                            class="w-full rounded-lg border border-white/10 bg-slate-700 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="">Select a field</option>
                            <option v-for="f in otherFields" :key="f.id" :value="f.key">{{ f.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-400">Condition</label>
                        <select
                            v-model="localField.conditional.operator"
                            class="w-full rounded-lg border border-white/10 bg-slate-700 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option v-for="op in operators" :key="op.value" :value="op.value">{{ op.label }}</option>
                        </select>
                    </div>

                    <div v-if="!['is_empty', 'is_not_empty'].includes(localField.conditional.operator)">
                        <label class="mb-1.5 block text-xs font-medium text-slate-400">Value</label>
                        <input
                            v-model="localField.conditional.value"
                            type="text"
                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>
            </template>

            <!-- Mapping Tab -->
            <template v-if="activeTab === 'mapping'">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-400">CRM Field Mapping</label>
                    <select
                        v-model="localField.crm_mapping"
                        class="w-full rounded-lg border border-white/10 bg-slate-700 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    >
                        <option :value="null">No mapping</option>
                        <option v-for="opt in crmOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">
                        When a submission is received, this field's value will be saved to the selected CRM field.
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>
