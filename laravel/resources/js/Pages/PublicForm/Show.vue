<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    form: Object,
    fields: Array,
});

const formData = useForm({
    submitter_name: '',
    submitter_email: '',
    data: {},
    website_url: '', // Honeypot field
});

const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;
    formData.post(route('public-form.submit', props.form.token), {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="form.name" />
    
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-10">
            <div class="max-w-2xl mx-auto px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-semibold text-gray-900">{{ form.name }}</h1>
                        <p class="text-sm text-gray-500">Secure Form</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Description Card -->
            <div v-if="form.description" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                <p class="text-gray-600 leading-relaxed">{{ form.description }}</p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                    <h2 class="text-lg font-semibold text-white">Your Information</h2>
                    <p class="text-indigo-100 text-sm mt-1">Please fill out the fields below</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Error Message -->
                    <div v-if="formData.errors.form" class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <p class="text-red-600 text-sm">{{ formData.errors.form }}</p>
                    </div>

                    <!-- Contact Info Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="submitter_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Your Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="submitter_name"
                                v-model="formData.submitter_name"
                                type="text"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                placeholder="John Smith"
                            />
                            <p v-if="formData.errors.submitter_name" class="text-red-500 text-sm mt-1">{{ formData.errors.submitter_name }}</p>
                        </div>

                        <div>
                            <label for="submitter_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="submitter_email"
                                v-model="formData.submitter_email"
                                type="email"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                placeholder="you@example.com"
                            />
                            <p v-if="formData.errors.submitter_email" class="text-red-500 text-sm mt-1">{{ formData.errors.submitter_email }}</p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-100"></div>

                    <!-- Custom Fields -->
                    <div class="space-y-5">
                        <div v-for="field in fields" :key="field.key">
                            <label :for="field.key" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ field.label }}
                                <span v-if="field.required" class="text-red-500">*</span>
                            </label>

                            <!-- Text -->
                            <input
                                v-if="field.type === 'text'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="text"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                            />

                            <!-- Textarea -->
                            <textarea
                                v-else-if="field.type === 'textarea'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                :required="field.required"
                                rows="4"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none resize-none"
                            ></textarea>

                            <!-- Number -->
                            <input
                                v-else-if="field.type === 'number'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="number"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                            />

                            <!-- Date -->
                            <input
                                v-else-if="field.type === 'date'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="date"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                            />

                            <!-- Email -->
                            <input
                                v-else-if="field.type === 'email'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="email"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                            />

                            <!-- Phone -->
                            <input
                                v-else-if="field.type === 'phone'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="tel"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                placeholder="(555) 123-4567"
                            />

                            <!-- URL -->
                            <input
                                v-else-if="field.type === 'url'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="url"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                placeholder="https://"
                            />

                            <!-- Currency -->
                            <div v-else-if="field.type === 'currency'" class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                <input
                                    :id="field.key"
                                    v-model="formData.data[field.key]"
                                    type="number"
                                    step="0.01"
                                    :required="field.required"
                                    class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                />
                            </div>

                            <!-- Checkbox -->
                            <label v-else-if="field.type === 'checkbox'" class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    :id="field.key"
                                    v-model="formData.data[field.key]"
                                    class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-gray-600">Yes</span>
                            </label>

                            <!-- Select -->
                            <select
                                v-else-if="field.type === 'select'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                :required="field.required"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none bg-white"
                            >
                                <option value="">Select an option...</option>
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>

                            <!-- Multi-Select -->
                            <select
                                v-else-if="field.type === 'multiselect'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                :required="field.required"
                                multiple
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none bg-white min-h-[120px]"
                            >
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Honeypot (hidden from real users) -->
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <input type="text" name="website_url" v-model="formData.website_url" tabindex="-1" autocomplete="off" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <button
                        type="submit"
                        :disabled="formData.processing"
                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-4 px-6 rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <span v-if="formData.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                        <span v-else>Submit Response</span>
                    </button>
                </div>
            </form>

            <!-- Security Footer -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Your response is secure and encrypted</span>
                </div>
            </div>
        </div>
    </div>
</template>
