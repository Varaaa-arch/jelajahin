<script setup lang="ts">
import { Plane, RotateCcw } from 'lucide-vue-next';

defineEmits<{ (e: 'reset'): void }>();
</script>

<template>
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-navy/5 text-navy">
            <Plane class="h-6 w-6" />
        </span>
        <div>
            <p class="text-base font-bold text-gray-900">Tidak ada penerbangan ditemukan</p>
            <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci atau reset filter pencarian.</p>
        </div>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50"
            @click="$emit('reset')"
        >
            <RotateCcw class="h-3.5 w-3.5" />
            RESET FILTER
        </button>
    </div>
</template>
