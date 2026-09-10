<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    forms: Array,
});
</script>

<template>
    <AppLayout title="Form Builder">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800">Form Builder</h2>
                <Link
                    :href="route('form-builder.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition hover:bg-indigo-700"
                >
                    Create Form
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="forms.length === 0"
                    class="overflow-hidden bg-white p-12 text-center shadow-sm sm:rounded-lg"
                >
                    <div class="mb-4 text-gray-400">
                        <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-medium text-gray-900">No forms yet</h3>
                    <p class="mb-6 text-gray-500">
                        Create your first public form to collect data from contacts or parishes.
                    </p>
                    <Link
                        :href="route('form-builder.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-indigo-700"
                    >
                        Create Your First Form
                    </Link>
                </div>

                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Form
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Responses
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="form in forms" :key="form.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ form.name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ form.description?.substring(0, 50)
                                        }}{{ form.description?.length > 50 ? '...' : '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                        :class="
                                            form.entity_type === 'contact'
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-purple-100 text-purple-800'
                                        "
                                    >
                                        {{ form.entity_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                    {{ form.submissions_count }} responses
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="form.is_active"
                                        class="inline-flex rounded-full bg-green-100 px-2 text-xs leading-5 font-semibold text-green-800"
                                    >
                                        Active
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-gray-100 px-2 text-xs leading-5 font-semibold text-gray-800"
                                    >
                                        Inactive
                                    </span>
                                </td>
                                <td class="space-x-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <Link
                                        :href="route('form-builder.show', form.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                        >View</Link
                                    >
                                    <button
                                        @click="
                                            navigator.clipboard.writeText(
                                                `${window.location.origin}/forms/${form.token}`,
                                            )
                                        "
                                        class="text-gray-600 hover:text-gray-900"
                                    >
                                        Copy Link
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
