<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    organizations: Object,
    filters: Object,
    userPermissions: {
        type: Object,
        default: () => ({ canCreate: false, canUpdate: false, canDelete: false }),
    },
});

const search = ref(props.filters.search);
const type = ref(props.filters.type || '');

watch([search, type], debounce(([searchValue, typeValue]) => {
    router.get(route('organizations.index'), { search: searchValue, type: typeValue }, { preserveState: true, replace: true });
}, 300));

const deleteOrganization = (id) => {
    if (confirm('Are you sure you want to delete this organization?')) {
        router.delete(route('organizations.destroy', id));
    }
};
</script>

<template>
    <AppLayout title="Organizations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Organizations
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <div class="flex space-x-2">
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Search organizations..." 
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                            <select 
                                v-model="type"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Types</option>
                                <option value="parish">Parish</option>
                                <option value="school">School</option>
                                <option value="office">Office</option>
                                <option value="apostolate">Apostolate</option>
                            </select>
                        </div>

                        <Link 
                            v-if="userPermissions.canCreate"
                            :href="route('organizations.create')" 
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Add Organization
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="org in organizations.data" :key="org.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ org.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ org.address?.city }}, {{ org.address?.state }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                        {{ org.type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ org.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ org.phone }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="userPermissions.canUpdate" :href="route('organizations.edit', org.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                        <button v-if="userPermissions.canDelete" @click="deleteOrganization(org.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="organizations.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No organizations found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
