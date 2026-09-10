<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    formName: String,
});
</script>

<template>
    <Head title="Thank You" />

    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 via-white to-emerald-50 p-4"
    >
        <div class="w-full max-w-md text-center">
            <!-- Success Icon -->
            <div class="mb-8">
                <div
                    class="animate-bounce-slow mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-emerald-500 shadow-2xl shadow-green-200"
                >
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-xl shadow-gray-200/50">
                <h1 class="mb-3 text-2xl font-bold text-gray-900">Thank You!</h1>
                <p class="mb-6 text-gray-600">
                    Your response to <span class="font-semibold text-gray-900">{{ formName }}</span> has been submitted
                    successfully.
                </p>
                <div class="mx-auto h-1 w-16 rounded-full bg-gradient-to-r from-green-400 to-emerald-500"></div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-sm text-gray-500">You can safely close this page now.</p>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}
</style>
