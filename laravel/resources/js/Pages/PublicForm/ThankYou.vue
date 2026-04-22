<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    formName: String,
});
</script>

<template>
    <Head title="Thank You" />
    
    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-emerald-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <!-- Success Icon -->
            <div class="mb-8">
                <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full mx-auto flex items-center justify-center shadow-2xl shadow-green-200 animate-bounce-slow">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-3">Thank You!</h1>
                <p class="text-gray-600 mb-6">
                    Your response to <span class="font-semibold text-gray-900">{{ formName }}</span> has been submitted successfully.
                </p>
                <div class="h-1 w-16 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full mx-auto"></div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-sm text-gray-500">
                You can safely close this page now.
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}
</style>
