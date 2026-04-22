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
watch(localField, (newValue) => {
    emit('update', JSON.parse(JSON.stringify(newValue)));
}, { deep: true });

watch(() => props.field, (newField) => {
    localField.value = JSON.parse(JSON.stringify(newField));
}, { deep: true });

// Other fields for conditional logic
const otherFields = computed(() => {
    return props.fields.filter(f => f.id !== props.field.id);
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
    <div class="h-full bg-slate-800/50 rounded-2xl border border-white/10 flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-white/10">
            <h3 class="text-sm font-semibold text-white">Edit Field</h3>
            <button @click="emit('close')" class="p-1 text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        ? 'text-indigo-400 border-b-2 border-indigo-400' 
                        : 'text-slate-500 hover:text-white'
                ]"
            >
                {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
            </button>
        </div>
        
        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- General Tab -->
            <template v-if="activeTab === 'general'">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Label</label>
                    <input 
                        v-model="localField.label"
                        type="text"
                        class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Field Key</label>
                    <input 
                        v-model="localField.key"
                        type="text"
                        class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:border-indigo-500 focus:outline-none font-mono"
                    />
                    <p class="mt-1 text-xs text-slate-500">Unique identifier for this field</p>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Placeholder</label>
                    <input 
                        v-model="localField.placeholder"
                        type="text"
                        class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Help Text</label>
                    <textarea 
                        v-model="localField.help_text"
                        rows="2"
                        class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:border-indigo-500 focus:outline-none resize-none"
                    ></textarea>
                </div>
                
                <!-- Options for select/radio/checkbox_group -->
                <div v-if="['select', 'radio', 'checkbox_group'].includes(localField.type)">
                    <label class="block text-xs font-medium text-slate-400 mb-2">Options</label>
                    <div class="space-y-2">
                        <div 
                            v-for="(option, index) in localField.options" 
                            :key="index"
                            class="flex gap-2"
                        >
                            <input 
                                v-model="option.label"
                                type="text"
                                placeholder="Label"
                                class="flex-1 px-2 py-1.5 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                            <input 
                                v-model="option.value"
                                type="text"
                                placeholder="Value"
                                class="w-24 px-2 py-1.5 bg-white/5 border border-white/10 rounded text-sm text-white font-mono focus:outline-none focus:border-indigo-500"
                            />
                            <button 
                                @click="removeOption(index)"
                                class="p-1.5 text-slate-400 hover:text-rose-400"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button 
                        @click="addOption"
                        class="mt-2 text-xs text-indigo-400 hover:text-indigo-300"
                    >
                        + Add Option
                    </button>
                </div>
            </template>
            
            <!-- Validation Tab -->
            <template v-if="activeTab === 'validation'">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        v-model="localField.validation.required"
                        class="w-4 h-4 rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-white">Required</span>
                </label>
                
                <div v-if="localField.type === 'number'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Min Value</label>
                            <input 
                                v-model.number="localField.validation.min"
                                type="number"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Max Value</label>
                            <input 
                                v-model.number="localField.validation.max"
                                type="number"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>
                    </div>
                </div>
                
                <div v-if="['text', 'textarea'].includes(localField.type)">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Min Length</label>
                            <input 
                                v-model.number="localField.validation.minLength"
                                type="number"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Max Length</label>
                            <input 
                                v-model.number="localField.validation.maxLength"
                                type="number"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- Logic Tab (Conditional) -->
            <template v-if="activeTab === 'logic'">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        v-model="hasConditional"
                        class="w-4 h-4 rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-white">Show conditionally</span>
                </label>
                
                <div v-if="hasConditional" class="space-y-3 mt-3 p-3 bg-white/5 rounded-lg border border-white/10">
                    <p class="text-xs text-slate-400">Show this field when:</p>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Field</label>
                        <select 
                            v-model="localField.conditional.field_key"
                            class="w-full px-3 py-2 bg-slate-700 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                        >
                            <option value="">Select a field</option>
                            <option v-for="f in otherFields" :key="f.id" :value="f.key">{{ f.label }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Condition</label>
                        <select 
                            v-model="localField.conditional.operator"
                            class="w-full px-3 py-2 bg-slate-700 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                        >
                            <option v-for="op in operators" :key="op.value" :value="op.value">{{ op.label }}</option>
                        </select>
                    </div>
                    
                    <div v-if="!['is_empty', 'is_not_empty'].includes(localField.conditional.operator)">
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Value</label>
                        <input 
                            v-model="localField.conditional.value"
                            type="text"
                            class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
                        />
                    </div>
                </div>
            </template>
            
            <!-- Mapping Tab -->
            <template v-if="activeTab === 'mapping'">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">CRM Field Mapping</label>
                    <select 
                        v-model="localField.crm_mapping"
                        class="w-full px-3 py-2 bg-slate-700 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500"
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
