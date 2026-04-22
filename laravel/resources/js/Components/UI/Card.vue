<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'glass',
        validator: (value) => ['glass', 'solid', 'outline'].includes(value),
    },
    padding: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'none'].includes(value),
    },
    hoverable: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    const base = 'rounded-2xl';
    
    const paddings = {
        none: '',
        sm: 'p-4',
        md: 'p-6',
        lg: 'p-8',
    };
    
    const variants = {
        glass: 'bg-white/5 backdrop-blur-sm border border-white/10',
        solid: 'bg-slate-800 border border-slate-700',
        outline: 'bg-transparent border border-white/20',
    };
    
    const hover = props.hoverable 
        ? 'transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/50 cursor-pointer' 
        : '';
    
    return [base, paddings[props.padding], variants[props.variant], hover];
});
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
