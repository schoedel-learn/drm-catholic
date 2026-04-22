<script setup>
import { Link, router } from '@inertiajs/vue3';
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
    setTimeout(() => copied.value = false, 2000);
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
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ form.name }}
                </h2>
                <div class="flex gap-2">
                    <button
                        @click="toggleActive"
                        class="px-4 py-2 rounded-md text-sm font-medium"
                        :class="form.is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                    >
                        {{ form.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        @click="deleteForm"
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-md text-sm font-medium hover:bg-red-200"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Form Link Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Public Form Link</h3>
                    <div class="flex items-center gap-4">
                        <code class="flex-1 bg-gray-100 px-4 py-3 rounded-lg text-sm break-all">
                            {{ `${window.location.origin}/forms/${form.token}` }}
                        </code>
                        <button
                            @click="copyLink"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition whitespace-nowrap"
                        >
                            {{ copied ? 'Copied!' : 'Copy Link' }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        Share this link with people who need to fill out the form. No login required.
                    </p>
                </div>

                <!-- Submissions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Submissions ({{ form.submissions?.length || 0 }})</h3>
                    </div>

                    <div v-if="!form.submissions || form.submissions.length === 0" class="p-6 text-center text-gray-500">
                        No submissions yet. Share the link above to start collecting responses.
                    </div>

                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="submission in form.submissions" :key="submission.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ submission.submitter_name }}</div>
                                    <div class="text-sm text-gray-500">{{ submission.submitter_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(submission.submitted_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 space-y-1">
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
