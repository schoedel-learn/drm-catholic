<script setup>
import { ref, onMounted, watch, computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
        // Array of { label: string, value: number, color?: string }
    },
    showLabels: {
        type: Boolean,
        default: true,
    },
    size: {
        type: Number,
        default: 200,
    },
    strokeWidth: {
        type: Number,
        default: 30,
    },
    centerLabel: {
        type: String,
        default: null,
    },
    centerValue: {
        type: [String, Number],
        default: null,
    },
});

const defaultColors = [
    'rgb(99, 102, 241)',   // Indigo
    'rgb(168, 85, 247)',   // Purple
    'rgb(16, 185, 129)',   // Emerald
    'rgb(245, 158, 11)',   // Amber
    'rgb(244, 63, 94)',    // Rose
    'rgb(59, 130, 246)',   // Blue
    'rgb(236, 72, 153)',   // Pink
];

const total = computed(() => props.data.reduce((sum, item) => sum + item.value, 0));

const segments = computed(() => {
    let currentOffset = 0;
    return props.data.map((item, index) => {
        const percentage = (item.value / total.value) * 100;
        const segment = {
            ...item,
            color: item.color || defaultColors[index % defaultColors.length],
            percentage,
            offset: currentOffset,
        };
        currentOffset += percentage;
        return segment;
    });
});

const radius = computed(() => (props.size - props.strokeWidth) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
</script>

<template>
    <div class="flex items-center gap-8">
        <!-- Chart -->
        <div class="relative" :style="{ width: `${size}px`, height: `${size}px` }">
            <svg :width="size" :height="size" class="-rotate-90">
                <!-- Background circle -->
                <circle
                    :cx="size / 2"
                    :cy="size / 2"
                    :r="radius"
                    fill="none"
                    stroke="rgba(255,255,255,0.1)"
                    :stroke-width="strokeWidth"
                />
                
                <!-- Segments -->
                <circle
                    v-for="(segment, index) in segments"
                    :key="index"
                    :cx="size / 2"
                    :cy="size / 2"
                    :r="radius"
                    fill="none"
                    :stroke="segment.color"
                    :stroke-width="strokeWidth"
                    :stroke-dasharray="`${(segment.percentage / 100) * circumference} ${circumference}`"
                    :stroke-dashoffset="`${-(segment.offset / 100) * circumference}`"
                    class="transition-all duration-500"
                />
            </svg>
            
            <!-- Center content -->
            <div v-if="centerLabel || centerValue" class="absolute inset-0 flex flex-col items-center justify-center">
                <span v-if="centerValue" class="text-2xl font-bold text-white">{{ centerValue }}</span>
                <span v-if="centerLabel" class="text-sm text-slate-400">{{ centerLabel }}</span>
            </div>
        </div>
        
        <!-- Legend -->
        <div v-if="showLabels" class="space-y-3">
            <div 
                v-for="(segment, index) in segments" 
                :key="index"
                class="flex items-center gap-3"
            >
                <div 
                    class="w-3 h-3 rounded-full shrink-0"
                    :style="{ backgroundColor: segment.color }"
                ></div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white">{{ segment.label }}</div>
                    <div class="text-xs text-slate-500">{{ segment.percentage.toFixed(1) }}%</div>
                </div>
                <div class="text-sm font-semibold text-slate-300">{{ segment.value }}</div>
            </div>
        </div>
    </div>
</template>
