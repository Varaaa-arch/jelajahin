<script setup lang="ts">
import { computed } from 'vue';
import {
    Armchair,
    Banknote,
    CircleAlert,
    Hourglass,
    Ticket,
    TrendingUp,
    UsersRound,
} from 'lucide-vue-next';

export interface StatItem {
    key: string;
    label: string;
    value: string;
    trend: string;
    trendTone: 'up' | 'warn' | 'neutral';
    icon: string;
}

const props = defineProps<{
    stat: StatItem;
}>();

const iconMap: Record<string, unknown> = {
    Banknote,
    Ticket,
    Hourglass,
    Armchair,
    UsersRound,
};

const iconComponent = computed(() => iconMap[props.stat.icon] ?? Banknote);

const trendClasses = computed(() => {
    if (props.stat.trendTone === 'warn') return 'text-orange-500';
    if (props.stat.trendTone === 'neutral') return 'text-gray-500';
    return 'text-teal-dark';
});
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5">
        <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gray-50" />
        <div class="relative flex items-start justify-between">
            <p class="text-xs font-semibold tracking-wide text-gray-700">{{ stat.label }}</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                <component :is="iconComponent" class="h-5 w-5" />
            </span>
        </div>
        <p class="relative mt-3 text-3xl font-black tracking-tight text-gray-900">{{ stat.value }}</p>
        <p class="relative mt-2 flex items-center gap-1.5 text-xs font-semibold" :class="trendClasses">
            <TrendingUp v-if="stat.trendTone === 'up'" class="h-3.5 w-3.5" />
            <CircleAlert v-else-if="stat.trendTone === 'warn'" class="h-3.5 w-3.5" />
            <span>{{ stat.trend }}</span>
        </p>
    </div>
</template>
