<script setup lang="ts">
import { computed } from 'vue';
import { STATUS_META, type FlightDbStatus } from '@/types/admin-flight';

const props = defineProps<{ status: FlightDbStatus }>();
const meta = computed(() => STATUS_META[props.status] ?? STATUS_META.scheduled);
</script>

<template>
    <span
        class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
        :class="meta.classes"
    >
        {{ meta.label }}
    </span>
</template>
