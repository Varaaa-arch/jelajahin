<script setup lang="ts">
import { ref, watch } from 'vue';
import { ChevronDown, ListFilter, Search } from 'lucide-vue-next';
import type { AdminPassengerFilters } from '@/types/admin-passenger';

const props = defineProps<{
    filters: AdminPassengerFilters;
    nationalities: string[];
}>();

const emit = defineEmits<{
    (e: 'change', patch: Partial<AdminPassengerFilters>): void;
    (e: 'reset'): void;
}>();

const sortOptions = [
    { key: 'recent', label: 'Sort by: Most Recent' },
    { key: 'oldest', label: 'Sort by: Oldest' },
    { key: 'name_az', label: 'Sort by: Name A-Z' },
    { key: 'flight_date', label: 'Sort by: Flight Date' },
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

const hasActiveFilter =
    !!props.filters.q ||
    props.filters.status !== 'all' ||
    props.filters.check_in !== 'all' ||
    props.filters.nationality !== 'all' ||
    props.filters.payment !== 'all' ||
    !!props.filters.date_from ||
    !!props.filters.date_to;
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
                    placeholder="Search by Name, Email, Phone, Passport or PNR..."
                    class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @input="onSearchInput"
                />
            </div>

            <div class="flex items-center gap-2 lg:col-span-4">
                <input
                    :value="filters.date_from ?? ''"
                    type="date"
                    aria-label="Tanggal mulai"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { date_from: ($event.target as HTMLInputElement).value || null })"
                />
                <span class="shrink-0 text-gray-400">-</span>
                <input
                    :value="filters.date_to ?? ''"
                    type="date"
                    aria-label="Tanggal akhir"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { date_to: ($event.target as HTMLInputElement).value || null })"
                />
            </div>

            <div class="relative lg:col-span-3">
                <select
                    :value="filters.sort"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { sort: ($event.target as HTMLSelectElement).value })"
                >
                    <option v-for="o in sortOptions" :key="o.key" :value="o.key">{{ o.label }}</option>
                </select>
                <ListFilter
                    class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
                />
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-gray-200 pt-4">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                Status:
                <span class="relative">
                    <select
                        :value="filters.status"
                        class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none"
                        @change="emit('change', { status: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">All Statuses</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"
                    />
                </span>
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Check-in:
                <span class="relative">
                    <select
                        :value="filters.check_in"
                        class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none"
                        @change="emit('change', { check_in: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">All</option>
                        <option value="checked_in">Checked-in</option>
                        <option value="not_checked_in">Not checked-in</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"
                    />
                </span>
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Nationality:
                <span class="relative">
                    <select
                        :value="filters.nationality"
                        class="max-w-44 appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none"
                        @change="emit('change', { nationality: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">All Nationalities</option>
                        <option v-for="n in nationalities" :key="n" :value="n">{{ n }}</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"
                    />
                </span>
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Payment:
                <span class="relative">
                    <select
                        :value="filters.payment"
                        class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none"
                        @change="emit('change', { payment: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">All Payments</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="expired">Expired</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"
                    />
                </span>
            </label>

            <button
                v-if="hasActiveFilter"
                type="button"
                class="text-xs font-bold text-teal-dark hover:underline"
                @click="emit('reset')"
            >
                Reset semua filter
            </button>
        </div>
    </section>
</template>
