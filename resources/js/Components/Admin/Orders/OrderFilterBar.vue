<script setup lang="ts">
import { ref, watch } from 'vue';
import { Calendar, Plane, Search, Tags } from 'lucide-vue-next';
import type { AdminOrderFilters, AirlineOption, RouteOption } from '@/types/admin-order';

const props = defineProps<{
    filters: AdminOrderFilters;
    airlines: AirlineOption[];
    routes: RouteOption[];
}>();

const emit = defineEmits<{
    (e: 'change', patch: Partial<AdminOrderFilters>): void;
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

const orderPills = [
    { key: 'all', label: 'All' },
    { key: 'confirmed', label: 'Confirmed' },
    { key: 'pending', label: 'Pending' },
    { key: 'completed', label: 'Completed' },
    { key: 'cancelled', label: 'Cancelled' },
    { key: 'refund_requested', label: 'Refund Requested' },
] as const;

const paymentPills = [
    { key: 'all', label: 'All' },
    { key: 'paid', label: 'Paid' },
    { key: 'unpaid', label: 'Unpaid' },
    { key: 'refunded', label: 'Refunded' },
] as const;

const hasActiveFilter =
    !!props.filters.q ||
    props.filters.order_status !== 'all' ||
    props.filters.payment_status !== 'all' ||
    !!props.filters.airline_id ||
    !!props.filters.route_id ||
    !!props.filters.date_from ||
    !!props.filters.date_to;

function pillClass(active: boolean): string {
    return active
        ? 'bg-navy text-white shadow-sm'
        : 'border border-gray-300 bg-white text-gray-700 hover:border-navy hover:text-navy';
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
            <div class="relative lg:col-span-4">
                <Search
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />
                <input
                    v-model="localQ"
                    type="text"
                    placeholder="Cari PNR, Nama Penumpang, Email..."
                    class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @input="onSearchInput"
                />
            </div>

            <div class="flex items-center gap-2 lg:col-span-3">
                <Calendar class="h-4 w-4 shrink-0 text-gray-400" />
                <input
                    :value="filters.date_from ?? ''"
                    type="date"
                    aria-label="Tanggal booking mulai"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { date_from: ($event.target as HTMLInputElement).value || null })"
                />
                <span class="shrink-0 text-gray-400">-</span>
                <input
                    :value="filters.date_to ?? ''"
                    type="date"
                    aria-label="Tanggal booking akhir"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { date_to: ($event.target as HTMLInputElement).value || null })"
                />
            </div>

            <div class="relative lg:col-span-2">
                <Plane
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />
                <select
                    :value="filters.route_id ?? ''"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white py-2.5 pl-9 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { route_id: ($event.target as HTMLSelectElement).value || null })"
                >
                    <option value="">Route</option>
                    <option v-for="r in routes" :key="r.id" :value="r.id">{{ r.label }}</option>
                </select>
            </div>

            <div class="relative lg:col-span-3">
                <Tags
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />
                <select
                    :value="filters.airline_id ?? ''"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white py-2.5 pl-9 pr-8 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                    @change="emit('change', { airline_id: ($event.target as HTMLSelectElement).value || null })"
                >
                    <option value="">Airline</option>
                    <option v-for="a in airlines" :key="a.id" :value="a.id">
                        {{ a.code }} · {{ a.name }}
                    </option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-4 border-t border-gray-200 pt-4 xl:flex-row xl:gap-8">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Order Status</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="p in orderPills"
                        :key="p.key"
                        type="button"
                        class="rounded-full px-4 py-1.5 text-xs font-semibold transition"
                        :class="pillClass(filters.order_status === p.key)"
                        @click="emit('change', { order_status: p.key })"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>
            <div class="xl:border-l xl:border-gray-200 xl:pl-8">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Payment Status</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="p in paymentPills"
                        :key="p.key"
                        type="button"
                        class="rounded-full px-4 py-1.5 text-xs font-semibold transition"
                        :class="pillClass(filters.payment_status === p.key)"
                        @click="emit('change', { payment_status: p.key })"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>
            <div class="flex items-end">
                <button
                    v-if="hasActiveFilter"
                    type="button"
                    class="text-xs font-bold text-teal-dark hover:underline"
                    @click="emit('reset')"
                >
                    Reset semua filter
                </button>
            </div>
        </div>
    </section>
</template>
