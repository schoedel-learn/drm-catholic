<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    organizations: Object,
    filters: Object,
    userPermissions: {
        type: Object,
        default: () => ({ canCreate: false, canUpdate: false, canDelete: false }),
    },
    entityTypes: Array,
});

const search = ref(props.filters.search);
const entityTypeId = ref(props.filters.entity_type_id || '');

watch(
    [search, entityTypeId],
    debounce(([searchValue, entityTypeValue]) => {
        router.get(
            route('organizations.index'),
            { search: searchValue, entity_type_id: entityTypeValue },
            { preserveState: true, replace: true },
        );
    }, 300),
);

const deleteOrganization = (id) => {
    if (confirm('Are you sure you want to delete this organization?')) {
        router.delete(route('organizations.destroy', id));
    }
};
</script>

<template>
    <AppLayout title="Organizations">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Organizations</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg">
                    <div class="mb-6 flex justify-between">
                        <div class="flex space-x-2">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search organizations..."
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <select
                                v-model="entityTypeId"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Types</option>
                                <option v-for="entityType in entityTypes" :key="entityType.id" :value="entityType.id">
                                    {{ entityType.name }}
                                </option>
                            </select>
                        </div>

                        <Link
                            v-if="userPermissions.canCreate"
                            :href="route('organizations.create')"
                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                        >
                            Add Organization
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
                                        Type
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
                                <tr v-for="org in organizations.data" :key="org.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ org.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ org.address?.city }}, {{ org.address?.state }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 capitalize">
                                        {{ org.entity_type?.name || 'Unspecified' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ org.email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ org.phone }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <Link
                                            v-if="userPermissions.canUpdate"
                                            :href="route('organizations.edit', org.id)"
                                            class="mr-4 text-indigo-600 hover:text-indigo-900"
                                            >Edit</Link
                                        >
                                        <button
                                            v-if="userPermissions.canDelete"
                                            @click="deleteOrganization(org.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
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
