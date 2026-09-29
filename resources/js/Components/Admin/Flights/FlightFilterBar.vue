<script setup lang="ts">
import { ref, watch } from 'vue';
import { ChevronDown, ListFilter, Search } from 'lucide-vue-next';
import type { AdminFlightFilters, FlightOption } from '@/types/admin-flight';

const props = defineProps<{
    filters: AdminFlightFilters;
    airlines: FlightOption[];
}>();

const emit = defineEmits<{
    (e: 'change', patch: Partial<AdminFlightFilters>): void;
    (e: 'reset'): void;
}>();

const tabs = [
    { key: 'semua', label: 'Semua' },
    { key: 'aktif', label: 'Aktif' },
    { key: 'nonaktif', label: 'Nonaktif' },
    { key: 'terjadwal', label: 'Terjadwal' },
] as const;

const sortOptions = [
    { key: 'terbaru', label: 'Urutkan: Terbaru' },
    { key: 'terlama', label: 'Urutkan: Terlama' },
    { key: 'okupansi', label: 'Urutkan: Okupansi' },
    { key: 'harga_tertinggi', label: 'Harga: Tertinggi' },
    { key: 'harga_terendah', label: 'Harga: Terendah' },
] as const;

const localQ = ref(props.filters.q ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

watch(
    () => props.filters.q,
    (v) => {
        if (v !== localQ.value) localQ.value = v ?? '';
    },
);

function onSearchInput(): void {
    if (debounce) clearTimeout(debounce);
    debounce = setTimeout(() => {
        emit('change', { q: localQ.value });
    }, 350);
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
            <div class="relative lg:col-span-5">
                <Search
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />
                <input
                    v-model="localQ"
                    type="text"
                    placeholder="Cari nomor penerbangan, maskapai, rute..."
                    class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @input="onSearchInput"
                />
            </div>

            <div class="relative lg:col-span-4">
                <select
                    :value="filters.airline_id ?? ''"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { airline_id: ($event.target as HTMLSelectElement).value || null })"
                >
                    <option value="">Semua Maskapai</option>
                    <option v-for="a in airlines" :key="a.id" :value="a.id">
                        {{ a.name }} ({{ a.code }})
                    </option>
                </select>
                <ChevronDown
                    class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
                />
            </div>

            <div class="relative lg:col-span-3">
                <select
                    :value="filters.sort"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { sort: ($event.target as HTMLSelectElement).value as AdminFlightFilters['sort'] })"
                >
                    <option v-for="o in sortOptions" :key="o.key" :value="o.key">{{ o.label }}</option>
                </select>
                <ListFilter
                    class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
                />
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12 xl:items-end">
            <div class="flex flex-wrap items-center gap-2 xl:col-span-4">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    type="button"
                    class="rounded-full border px-4 py-1.5 text-xs font-bold tracking-wide transition"
                    :class="
                        filters.status_tab === t.key
                            ? 'border-navy bg-navy text-white shadow-sm'
                            : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400 hover:text-gray-900'
                    "
                    @click="emit('change', { status_tab: t.key })"
                >
                    {{ t.label }}
                </button>
            </div>

            <div class="sm:flex sm:items-end sm:gap-2 xl:col-span-5">
                <p class="mb-1.5 text-xs font-bold tracking-wide text-gray-700 sm:w-full">Rentang Tanggal</p>
                <div class="flex items-center gap-2">
                    <input
                        :value="filters.date_from ?? ''"
                        type="date"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                        @change="emit('change', { date_from: ($event.target as HTMLInputElement).value || null })"
                    />
                    <span class="text-gray-400">-</span>
                    <input
                        :value="filters.date_to ?? ''"
                        type="date"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                        @change="emit('change', { date_to: ($event.target as HTMLInputElement).value || null })"
                    />
                </div>
            </div>

            <div class="xl:col-span-3">
                <div class="mb-1.5 flex items-center justify-between text-xs font-bold tracking-wide text-gray-700">
                    <span>Okupansi Min</span>
                    <span class="font-semibold text-gray-500">{{ filters.occupancy_min }}% - 100%</span>
                </div>
                <input
                    :value="filters.occupancy_min"
                    type="range"
                    min="0"
                    max="100"
                    step="5"
                    class="w-full accent-navy"
                    @input="emit('change', { occupancy_min: Number(($event.target as HTMLInputElement).value) })"
                />
            </div>
        </div>

        <button
            v-if="filters.q || filters.airline_id || filters.date_from || filters.date_to || filters.occupancy_min > 0 || filters.status_tab !== 'semua'"
            type="button"
            class="mt-3 text-xs font-bold text-teal-dark hover:underline"
            @click="emit('reset')"
        >
            Reset semua filter
        </button>
    </section>
</template>
