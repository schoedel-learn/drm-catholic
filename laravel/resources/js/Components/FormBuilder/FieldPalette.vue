<script setup>
const emit = defineEmits(['add-field']);

const fieldTypes = [
    { type: 'text', label: 'Text Input', icon: 'type', description: 'Single line text' },
    { type: 'textarea', label: 'Text Area', icon: 'align-left', description: 'Multi-line text' },
    { type: 'email', label: 'Email', icon: 'mail', description: 'Email address' },
    { type: 'phone', label: 'Phone', icon: 'phone', description: 'Phone number' },
    { type: 'number', label: 'Number', icon: 'hash', description: 'Numeric value' },
    { type: 'select', label: 'Dropdown', icon: 'chevron-down', description: 'Select from list' },
    { type: 'radio', label: 'Radio', icon: 'circle', description: 'Single choice' },
    { type: 'checkbox', label: 'Checkbox', icon: 'check-square', description: 'Yes/No toggle' },
    { type: 'checkbox_group', label: 'Checkboxes', icon: 'list', description: 'Multiple choice' },
    { type: 'date', label: 'Date', icon: 'calendar', description: 'Date picker' },
    { type: 'file', label: 'File Upload', icon: 'upload', description: 'File attachment' },
];
</script>

<template>
    <div class="bg-slate-800/50 rounded-2xl border border-white/10 p-4 h-full overflow-y-auto">
        <h3 class="text-sm font-semibold text-white mb-4 px-2">Add Field</h3>
        
        <div class="space-y-1">
            <button
                v-for="fieldType in fieldTypes"
                :key="fieldType.type"
                @click="emit('add-field', fieldType)"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left text-sm transition-all duration-200 text-slate-300 hover:bg-indigo-500/20 hover:text-white group"
            >
                <!-- Icons -->
                <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-500/30 group-hover:text-indigo-400 transition-colors">
                    <svg v-if="fieldType.icon === 'type'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'align-left'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h10M4 14h16M4 18h10" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'mail'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'phone'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'hash'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'chevron-down'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'circle'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'check-square'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'list'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'calendar'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <svg v-else-if="fieldType.icon === 'upload'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="font-medium">{{ fieldType.label }}</div>
                    <div class="text-xs text-slate-500 group-hover:text-slate-400">{{ fieldType.description }}</div>
                </div>
            </button>
        </div>
    </div>
</template>
