<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Each column: { key: string, label: string, sortable?: boolean, class?: string }
    },
    data: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyText: {
        type: String,
        default: 'No data available',
    },
    striped: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['row-click', 'sort']);

const sortKey = ref(null);
const sortOrder = ref('asc');

const handleSort = (column) => {
    if (!column.sortable) return;
    
    if (sortKey.value === column.key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortOrder.value = 'asc';
    }
    
    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const sortedData = computed(() => {
    if (!sortKey.value) return props.data;
    
    return [...props.data].sort((a, b) => {
        const aVal = a[sortKey.value];
        const bVal = b[sortKey.value];
        
        if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
        return 0;
    });
});

const rowClick = (row, index) => {
    emit('row-click', row, index);
};
</script>

<template>
    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:text-slate-200 transition-colors select-none' : '',
                                column.class || ''
                            ]"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center gap-2">
                                {{ column.label }}
                                <span v-if="column.sortable" class="text-slate-500">
                                    <svg 
                                        v-if="sortKey === column.key"
                                        :class="['w-4 h-4 transition-transform', sortOrder === 'desc' ? 'rotate-180' : '']" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg v-else class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <tr v-if="loading">
                        <td :colspan="columns.length" class="px-6 py-16 text-center">
                            <svg class="animate-spin h-8 w-8 text-indigo-400 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                        </td>
                    </tr>
                    
                    <!-- Empty State -->
                    <tr v-else-if="data.length === 0">
                        <td :colspan="columns.length" class="px-6 py-16 text-center text-slate-400">
                            {{ emptyText }}
                        </td>
                    </tr>
                    
                    <!-- Data Rows -->
                    <tr
                        v-else
                        v-for="(row, index) in sortedData"
                        :key="index"
                        :class="[
                            'border-b border-white/5 transition-colors hover:bg-white/5 cursor-pointer',
                            striped && index % 2 === 1 ? 'bg-white/[0.02]' : ''
                        ]"
                        @click="rowClick(row, index)"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="['px-6 py-4 text-sm text-slate-300', column.class || '']"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
