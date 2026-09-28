<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

export interface DonutSlice {
    label: string;
    value: number;
    color: string;
}

const props = defineProps<{
    slices: DonutSlice[];
}>();

const chartData = computed(() => ({
    labels: props.slices.map((s) => s.label),
    datasets: [
        {
            data: props.slices.map((s) => s.value),
            backgroundColor: props.slices.map((s) => s.color),
            borderWidth: 3,
            borderColor: '#ffffff',
            hoverOffset: 4,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: { label?: string; parsed: number }) => ` ${ctx.label ?? ''}: ${ctx.parsed}%`,
            },
        },
    },
};
</script>

<template>
    <div class="relative mx-auto h-56 w-full max-w-xs">
        <Doughnut :data="chartData" :options="chartOptions" />
        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
            <p class="text-3xl font-black text-gray-900">1.2K</p>
            <p class="text-xs font-semibold text-gray-500">Total</p>
        </div>
    </div>
</template>
