<script setup>
import { ref, computed, watch } from 'vue';
import FormField from '@/Components/FormBuilder/FormField.vue';
import FieldPalette from '@/Components/FormBuilder/FieldPalette.vue';
import FieldEditor from '@/Components/FormBuilder/FieldEditor.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    entityType: {
        type: String,
        default: 'contact',
    },
    customFields: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const fields = ref([...props.modelValue]);
const selectedField = ref(null);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);

// Sync with parent
watch(
    fields,
    (newFields) => {
        emit('update:modelValue', newFields);
    },
    { deep: true },
);

watch(
    () => props.modelValue,
    (newValue) => {
        if (JSON.stringify(newValue) !== JSON.stringify(fields.value)) {
            fields.value = [...newValue];
        }
    },
    { deep: true },
);

// Generate unique key for new field
const generateKey = (type) => {
    const base = type.toLowerCase().replace(/\s+/g, '_');
    const existing = fields.value.filter((f) => f.key.startsWith(base));
    return `${base}_${existing.length + 1}`;
};

// Add field from palette
const addField = (fieldType) => {
    const newField = {
        id: crypto.randomUUID(),
        type: fieldType.type,
        key: generateKey(fieldType.type),
        label: fieldType.label,
        placeholder: '',
        help_text: '',
        options:
            fieldType.type === 'select' || fieldType.type === 'radio' || fieldType.type === 'checkbox_group'
                ? [{ value: 'option_1', label: 'Option 1' }]
                : null,
        validation: { required: false },
        conditional: null,
        crm_mapping: null,
        order: fields.value.length,
    };

    fields.value.push(newField);
    selectedField.value = newField;
};

// Remove field
const removeField = (index) => {
    if (selectedField.value?.id === fields.value[index].id) {
        selectedField.value = null;
    }
    fields.value.splice(index, 1);
    updateOrder();
};

// Duplicate field
const duplicateField = (index) => {
    const original = fields.value[index];
    const duplicate = {
        ...JSON.parse(JSON.stringify(original)),
        id: crypto.randomUUID(),
        key: generateKey(original.type),
    };
    fields.value.splice(index + 1, 0, duplicate);
    updateOrder();
};

// Select field for editing
const selectField = (field) => {
    selectedField.value = field;
};

// Update field from editor
const updateField = (updatedField) => {
    const index = fields.value.findIndex((f) => f.id === updatedField.id);
    if (index !== -1) {
        fields.value[index] = updatedField;
    }
};

// Drag and drop handlers
const onDragStart = (index) => {
    draggedIndex.value = index;
};

const onDragOver = (index) => {
    if (draggedIndex.value !== null && draggedIndex.value !== index) {
        dragOverIndex.value = index;
    }
};

const onDragEnd = () => {
    if (draggedIndex.value !== null && dragOverIndex.value !== null) {
        const item = fields.value[draggedIndex.value];
        fields.value.splice(draggedIndex.value, 1);
        fields.value.splice(dragOverIndex.value, 0, item);
        updateOrder();
    }
    draggedIndex.value = null;
    dragOverIndex.value = null;
};

const updateOrder = () => {
    fields.value.forEach((field, index) => {
        field.order = index;
    });
};

// CRM mapping options
const crmMappingOptions = computed(() => {
    const options = [];

    // Standard contact fields
    if (props.entityType === 'contact') {
        options.push(
            { value: 'contact.email', label: 'Contact Email' },
            { value: 'contact.phone', label: 'Contact Phone' },
            { value: 'contact.first_name', label: 'First Name' },
            { value: 'contact.last_name', label: 'Last Name' },
            { value: 'contact.title', label: 'Title' },
        );
    } else {
        options.push(
            { value: 'organization.name', label: 'Organization Name' },
            { value: 'organization.email', label: 'Organization Email' },
            { value: 'organization.phone', label: 'Organization Phone' },
        );
    }

    // Custom fields
    props.customFields.forEach((cf) => {
        options.push({
            value: `custom.${cf.key}`,
            label: `Custom: ${cf.label}`,
        });
    });

    return options;
});
</script>

<template>
    <div class="flex h-[calc(100vh-200px)] min-h-[600px] gap-6">
        <!-- Field Palette (left) -->
        <div class="w-64 shrink-0">
            <FieldPalette @add-field="addField" />
        </div>

        <!-- Form Canvas (center) -->
        <div class="flex-1 overflow-y-auto rounded-2xl border border-white/10 bg-slate-800/50 p-6">
            <div v-if="fields.length === 0" class="flex h-full flex-col items-center justify-center text-slate-500">
                <svg class="mb-4 h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <p class="text-lg font-medium">No fields yet</p>
                <p class="text-sm">Drag fields from the left panel or click to add</p>
            </div>

            <TransitionGroup v-else name="field-list" tag="div" class="space-y-3">
                <FormField
                    v-for="(field, index) in fields"
                    :key="field.id"
                    :field="field"
                    :index="index"
                    :is-selected="selectedField?.id === field.id"
                    :is-dragging="draggedIndex === index"
                    :is-drag-over="dragOverIndex === index"
                    @select="selectField(field)"
                    @remove="removeField(index)"
                    @duplicate="duplicateField(index)"
                    @dragstart="onDragStart(index)"
                    @dragover="onDragOver(index)"
                    @dragend="onDragEnd"
                />
            </TransitionGroup>
        </div>

        <!-- Field Editor (right) -->
        <div class="w-80 shrink-0">
            <FieldEditor
                v-if="selectedField"
                :field="selectedField"
                :fields="fields"
                :crm-options="crmMappingOptions"
                @update="updateField"
                @close="selectedField = null"
            />
            <div
                v-else
                class="flex h-full flex-col items-center justify-center rounded-2xl border border-white/10 bg-slate-800/30 p-6 text-slate-500"
            >
                <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                    />
                </svg>
                <p class="text-sm">Select a field to edit</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.field-list-move {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.field-list-enter-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.field-list-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: absolute;
}

.field-list-enter-from {
    opacity: 0;
    transform: translateX(-30px);
}

.field-list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
