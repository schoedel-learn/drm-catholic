<script setup>
import { computed } from 'vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
    },
    color: {
        type: String,
        default: 'indigo',
        validator: (value) => ['indigo', 'purple', 'emerald', 'amber', 'rose', 'blue', 'slate'].includes(value),
    },
});

const sizeClasses = computed(() => {
    const sizes = {
        xs: 'w-6 h-6 text-xs',
        sm: 'w-8 h-8 text-sm',
        md: 'w-10 h-10 text-sm',
        lg: 'w-12 h-12 text-base',
        xl: 'w-16 h-16 text-lg',
    };
    return sizes[props.size];
});

const colorClasses = computed(() => {
    const colors = {
        indigo: 'bg-indigo-500/20 text-indigo-400',
        purple: 'bg-purple-500/20 text-purple-400',
        emerald: 'bg-emerald-500/20 text-emerald-400',
        amber: 'bg-amber-500/20 text-amber-400',
        rose: 'bg-rose-500/20 text-rose-400',
        blue: 'bg-blue-500/20 text-blue-400',
        slate: 'bg-slate-500/20 text-slate-400',
    };
    return colors[props.color];
});

const initials = computed(() => {
    if (!props.name) return '?';
    return props.name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});
</script>

<template>
    <div
        :class="[
            'flex items-center justify-center overflow-hidden rounded-full font-semibold',
            sizeClasses,
            !src ? colorClasses : '',
        ]"
    >
        <img v-if="src" :src="src" :alt="name" class="h-full w-full object-cover" />
        <span v-else>{{ initials }}</span>
    </div>
</template>
