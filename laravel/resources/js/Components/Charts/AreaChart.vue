<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
        // Array of { label: string, value: number }
    },
    height: {
        type: Number,
        default: 200,
    },
    color: {
        type: String,
        default: 'rgb(99, 102, 241)', // Indigo
    },
    gradientFrom: {
        type: String,
        default: 'rgba(99, 102, 241, 0.3)',
    },
    gradientTo: {
        type: String,
        default: 'rgba(99, 102, 241, 0)',
    },
    showGrid: {
        type: Boolean,
        default: true,
    },
    showLabels: {
        type: Boolean,
        default: true,
    },
    smooth: {
        type: Boolean,
        default: true,
    },
});

const width = 400;
const padding = { top: 20, right: 20, bottom: 30, left: 40 };

const chartWidth = computed(() => width - padding.left - padding.right);
const chartHeight = computed(() => props.height - padding.top - padding.bottom);

const maxValue = computed(() => Math.max(...props.data.map((d) => d.value)) * 1.1);
const minValue = computed(() => 0);

const xScale = (index) => {
    return (index / (props.data.length - 1)) * chartWidth.value;
};

const yScale = (value) => {
    return chartHeight.value - ((value - minValue.value) / (maxValue.value - minValue.value)) * chartHeight.value;
};

const points = computed(() => {
    return props.data.map((d, i) => ({
        x: xScale(i) + padding.left,
        y: yScale(d.value) + padding.top,
        ...d,
    }));
});

const linePath = computed(() => {
    if (points.value.length === 0) return '';

    if (props.smooth && points.value.length > 2) {
        // Catmull-Rom spline
        let path = `M ${points.value[0].x} ${points.value[0].y}`;

        for (let i = 0; i < points.value.length - 1; i++) {
            const p0 = points.value[Math.max(0, i - 1)];
            const p1 = points.value[i];
            const p2 = points.value[Math.min(points.value.length - 1, i + 1)];
            const p3 = points.value[Math.min(points.value.length - 1, i + 2)];

            const cp1x = p1.x + (p2.x - p0.x) / 6;
            const cp1y = p1.y + (p2.y - p0.y) / 6;
            const cp2x = p2.x - (p3.x - p1.x) / 6;
            const cp2y = p2.y - (p3.y - p1.y) / 6;

            path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`;
        }

        return path;
    }

    return points.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
});

const areaPath = computed(() => {
    if (points.value.length === 0) return '';

    const baseY = padding.top + chartHeight.value;
    return `${linePath.value} L ${points.value[points.value.length - 1].x} ${baseY} L ${points.value[0].x} ${baseY} Z`;
});

const yGridLines = computed(() => {
    const lines = [];
    const steps = 4;
    for (let i = 0; i <= steps; i++) {
        const value = minValue.value + ((maxValue.value - minValue.value) / steps) * i;
        lines.push({
            y: yScale(value) + padding.top,
            value: Math.round(value),
        });
    }
    return lines;
});
</script>

<template>
    <div class="w-full overflow-hidden">
        <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" class="h-auto w-full">
            <defs>
                <linearGradient :id="`areaGradient`" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" :stop-color="gradientFrom" />
                    <stop offset="100%" :stop-color="gradientTo" />
                </linearGradient>
            </defs>

            <!-- Grid lines -->
            <g v-if="showGrid">
                <line
                    v-for="(line, i) in yGridLines"
                    :key="i"
                    :x1="padding.left"
                    :y1="line.y"
                    :x2="width - padding.right"
                    :y2="line.y"
                    stroke="rgba(255,255,255,0.1)"
                    stroke-dasharray="4,4"
                />
                <text
                    v-for="(line, i) in yGridLines"
                    :key="`label-${i}`"
                    :x="padding.left - 8"
                    :y="line.y + 4"
                    class="fill-slate-500 text-xs"
                    text-anchor="end"
                >
                    {{ line.value }}
                </text>
            </g>

            <!-- Area fill -->
            <path :d="areaPath" :fill="`url(#areaGradient)`" class="transition-all duration-500" />

            <!-- Line -->
            <path
                :d="linePath"
                fill="none"
                :stroke="color"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="transition-all duration-500"
            />

            <!-- Points -->
            <circle
                v-for="(point, i) in points"
                :key="i"
                :cx="point.x"
                :cy="point.y"
                r="4"
                :fill="color"
                class="hover:r-6 transition-all duration-300"
            />

            <!-- X-axis labels -->
            <template v-if="showLabels">
                <text
                    v-for="(point, i) in points"
                    :key="`x-${i}`"
                    :x="point.x"
                    :y="height - 8"
                    class="fill-slate-500 text-xs"
                    text-anchor="middle"
                >
                    {{ point.label }}
                </text>
            </template>
        </svg>
    </div>
</template>
