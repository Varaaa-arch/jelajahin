<script setup lang="ts">
import { computed } from 'vue';
import { occupancyTone } from '@/types/admin-flight';

const props = defineProps<{
    pct: number;
    booked: number;
    total: number;
    animateKey?: string | number;
}>();

const tone = computed(() => occupancyTone(props.pct));
</script>

<template>
    <div class="min-w-[140px]">
        <p class="text-xs font-semibold" :class="tone.text">{{ pct }}% ({{ booked }}/{{ total }})</p>
        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
            <div
                :key="animateKey"
                class="h-full rounded-full transition-[width] duration-700 ease-out"
                :class="tone.bar"
                :style="{ width: `${Math.min(Math.max(pct, 0), 100)}%` }"
            />
        </div>
    </div>
</template>
