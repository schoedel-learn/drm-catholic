<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Select a date',
    },
    min: {
        type: String,
        default: null,
    },
    max: {
        type: String,
        default: null,
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

const isOpen = ref(false);
const currentMonth = ref(new Date());

const selectedDate = computed({
    get: () => (props.modelValue ? new Date(props.modelValue) : null),
    set: (value) => {
        emit('update:modelValue', value ? formatDate(value) : '');
    },
});

const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const displayValue = computed(() => {
    if (!selectedDate.value) return '';
    return selectedDate.value.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const monthName = computed(() => {
    return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const daysOfWeek = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startPadding = firstDay.getDay();

    const days = [];

    // Previous month padding
    const prevMonth = new Date(year, month, 0);
    for (let i = startPadding - 1; i >= 0; i--) {
        days.push({
            date: new Date(year, month - 1, prevMonth.getDate() - i),
            isCurrentMonth: false,
        });
    }

    // Current month
    for (let d = 1; d <= lastDay.getDate(); d++) {
        days.push({
            date: new Date(year, month, d),
            isCurrentMonth: true,
        });
    }

    // Next month padding
    const remaining = 42 - days.length;
    for (let d = 1; d <= remaining; d++) {
        days.push({
            date: new Date(year, month + 1, d),
            isCurrentMonth: false,
        });
    }

    return days;
});

const isSelected = (date) => {
    if (!selectedDate.value) return false;
    return formatDate(date) === formatDate(selectedDate.value);
};

const isToday = (date) => {
    return formatDate(date) === formatDate(new Date());
};

const isDisabled = (date) => {
    if (props.min && date < new Date(props.min)) return true;
    if (props.max && date > new Date(props.max)) return true;
    return false;
};

const selectDate = (day) => {
    if (isDisabled(day.date)) return;
    selectedDate.value = day.date;
    isOpen.value = false;
};

const prevMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.datepicker-container')) {
        isOpen.value = false;
    }
};

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', handleClickOutside);
        if (selectedDate.value) {
            currentMonth.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), 1);
        } else {
            currentMonth.value = new Date();
        }
    } else {
        document.removeEventListener('click', handleClickOutside);
    }
});
</script>

<template>
    <div class="datepicker-container relative">
        <!-- Label -->
        <label v-if="label" class="mb-2 block text-sm font-medium text-slate-300">
            {{ label }}
        </label>

        <!-- Input -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            :disabled="disabled"
            :class="[
                'flex w-full items-center gap-3 rounded-xl border bg-white/5 px-4 py-3 text-left transition-all duration-200',
                error
                    ? 'border-rose-500/50 focus:border-rose-500 focus:ring-rose-500/20'
                    : 'border-white/10 hover:border-white/20 focus:border-indigo-500 focus:ring-indigo-500/20',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
        >
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
            </svg>
            <span :class="displayValue ? 'text-white' : 'text-slate-500'">
                {{ displayValue || placeholder }}
            </span>
        </button>

        <!-- Error -->
        <p v-if="error" class="mt-2 text-sm text-rose-400">{{ error }}</p>

        <!-- Calendar Dropdown -->
        <div
            v-if="isOpen"
            class="absolute z-50 mt-2 w-72 rounded-2xl border border-white/10 bg-slate-800 p-4 shadow-2xl shadow-black/50"
        >
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between">
                <button
                    type="button"
                    @click.stop="prevMonth"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-white/10 hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="text-sm font-semibold text-white">{{ monthName }}</span>
                <button
                    type="button"
                    @click.stop="nextMonth"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-white/10 hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Days of week -->
            <div class="mb-2 grid grid-cols-7 gap-1">
                <div v-for="day in daysOfWeek" :key="day" class="py-1 text-center text-xs font-medium text-slate-500">
                    {{ day }}
                </div>
            </div>

            <!-- Calendar grid -->
            <div class="grid grid-cols-7 gap-1">
                <button
                    v-for="(day, i) in calendarDays"
                    :key="i"
                    type="button"
                    @click.stop="selectDate(day)"
                    :disabled="isDisabled(day.date)"
                    :class="[
                        'h-8 w-8 rounded-lg text-sm transition-all duration-150',
                        isSelected(day.date)
                            ? 'bg-indigo-500 font-semibold text-white'
                            : isToday(day.date)
                              ? 'bg-white/10 text-white'
                              : day.isCurrentMonth
                                ? 'text-slate-300 hover:bg-white/10'
                                : 'text-slate-600',
                        isDisabled(day.date) ? 'cursor-not-allowed opacity-30' : 'cursor-pointer',
                    ]"
                >
                    {{ day.date.getDate() }}
                </button>
            </div>
        </div>
    </div>
</template>
