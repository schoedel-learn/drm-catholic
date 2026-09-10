<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    contacts: Object,
    filters: Object,
    userPermissions: {
        type: Object,
        default: () => ({ canCreate: false, canUpdate: false, canDelete: false }),
    },
});

const search = ref(props.filters.search);

watch(
    search,
    debounce((value) => {
        router.get(route('contacts.index'), { search: value }, { preserveState: true, replace: true });
    }, 300),
);

const deleteContact = (id) => {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(route('contacts.destroy', id));
    }
};
</script>

<template>
    <AppLayout title="Contacts">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Contacts</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg">
                    <div class="mb-6 flex justify-between">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search contacts..."
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <Link
                            v-if="userPermissions.canCreate"
                            :href="route('contacts.create')"
                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                        >
                            Add Contact
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Name
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Role
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Email
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Phone
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="contact in contacts.data" :key="contact.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ contact.last_name }}, {{ contact.first_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ contact.title }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ contact.role }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ contact.email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ contact.phone }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <Link
                                            v-if="userPermissions.canUpdate"
                                            :href="route('contacts.edit', contact.id)"
                                            class="mr-4 text-indigo-600 hover:text-indigo-900"
                                            >Edit</Link
                                        >
                                        <button
                                            v-if="userPermissions.canDelete"
                                            @click="deleteContact(contact.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="contacts.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No contacts found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (Basic) -->
                    <div class="mt-4 flex justify-between" v-if="contacts.links && contacts.last_page > 1">
                        <!-- Implement proper pagination component later if needed -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
