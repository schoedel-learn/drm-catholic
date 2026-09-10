<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    form: Object,
    customFields: Object,
});

const copied = ref(false);

const copyLink = () => {
    navigator.clipboard.writeText(`${window.location.origin}/forms/${props.form.token}`);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const toggleActive = () => {
    router.post(route('form-builder.toggle', props.form.id));
};

const deleteForm = () => {
    if (confirm('Are you sure you want to delete this form? All submissions will be permanently deleted.')) {
        router.delete(route('form-builder.destroy', props.form.id));
    }
};
</script>

<template>
    <AppLayout title="View Form">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800">
                    {{ form.name }}
                </h2>
                <div class="flex gap-2">
                    <button
                        @click="toggleActive"
                        class="rounded-md px-4 py-2 text-sm font-medium"
                        :class="
                            form.is_active
                                ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                : 'bg-green-100 text-green-700 hover:bg-green-200'
                        "
                    >
                        {{ form.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        @click="deleteForm"
                        class="rounded-md bg-red-100 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-200"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Form Link Card -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Public Form Link</h3>
                    <div class="flex items-center gap-4">
                        <code class="flex-1 rounded-lg bg-gray-100 px-4 py-3 text-sm break-all">
                            {{ `${window.location.origin}/forms/${form.token}` }}
                        </code>
                        <button
                            @click="copyLink"
                            class="rounded-md bg-indigo-600 px-4 py-2 whitespace-nowrap text-white transition hover:bg-indigo-700"
                        >
                            {{ copied ? 'Copied!' : 'Copy Link' }}
                        </button>
                    </div>
                    <p class="mt-3 text-sm text-gray-500">
                        Share this link with people who need to fill out the form. No login required.
                    </p>
                </div>

                <!-- Submissions -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            Submissions ({{ form.submissions?.length || 0 }})
                        </h3>
                    </div>

                    <div
                        v-if="!form.submissions || form.submissions.length === 0"
                        class="p-6 text-center text-gray-500"
                    >
                        No submissions yet. Share the link above to start collecting responses.
                    </div>

                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Submitted By
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                >
                                    Data
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="submission in form.submissions" :key="submission.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ submission.submitter_name }}</div>
                                    <div class="text-sm text-gray-500">{{ submission.submitter_email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                    {{ new Date(submission.submitted_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1 text-sm text-gray-900">
                                        <div v-for="(value, key) in submission.data" :key="key">
                                            <span class="font-medium">{{ customFields[key]?.label || key }}:</span>
                                            {{ Array.isArray(value) ? value.join(', ') : value }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
