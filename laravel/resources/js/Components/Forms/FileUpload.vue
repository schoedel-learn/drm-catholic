<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: null,
    },
    accept: {
        type: String,
        default: 'image/*,application/pdf,.doc,.docx',
    },
    multiple: {
        type: Boolean,
        default: true,
    },
    maxSize: {
        type: Number,
        default: 10, // MB
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const isDragging = ref(false);
const fileInput = ref(null);

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (file) => {
    if (file.type.startsWith('image/')) return 'image';
    if (file.type === 'application/pdf') return 'pdf';
    if (file.type.includes('word') || file.name.endsWith('.doc') || file.name.endsWith('.docx')) return 'doc';
    return 'file';
};

const handleDrop = (e) => {
    isDragging.value = false;
    if (props.disabled) return;

    const files = Array.from(e.dataTransfer.files);
    addFiles(files);
};

const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    addFiles(files);
    e.target.value = ''; // Reset input
};

const addFiles = (files) => {
    const maxBytes = props.maxSize * 1024 * 1024;
    const validFiles = files.filter((f) => f.size <= maxBytes);

    const newFiles = props.multiple ? [...props.modelValue, ...validFiles] : validFiles.slice(0, 1);

    emit('update:modelValue', newFiles);
};

const removeFile = (index) => {
    const newFiles = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', newFiles);
};

const openFilePicker = () => {
    if (!props.disabled) {
        fileInput.value.click();
    }
};
</script>

<template>
    <div>
        <!-- Label -->
        <label v-if="label" class="mb-2 block text-sm font-medium text-slate-300">
            {{ label }}
        </label>

        <!-- Drop Zone -->
        <div
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="handleDrop"
            @click="openFilePicker"
            :class="[
                'relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-10 transition-all duration-200',
                isDragging
                    ? 'border-indigo-500 bg-indigo-500/10'
                    : error
                      ? 'border-rose-500/50 bg-rose-500/5'
                      : 'border-white/20 bg-white/5 hover:border-white/40',
                disabled ? 'cursor-not-allowed opacity-50' : '',
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                :multiple="multiple"
                :disabled="disabled"
                @change="handleFileSelect"
                class="sr-only"
            />

            <svg class="mb-4 h-12 w-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                />
            </svg>

            <p class="mb-1 text-sm text-slate-300">
                <span class="font-semibold text-indigo-400">Click to upload</span>
                or drag and drop
            </p>
            <p class="text-xs text-slate-500">Max size: {{ maxSize }}MB</p>
        </div>

        <!-- Error -->
        <p v-if="error" class="mt-2 text-sm text-rose-400">{{ error }}</p>

        <!-- File List -->
        <ul v-if="modelValue.length > 0" class="mt-4 space-y-2">
            <li
                v-for="(file, index) in modelValue"
                :key="index"
                class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-3"
            >
                <!-- Icon -->
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-500/20">
                    <svg
                        v-if="getFileIcon(file) === 'image'"
                        class="h-5 w-5 text-indigo-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <svg
                        v-else-if="getFileIcon(file) === 'pdf'"
                        class="h-5 w-5 text-rose-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>
                    <svg v-else class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </div>

                <!-- File info -->
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ file.name }}</p>
                    <p class="text-xs text-slate-500">{{ formatSize(file.size) }}</p>
                </div>

                <!-- Remove button -->
                <button
                    type="button"
                    @click.stop="removeFile(index)"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-rose-500/10 hover:text-rose-400"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</template>
