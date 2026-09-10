<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    change: {
        type: Number,
        default: null,
    },
    changeLabel: {
        type: String,
        default: 'vs last period',
    },
    icon: {
        type: String,
        default: null,
    },
    iconColor: {
        type: String,
        default: 'indigo',
        validator: (value) => ['indigo', 'purple', 'emerald', 'amber', 'rose', 'blue'].includes(value),
    },
});

const iconBgClass = computed(() => {
    const colors = {
        indigo: 'from-indigo-500 to-purple-600 shadow-indigo-500/30',
        purple: 'from-purple-500 to-pink-600 shadow-purple-500/30',
        emerald: 'from-emerald-500 to-teal-600 shadow-emerald-500/30',
        amber: 'from-amber-500 to-orange-600 shadow-amber-500/30',
        rose: 'from-rose-500 to-red-600 shadow-rose-500/30',
        blue: 'from-blue-500 to-cyan-600 shadow-blue-500/30',
    };
    return colors[props.iconColor];
});

const changeClass = computed(() => {
    if (props.change === null) return '';
    return props.change >= 0 ? 'text-emerald-400' : 'text-rose-400';
});

const changePrefix = computed(() => {
    if (props.change === null) return '';
    return props.change >= 0 ? '+' : '';
});
</script>

<template>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="mb-1 text-sm font-medium text-slate-400">{{ title }}</p>
                <p class="text-3xl font-bold text-white">{{ value }}</p>

                <div v-if="change !== null" class="mt-2 flex items-center gap-1.5">
                    <span :class="['text-sm font-medium', changeClass]"> {{ changePrefix }}{{ change }}% </span>
                    <span class="text-xs text-slate-500">{{ changeLabel }}</span>
                </div>
            </div>

            <div
                v-if="icon || $slots.icon"
                :class="[
                    'flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg',
                    iconBgClass,
                ]"
            >
                <slot name="icon">
                    <span class="text-lg text-white">{{ icon }}</span>
                </slot>
            </div>
        </div>
    </div>
</template>
