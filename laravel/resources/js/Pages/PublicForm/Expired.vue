<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    message: String,
});
</script>

<template>
    <Head title="Form Unavailable" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-slate-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <!-- Error Icon -->
            <div class="mb-8">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-300 to-slate-400 rounded-full mx-auto flex items-center justify-center shadow-xl">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-3">Form Unavailable</h1>
                <p class="text-gray-600">
                    {{ message || 'This form is no longer accepting responses.' }}
                </p>
            </div>

            <!-- Contact -->
            <p class="mt-8 text-sm text-gray-500">
                Please contact the form administrator for assistance.
            </p>
        </div>
    </div>
</template>
