<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'success', 'warning', 'error', 'info', 'primary'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    dot: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    const base = 'inline-flex items-center font-medium rounded-full';

    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-xs',
        lg: 'px-3 py-1.5 text-sm',
    };

    const variants = {
        default: 'bg-slate-500/20 text-slate-300',
        success: 'bg-emerald-500/20 text-emerald-400',
        warning: 'bg-amber-500/20 text-amber-400',
        error: 'bg-rose-500/20 text-rose-400',
        info: 'bg-blue-500/20 text-blue-400',
        primary: 'bg-indigo-500/20 text-indigo-400',
    };

    return [base, sizes[props.size], variants[props.variant]];
});

const dotClasses = computed(() => {
    const variants = {
        default: 'bg-slate-400',
        success: 'bg-emerald-400',
        warning: 'bg-amber-400',
        error: 'bg-rose-400',
        info: 'bg-blue-400',
        primary: 'bg-indigo-400',
    };

    return ['w-1.5 h-1.5 rounded-full mr-1.5', variants[props.variant]];
});
</script>

<template>
    <span :class="classes">
        <span v-if="dot" :class="dotClasses"></span>
        <slot />
    </span>
</template>
