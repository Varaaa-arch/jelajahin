<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Search } from 'lucide-vue-next';
import type { AdminPaymentFilters, PaymentMethodSummary } from '@/types/admin-payment';
import { paymentMethodLabel } from '@/types/admin-payment';

const props = defineProps<{
    filters: AdminPaymentFilters;
    methods: PaymentMethodSummary[];
}>();

const emit = defineEmits<{
    (e: 'change', patch: Partial<AdminPaymentFilters>): void;
    (e: 'reset'): void;
}>();

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

const statusPills = [
    { key: 'all', label: 'All' },
    { key: 'paid', label: 'Paid' },
    { key: 'pending', label: 'Pending' },
    { key: 'failed', label: 'Failed' },
    { key: 'refunded', label: 'Refunded' },
] as const;

const sortOptions = [
    { key: 'recent', label: 'Sort by: Most Recent' },
    { key: 'oldest', label: 'Sort by: Oldest' },
    { key: 'amount_desc', label: 'Sort by: Amount (High)' },
    { key: 'amount_asc', label: 'Sort by: Amount (Low)' },
] as const;

const hasActiveFilter = computed(
    () =>
        !!props.filters.q ||
        props.filters.status !== 'all' ||
        props.filters.method !== 'all' ||
        !!props.filters.date_from ||
        !!props.filters.date_to,
);

function pillClass(active: boolean): string {
    return active
        ? 'bg-navy text-white shadow-sm'
        : 'border border-gray-300 bg-white text-gray-700 hover:border-navy hover:text-navy';
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="mb-1.5 text-xs font-bold text-gray-700">Search Transaction</p>
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="localQ"
                        type="text"
                        placeholder="Transaction ID or PNR..."
                        class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                        @input="onSearchInput"
                    />
                </div>
            </div>

            <div class="lg:col-span-2">
                <p class="mb-1.5 text-xs font-bold text-gray-700">Status</p>
                <select
                    :value="filters.status"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { status: ($event.target as HTMLSelectElement).value })"
                >
                    <option value="all">All Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <p class="mb-1.5 text-xs font-bold text-gray-700">Method</p>
                <select
                    :value="filters.method"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { method: ($event.target as HTMLSelectElement).value })"
                >
                    <option value="all">All Methods</option>
                    <option v-for="m in methods" :key="m.method" :value="m.method">
                        {{ paymentMethodLabel(m.method) }}
                    </option>
                </select>
            </div>

            <div class="lg:col-span-4">
                <p class="mb-1.5 text-xs font-bold text-gray-700">Date Range</p>
                <div class="flex items-center gap-2">
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
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-200 pt-4">
            <button
                v-for="p in statusPills"
                :key="p.key"
                type="button"
                class="rounded-full px-4 py-1.5 text-xs font-semibold transition"
                :class="pillClass(filters.status === p.key)"
                @click="emit('change', { status: p.key })"
            >
                {{ p.label }}
            </button>
            <select
                :value="filters.sort"
                class="ml-auto rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-800 focus:border-teal focus:outline-none"
                @change="emit('change', { sort: ($event.target as HTMLSelectElement).value })"
            >
                <option v-for="o in sortOptions" :key="o.key" :value="o.key">{{ o.label }}</option>
            </select>
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
