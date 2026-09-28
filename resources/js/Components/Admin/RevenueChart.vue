<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Title,
    Tooltip,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    values: number[];
}>();

const chartData = computed(() => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    let gradient = undefined as unknown as CanvasGradient;
    if (ctx) {
        gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(14, 122, 116, 0.95)');
        gradient.addColorStop(1, 'rgba(14, 165, 160, 0.15)');
    }
    return {
        labels: props.labels,
        datasets: [
            {
                data: props.values,
                backgroundColor: gradient ?? '#0e7a74',
                borderRadius: 4,
                borderSkipped: false,
                maxBarThickness: 36,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: { parsed: { y: number | null } }) => ` ${ctx.parsed.y ?? 0}M`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: false,
            min: 0,
            max: 5,
            ticks: {
                stepSize: 1,
                callback: (v: string | number) => `${v}M`,
                color: '#9CA3AF',
                font: { size: 11 },
            },
            grid: { display: false },
            border: { display: true, color: '#E5E7EB' },
        },
        x: {
            ticks: { display: false },
            grid: { display: false },
            border: { display: true, color: '#E5E7EB' },
        },
    },
};
</script>

<template>
    <div class="h-64 w-full">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
