<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    message: String,
});
</script>

<template>
    <Head title="Form Unavailable" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-50 via-white to-slate-50 p-4">
        <div class="w-full max-w-md text-center">
            <!-- Error Icon -->
            <div class="mb-8">
                <div
                    class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-gray-300 to-slate-400 shadow-xl"
                >
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-xl shadow-gray-200/50">
                <h1 class="mb-3 text-2xl font-bold text-gray-900">Form Unavailable</h1>
                <p class="text-gray-600">
                    {{ message || 'This form is no longer accepting responses.' }}
                </p>
            </div>

            <!-- Contact -->
            <p class="mt-8 text-sm text-gray-500">Please contact the form administrator for assistance.</p>
        </div>
    </div>
</template>
