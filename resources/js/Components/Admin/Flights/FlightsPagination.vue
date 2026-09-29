<script setup lang="ts">
import { ChevronDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';

export interface PaginationMeta {
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
}

withDefaults(
    defineProps<{
        meta: PaginationMeta;
        itemLabel?: string;
        showPerPage?: boolean;
        perPageOptions?: number[];
    }>(),
    { itemLabel: 'penerbangan', showPerPage: false, perPageOptions: () => [10, 20, 50] },
);

defineEmits<{
    (e: 'page', page: number): void;
    (e: 'per-page', perPage: number): void;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-b-2xl border border-t-0 border-gray-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-sm text-gray-600">
            Menampilkan {{ meta.from ?? 0 }}-{{ meta.to ?? 0 }} dari {{ meta.total }} {{ itemLabel }}
        </p>
        <div class="flex items-center gap-3">
            <label v-if="showPerPage" class="flex items-center gap-2 text-sm text-gray-600">
                Rows per page:
                <span class="relative">
                    <select
                        :value="meta.per_page"
                        class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-semibold text-gray-800 focus:border-teal focus:outline-none"
                        @change="$emit('per-page', Number(($event.target as HTMLSelectElement).value))"
                    >
                        <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"
                    />
                </span>
            </label>
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
    </div>
</template>
