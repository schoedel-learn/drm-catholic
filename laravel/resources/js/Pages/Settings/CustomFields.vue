<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
    customFields: Array,
});

const form = useForm({
    label: '',
    entity_type: 'contact',
    type: 'text',
    options_string: '', // "Option 1, Option 2"
    required: false,
});

const submit = () => {
    // Parse options
    let options = null;
    if ((form.type === 'select' || form.type === 'multiselect') && form.options_string) {
        options = form.options_string
            .split(',')
            .map((s) => s.trim())
            .filter((s) => s);
    }

    form.transform((data) => ({
        ...data,
        options: options,
    })).post(route('custom-fields.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const deleteField = (id) => {
    if (confirm('Are you sure? Data stored in this field for existing contacts will be lost.')) {
        router.delete(route('custom-fields.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Custom Fields">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Custom Fields</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Create New Field Form -->
                <FormSection @submitted="submit">
                    <template #title> Add Custom Field </template>

                    <template #description> Define a new field to collect custom data for your contacts. </template>

                    <template #form>
                        <!-- Label -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="label" value="Label" />
                            <TextInput
                                id="label"
                                v-model="form.label"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="e.g. Ordination Date"
                            />
                            <InputError :message="form.errors.label" class="mt-2" />
                        </div>

                        <!-- Entity Type -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="entity_type" value="Applies To" />
                            <select
                                id="entity_type"
                                v-model="form.entity_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="contact">Contact</option>
                                <option value="organization">Organization</option>
                            </select>
                            <InputError :message="form.errors.entity_type" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="type" value="Type" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="text">Text</option>
                                <option value="textarea">Textarea (Long Text)</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="url">URL</option>
                                <option value="currency">Currency</option>
                                <option value="checkbox">Checkbox (Yes/No)</option>
                                <option value="select">Select (Dropdown)</option>
                                <option value="multiselect">Multi-Select</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <!-- Options (if Select or Multiselect) -->
                        <div v-if="form.type === 'select' || form.type === 'multiselect'" class="col-span-6">
                            <InputLabel for="options" value="Options (comma separated)" />
                            <TextInput
                                id="options"
                                v-model="form.options_string"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Option 1, Option 2, Option 3"
                            />
                            <InputError :message="form.errors.options" class="mt-2" />
                        </div>

                        <!-- Required -->
                        <div class="col-span-6">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.required" name="required" />
                                <span class="ml-2 text-sm text-gray-600">Required field</span>
                            </label>
                        </div>
                    </template>

                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3"> Created. </ActionMessage>

                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create Field
                        </PrimaryButton>
                    </template>
                </FormSection>

                <!-- List Existing Fields -->
                <div class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Existing Fields</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Label
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Key
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Entity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Required
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="field in customFields" :key="field.id">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                                        {{ field.label }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{{ field.key }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 capitalize">
                                        {{ field.entity_type }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{{ field.type }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        <span v-if="field.required" class="font-bold text-green-600">Yes</span>
                                        <span v-else class="text-gray-400">No</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <DangerButton @click="deleteField(field.id)"> Delete </DangerButton>
                                    </td>
                                </tr>
                                <tr v-if="customFields.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No custom fields defined.
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
