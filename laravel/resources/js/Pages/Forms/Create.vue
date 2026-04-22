<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { FormBuilderCanvas } from '@/Components/FormBuilder';
import { Button, Card } from '@/Components/UI';

const props = defineProps({
    customFields: Object, // Grouped by entity_type
    templates: Array,
});

const form = useForm({
    name: '',
    description: '',
    entity_type: 'contact',
    fields: [],
    expires_at: '',
    template_id: null,
    save_as_template: false,
    template_name: '',
});

const step = ref(1); // 1: Basic info, 2: Build fields

const currentCustomFields = computed(() => {
    return props.customFields?.[form.entity_type] || [];
});

const goToBuilder = () => {
    if (form.name) {
        step.value = 2;
    }
};

const goBack = () => {
    step.value = 1;
};

const submit = () => {
    form.post(route('form-builder.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <DashboardLayout title="Create Form">
        <template #header>
            <div class="flex items-center gap-4">
                <button 
                    v-if="step === 2"
                    @click="goBack"
                    class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-white">Create Form</h1>
            </div>
        </template>

        <!-- Step 1: Basic Info -->
        <div v-if="step === 1" class="max-w-2xl mx-auto">
            <Card variant="glass" class="p-6">
                <h2 class="text-xl font-semibold text-white mb-6">Form Details</h2>
                
                <div class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Form Name *</label>
                        <input 
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                            placeholder="e.g. Annual Parish Report 2026"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-rose-400">{{ form.errors.name }}</p>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                        <textarea 
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none"
                            placeholder="Instructions shown to respondents..."
                        ></textarea>
                    </div>
                    
                    <!-- Entity Type -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Form Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="form.entity_type = 'contact'"
                                :class="[
                                    'p-4 rounded-xl border text-left transition-all',
                                    form.entity_type === 'contact'
                                        ? 'bg-indigo-500/20 border-indigo-500/50 text-white'
                                        : 'bg-white/5 border-white/10 text-slate-400 hover:border-white/20'
                                ]"
                            >
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">Contact</span>
                                </div>
                                <p class="text-xs text-slate-500">For individuals (parishioners, clergy, staff)</p>
                            </button>
                            <button
                                type="button"
                                @click="form.entity_type = 'organization'"
                                :class="[
                                    'p-4 rounded-xl border text-left transition-all',
                                    form.entity_type === 'organization'
                                        ? 'bg-indigo-500/20 border-indigo-500/50 text-white'
                                        : 'bg-white/5 border-white/10 text-slate-400 hover:border-white/20'
                                ]"
                            >
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="font-medium">Organization</span>
                                </div>
                                <p class="text-xs text-slate-500">For parishes, schools, institutions</p>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Expiry -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Expiration Date (Optional)</label>
                        <input 
                            v-model="form.expires_at"
                            type="datetime-local"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                        />
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <Button 
                        @click="goToBuilder"
                        :disabled="!form.name"
                        variant="primary"
                    >
                        Continue to Builder
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Step 2: Form Builder -->
        <div v-else class="h-full">
            <FormBuilderCanvas
                v-model="form.fields"
                :entity-type="form.entity_type"
                :custom-fields="currentCustomFields"
            />
            
            <!-- Bottom action bar -->
            <div class="fixed bottom-0 left-0 right-0 lg:left-64 bg-slate-900/95 backdrop-blur-xl border-t border-white/10 px-6 py-4">
                <div class="flex items-center justify-between max-w-7xl mx-auto">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-400">
                            <input 
                                type="checkbox" 
                                v-model="form.save_as_template"
                                class="w-4 h-4 rounded border-white/20 bg-white/5 text-indigo-500"
                            />
                            Save as template
                        </label>
                        <input 
                            v-if="form.save_as_template"
                            v-model="form.template_name"
                            type="text"
                            placeholder="Template name"
                            class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                        />
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-400">
                            {{ form.fields.length }} fields
                        </span>
                        <Button 
                            @click="submit"
                            :loading="form.processing"
                            :disabled="form.fields.length === 0"
                            variant="primary"
                        >
                            Create Form
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
