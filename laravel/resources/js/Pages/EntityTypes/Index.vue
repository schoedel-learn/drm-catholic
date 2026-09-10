<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button, Card, Badge } from '@/Components/UI';

defineProps({
    entityTypes: Array,
    baseEntityOptions: Array,
});

const showCreateModal = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    base_entity: 'contact',
    icon: '',
    color: '',
    description: '',
});

const editForm = useForm({
    name: '',
    icon: '',
    color: '',
    description: '',
    is_active: true,
});

const openCreate = () => {
    form.reset();
    showCreateModal.value = true;
};

const openEdit = (type) => {
    editingType.value = type;
    editForm.name = type.name;
    editForm.icon = type.icon || '';
    editForm.color = type.color || '';
    editForm.description = type.description || '';
    editForm.is_active = type.is_active;
};

const submitCreate = () => {
    form.post(route('entity-types.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const submitEdit = () => {
    editForm.put(route('entity-types.update', editingType.value.id), {
        onSuccess: () => {
            editingType.value = null;
        },
    });
};

const deleteType = (type) => {
    if (confirm(`Delete "${type.name}"? This cannot be undone.`)) {
        useForm({}).delete(route('entity-types.destroy', type.id));
    }
};

const colors = ['slate', 'blue', 'green', 'emerald', 'purple', 'rose', 'amber', 'cyan'];
const getColorClass = (color) => {
    const map = {
        slate: 'bg-slate-500/20 text-slate-400',
        blue: 'bg-blue-500/20 text-blue-400',
        green: 'bg-green-500/20 text-green-400',
        emerald: 'bg-emerald-500/20 text-emerald-400',
        purple: 'bg-purple-500/20 text-purple-400',
        rose: 'bg-rose-500/20 text-rose-400',
        amber: 'bg-amber-500/20 text-amber-400',
        cyan: 'bg-cyan-500/20 text-cyan-400',
    };
    return map[color] || map.slate;
};
</script>

<template>
    <DashboardLayout title="Entity Types">
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold text-white">Entity Types</h1>
                <Button @click="openCreate" variant="primary">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Type
                </Button>
            </div>
        </template>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="type in entityTypes" :key="type.id" variant="glass" class="group relative p-4">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div :class="['flex h-12 w-12 items-center justify-center rounded-xl', getColorClass(type.color)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                v-if="type.icon === 'user'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                            <path
                                v-else-if="type.icon === 'building' || type.icon === 'building-2'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                            <path
                                v-else-if="type.icon === 'church'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 2L8 6H4v14h16V6h-4L12 2zm0 0v4m-4 8v4m8-4v4"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center gap-2">
                            <h3 class="font-medium text-white">{{ type.name }}</h3>
                            <Badge v-if="type.is_system" variant="secondary" size="sm">System</Badge>
                        </div>
                        <p class="text-xs text-slate-400 capitalize">{{ type.base_entity }} type</p>
                        <p v-if="type.description" class="mt-2 line-clamp-2 text-sm text-slate-500">
                            {{ type.description }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    v-if="!type.is_system"
                    class="absolute top-2 right-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                >
                    <button
                        @click="openEdit(type)"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                    </button>
                    <button
                        @click="deleteType(type)"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-500/10 hover:text-rose-400"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                    </button>
                </div>
            </Card>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <Card variant="solid" class="w-full max-w-md p-6">
                    <h2 class="mb-4 text-lg font-semibold text-white">Create Entity Type</h2>

                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-white focus:border-indigo-500 focus:outline-none"
                                placeholder="e.g. Religious Sister"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Base Type</label>
                            <select
                                v-model="form.base_entity"
                                class="w-full rounded-lg border border-white/10 bg-slate-700 px-3 py-2 text-white focus:border-indigo-500 focus:outline-none"
                            >
                                <option v-for="opt in baseEntityOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="c in colors"
                                    :key="c"
                                    type="button"
                                    @click="form.color = c"
                                    :class="[
                                        'h-8 w-8 rounded-lg transition-all',
                                        getColorClass(c),
                                        form.color === c ? 'ring-2 ring-white' : '',
                                    ]"
                                ></button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full resize-none rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-white focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Button @click="showCreateModal = false" variant="ghost">Cancel</Button>
                            <Button type="submit" :loading="form.processing" variant="primary">Create</Button>
                        </div>
                    </form>
                </Card>
            </div>
        </Teleport>

        <!-- Edit Modal -->
        <Teleport to="body">
            <div
                v-if="editingType"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <Card variant="solid" class="w-full max-w-md p-6">
                    <h2 class="mb-4 text-lg font-semibold text-white">Edit {{ editingType.name }}</h2>

                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Name</label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="c in colors"
                                    :key="c"
                                    type="button"
                                    @click="editForm.color = c"
                                    :class="[
                                        'h-8 w-8 rounded-lg transition-all',
                                        getColorClass(c),
                                        editForm.color === c ? 'ring-2 ring-white' : '',
                                    ]"
                                ></button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-300">Description</label>
                            <textarea
                                v-model="editForm.description"
                                rows="2"
                                class="w-full resize-none rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-white focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Button @click="editingType = null" variant="ghost">Cancel</Button>
                            <Button type="submit" :loading="editForm.processing" variant="primary">Save</Button>
                        </div>
                    </form>
                </Card>
            </div>
        </Teleport>
    </DashboardLayout>
</template>
