<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ActionMessage from '@/Components/ActionMessage.vue';

const props = defineProps({
    customFields: Array,
    entityTypes: Array,
});

const form = useForm({
    name: '',
    entity_type_id: props.entityTypes?.[0]?.id || '',
    email: '',
    phone: '',
    website: '',
    address: {
        street: '',
        city: '',
        state: '',
        zip: '',
    },
    custom_data: {},
});

const submit = () => {
    form.post(route('organizations.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AppLayout title="Add Organization">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add New Organization
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Organization Details
                    </template>

                    <template #description>
                        Add a new organization to your diocese.
                    </template>

                    <template #form>
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="name" value="Organization Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Entity Type -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="entity_type_id" value="Type" />
                            <select
                                id="entity_type_id"
                                v-model="form.entity_type_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>Select type</option>
                                <option v-for="entityType in entityTypes" :key="entityType.id" :value="entityType.id">
                                    {{ entityType.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.entity_type_id" class="mt-2" />
                        </div>

                        <!-- Contact Info -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="phone" value="Phone" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="website" value="Website" />
                            <TextInput
                                id="website"
                                v-model="form.website"
                                type="url"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.website" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="col-span-6">
                            <hr class="my-4 border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Address</h3>
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="street" value="Street Address" />
                            <TextInput
                                id="street"
                                v-model="form.address.street"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors['address.street']" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <InputLabel for="city" value="City" />
                            <TextInput
                                id="city"
                                v-model="form.address.city"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors['address.city']" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <InputLabel for="state" value="State" />
                            <TextInput
                                id="state"
                                v-model="form.address.state"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors['address.state']" class="mt-2" />
                        </div>

                         <div class="col-span-6 sm:col-span-2">
                            <InputLabel for="zip" value="ZIP Code" />
                            <TextInput
                                id="zip"
                                v-model="form.address.zip"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors['address.zip']" class="mt-2" />
                        </div>

                        <!-- Custom Fields -->
                        <div v-if="customFields.length > 0" class="col-span-6">
                            <hr class="my-4 border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                        </div>

                        <div v-for="field in customFields" :key="field.key" class="col-span-6 sm:col-span-3">
                            <InputLabel :for="field.key" :value="field.label" />
                            
                            <!-- Text Input -->
                            <TextInput
                                v-if="field.type === 'text'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="text"
                                class="mt-1 block w-full"
                                :required="field.required"
                            />

                            <!-- Textarea -->
                            <textarea
                                v-else-if="field.type === 'textarea'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                                :required="field.required"
                            ></textarea>

                            <!-- Number Input -->
                            <TextInput
                                v-else-if="field.type === 'number'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="number"
                                class="mt-1 block w-full"
                                :required="field.required"
                            />

                            <!-- Date Input -->
                            <TextInput
                                v-else-if="field.type === 'date'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="date"
                                class="mt-1 block w-full"
                                :required="field.required"
                            />

                            <!-- Email Input -->
                            <TextInput
                                v-else-if="field.type === 'email'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="email"
                                class="mt-1 block w-full"
                                :required="field.required"
                            />

                            <!-- Phone Input -->
                            <TextInput
                                v-else-if="field.type === 'phone'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="tel"
                                class="mt-1 block w-full"
                                :required="field.required"
                            />

                            <!-- URL Input -->
                            <TextInput
                                v-else-if="field.type === 'url'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://"
                                :required="field.required"
                            />

                            <!-- Currency Input -->
                            <div v-else-if="field.type === 'currency'" class="mt-1 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input
                                    :id="field.key"
                                    v-model="form.custom_data[field.key]"
                                    type="number"
                                    step="0.01"
                                    class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500"
                                    :required="field.required"
                                />
                            </div>

                            <!-- Checkbox -->
                            <div v-else-if="field.type === 'checkbox'" class="mt-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :id="field.key"
                                        v-model="form.custom_data[field.key]"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Yes</span>
                                </label>
                            </div>

                            <!-- Select Dropdown -->
                            <select
                                v-else-if="field.type === 'select'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                :required="field.required"
                            >
                                <option value="">Select option</option>
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>

                            <!-- Multi-Select -->
                            <select
                                v-else-if="field.type === 'multiselect'"
                                :id="field.key"
                                v-model="form.custom_data[field.key]"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                multiple
                                :required="field.required"
                            >
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>

                            <InputError :message="form.errors[`custom_data.${field.key}`]" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                            Saved.
                        </ActionMessage>

                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
