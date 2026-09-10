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
        <div class="sticky top-0 z-10 border-b border-gray-100 bg-white/80 backdrop-blur-sm">
            <div class="mx-auto max-w-2xl px-4 py-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-200"
                    >
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
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
        <div class="mx-auto max-w-2xl px-4 py-8">
            <!-- Description Card -->
            <div v-if="form.description" class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="leading-relaxed text-gray-600">{{ form.description }}</p>
            </div>

            <!-- Form Card -->
            <form
                @submit.prevent="submit"
                class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl shadow-gray-200/50"
            >
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                    <h2 class="text-lg font-semibold text-white">Your Information</h2>
                    <p class="mt-1 text-sm text-indigo-100">Please fill out the fields below</p>
                </div>

                <div class="space-y-6 p-6">
                    <!-- Error Message -->
                    <div v-if="formData.errors.form" class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm text-red-600">{{ formData.errors.form }}</p>
                    </div>

                    <!-- Contact Info Section -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="submitter_name" class="mb-2 block text-sm font-medium text-gray-700">
                                Your Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="submitter_name"
                                v-model="formData.submitter_name"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                placeholder="John Smith"
                            />
                            <p v-if="formData.errors.submitter_name" class="mt-1 text-sm text-red-500">
                                {{ formData.errors.submitter_name }}
                            </p>
                        </div>

                        <div>
                            <label for="submitter_email" class="mb-2 block text-sm font-medium text-gray-700">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="submitter_email"
                                v-model="formData.submitter_email"
                                type="email"
                                required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                placeholder="you@example.com"
                            />
                            <p v-if="formData.errors.submitter_email" class="mt-1 text-sm text-red-500">
                                {{ formData.errors.submitter_email }}
                            </p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-100"></div>

                    <!-- Custom Fields -->
                    <div class="space-y-5">
                        <div v-for="field in fields" :key="field.key">
                            <label :for="field.key" class="mb-2 block text-sm font-medium text-gray-700">
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
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />

                            <!-- Textarea -->
                            <textarea
                                v-else-if="field.type === 'textarea'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                :required="field.required"
                                rows="4"
                                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            ></textarea>

                            <!-- Number -->
                            <input
                                v-else-if="field.type === 'number'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="number"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />

                            <!-- Date -->
                            <input
                                v-else-if="field.type === 'date'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="date"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />

                            <!-- Email -->
                            <input
                                v-else-if="field.type === 'email'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="email"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />

                            <!-- Phone -->
                            <input
                                v-else-if="field.type === 'phone'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="tel"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                placeholder="(555) 123-4567"
                            />

                            <!-- URL -->
                            <input
                                v-else-if="field.type === 'url'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                type="url"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                placeholder="https://"
                            />

                            <!-- Currency -->
                            <div v-else-if="field.type === 'currency'" class="relative">
                                <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500">$</span>
                                <input
                                    :id="field.key"
                                    v-model="formData.data[field.key]"
                                    type="number"
                                    step="0.01"
                                    :required="field.required"
                                    class="w-full rounded-xl border border-gray-200 py-3 pr-4 pl-8 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                />
                            </div>

                            <!-- Checkbox -->
                            <label v-else-if="field.type === 'checkbox'" class="flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    :id="field.key"
                                    v-model="formData.data[field.key]"
                                    class="h-5 w-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-gray-600">Yes</span>
                            </label>

                            <!-- Select -->
                            <select
                                v-else-if="field.type === 'select'"
                                :id="field.key"
                                v-model="formData.data[field.key]"
                                :required="field.required"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
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
                                class="min-h-[120px] w-full rounded-xl border border-gray-200 bg-white px-4 py-3 transition-all duration-200 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            >
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Honeypot (hidden from real users) -->
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <input
                            type="text"
                            name="website_url"
                            v-model="formData.website_url"
                            tabindex="-1"
                            autocomplete="off"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <button
                        type="submit"
                        :disabled="formData.processing"
                        class="w-full transform rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4 font-semibold text-white shadow-lg shadow-indigo-200 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-300 disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="formData.processing" class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24">
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    fill="none"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
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
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                    <span>Your response is secure and encrypted</span>
                </div>
            </div>
        </div>
    </div>
</template>
