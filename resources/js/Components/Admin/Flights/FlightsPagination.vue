<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { PaginatedFlights } from '@/types/admin-flight';

defineProps<{ meta: PaginatedFlights }>();
defineEmits<{ (e: 'page', page: number): void }>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-b-2xl border border-t-0 border-gray-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-sm text-gray-600">
            Menampilkan {{ meta.from ?? 0 }}-{{ meta.to ?? 0 }} dari {{ meta.total }} penerbangan
        </p>
        <div class="flex items-center gap-1">
            <button
                type="button"
                :disabled="meta.current_page <= 1"
                class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 disabled:opacity-40"
                aria-label="Halaman sebelumnya"
                @click="$emit('page', meta.current_page - 1)"
            >
                <ChevronLeft class="h-4 w-4" />
            </button>
            <button
                v-for="p in [meta.current_page - 1, meta.current_page, meta.current_page + 1].filter(
                    (n) => n >= 1 && n <= meta.last_page,
                )"
                :key="p"
                type="button"
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                :class="
                    p === meta.current_page
                        ? 'bg-navy text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100'
                "
                @click="$emit('page', p)"
            >
                {{ p }}
            </button>
            <span v-if="meta.current_page + 1 < meta.last_page" class="px-1 text-sm text-gray-400">...</span>
            <button
                type="button"
                :disabled="meta.current_page >= meta.last_page"
                class="rounded-lg p-2 text-gray-700 transition hover:bg-gray-100 disabled:opacity-40"
                aria-label="Halaman berikutnya"
                @click="$emit('page', meta.current_page + 1)"
            >
                <ChevronRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
