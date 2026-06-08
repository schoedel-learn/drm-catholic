<script setup>
import { computed } from 'vue';

const props = defineProps({
    field: Object,
    index: Number,
    isSelected: Boolean,
    isDragging: Boolean,
    isDragOver: Boolean,
});

const emit = defineEmits(['select', 'remove', 'duplicate', 'dragstart', 'dragover', 'dragend']);

const fieldTypeLabel = computed(() => {
    const labels = {
        text: 'Text',
        textarea: 'Text Area',
        email: 'Email',
        phone: 'Phone',
        number: 'Number',
        select: 'Dropdown',
        radio: 'Radio',
        checkbox: 'Checkbox',
        checkbox_group: 'Checkboxes',
        date: 'Date',
        file: 'File',
    };
    return labels[props.field.type] || props.field.type;
});
</script>

<template>
    <div
        :class="[
            'group relative rounded-xl border p-4 cursor-pointer transition-all duration-200',
            isSelected 
                ? 'bg-indigo-500/10 border-indigo-500/50 ring-2 ring-indigo-500/20' 
                : 'bg-white/5 border-white/10 hover:border-white/20 hover:bg-white/10',
            isDragging ? 'opacity-50 scale-95' : '',
            isDragOver ? 'border-indigo-400 border-dashed' : '',
        ]"
        draggable="true"
        @click="emit('select')"
        @dragstart="emit('dragstart')"
        @dragover.prevent="emit('dragover')"
        @dragend="emit('dragend')"
    >
        <!-- Drag Handle -->
        <div 
            class="absolute left-2 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity cursor-grab active:cursor-grabbing"
        >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
        </div>
        
        <!-- Field Content -->
        <div class="pl-4">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-medium text-indigo-400 bg-indigo-500/20 px-2 py-0.5 rounded">
                    {{ fieldTypeLabel }}
                </span>
                <span v-if="field.validation?.required" class="text-xs text-rose-400">Required</span>
                <span v-if="field.conditional" class="text-xs text-amber-400">Conditional</span>
            </div>
            
            <div class="text-white font-medium">{{ field.label }}</div>
            <div v-if="field.help_text" class="text-sm text-slate-500 mt-1">{{ field.help_text }}</div>
            
            <!-- Preview based on type -->
            <div class="mt-3 pointer-events-none">
                <input 
                    v-if="['text', 'email', 'phone', 'number'].includes(field.type)"
                    :type="field.type === 'email' ? 'email' : field.type === 'number' ? 'number' : 'text'"
                    :placeholder="field.placeholder || `Enter ${field.label.toLowerCase()}`"
                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-400 placeholder-slate-600"
                    disabled
                />
                <textarea 
                    v-else-if="field.type === 'textarea'"
                    :placeholder="field.placeholder"
                    rows="2"
                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-400 placeholder-slate-600 resize-none"
                    disabled
                ></textarea>
                <select 
                    v-else-if="field.type === 'select'"
                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-500"
                    disabled
                >
                    <option>{{ field.placeholder || 'Select an option' }}</option>
                </select>
                <div v-else-if="field.type === 'radio' || field.type === 'checkbox_group'" class="space-y-2">
                    <label 
                        v-for="(opt, i) in (field.options || []).slice(0, 3)" 
                        :key="i"
                        class="flex items-center gap-2 text-sm text-slate-400"
                    >
                        <div :class="['w-4 h-4 border border-white/20 bg-white/5', field.type === 'radio' ? 'rounded-full' : 'rounded']"></div>
                        {{ opt.label }}
                    </label>
                </div>
                <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 text-sm text-slate-400">
                    <div class="w-4 h-4 border border-white/20 bg-white/5 rounded"></div>
                    {{ field.label }}
                </div>
                <div v-else-if="field.type === 'date'" class="flex items-center gap-2 px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Select a date
                </div>
                <div v-else-if="field.type === 'file'" class="flex items-center justify-center gap-2 px-4 py-4 bg-white/5 border border-dashed border-white/20 rounded-lg text-sm text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Click or drag to upload
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="absolute right-2 top-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button 
                @click.stop="emit('duplicate')"
                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10"
                title="Duplicate"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>
            <button 
                @click.stop="emit('remove')"
                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10"
                title="Delete"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</template>
