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
    contact: Object,
    customFields: Array,
});

const form = useForm({
    _method: 'PUT',
    first_name: props.contact.first_name,
    last_name: props.contact.last_name,
    email: props.contact.email,
    phone: props.contact.phone,
    title: props.contact.title,
    role: props.contact.role,
    custom_data: props.contact.custom_data || {},
});

const submit = () => {
    form.post(route('contacts.update', props.contact.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Contact">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Edit Contact</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title> Contact Details </template>

                    <template #description> Edit contact information. </template>

                    <template #form>
                        <!-- First Name -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="first_name" value="First Name" />
                            <TextInput
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.first_name" class="mt-2" />
                        </div>

                        <!-- Last Name -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="last_name" value="Last Name" />
                            <TextInput
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.last_name" class="mt-2" />
                        </div>

                        <!-- Title -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="title" value="Title" />
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="role" value="Role" />
                            <TextInput id="role" v-model="form.role" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.role" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="phone" value="Phone" />
                            <TextInput id="phone" v-model="form.phone" type="tel" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>

                        <!-- Custom Fields -->
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                            <div v-else-if="field.type === 'currency'" class="relative mt-1 rounded-md shadow-sm">
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3"> Saved. </ActionMessage>

                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
