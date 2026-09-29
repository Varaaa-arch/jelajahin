<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { ChevronDown, Search } from 'lucide-vue-next';
import type { AdminUserFilters } from '@/types/admin-user';

const props = defineProps<{ filters: AdminUserFilters }>();

const emit = defineEmits<{
    (e: 'change', patch: Partial<AdminUserFilters>): void;
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

const sortOptions = [
    { key: 'recent', label: 'Terbaru' },
    { key: 'oldest', label: 'Terlama' },
    { key: 'name_az', label: 'Nama A-Z' },
    { key: 'bookings', label: 'Booking terbanyak' },
] as const;

const hasActiveFilter = computed(
    () =>
        !!props.filters.q ||
        props.filters.role !== 'all' ||
        props.filters.status !== 'all',
);

function selectClass(): string {
    return 'w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20';
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-widest text-gray-500">Cari Pengguna</p>
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="localQ"
                        type="text"
                        placeholder="Name, Email..."
                        class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                        @input="onSearchInput"
                    />
                </div>
            </div>

            <div class="lg:col-span-2">
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-widest text-gray-500">Tipe Pengguna</p>
                <span class="relative block">
                    <select
                        :value="filters.role"
                        :class="selectClass()"
                        @change="emit('change', { role: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">Semua Tipe</option>
                        <option value="user">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                </span>
            </div>

            <div class="lg:col-span-2">
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-widest text-gray-500">Status</p>
                <span class="relative block">
                    <select
                        :value="filters.status"
                        :class="selectClass()"
                        @change="emit('change', { status: ($event.target as HTMLSelectElement).value })"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                </span>
            </div>

            <div class="lg:col-span-2">
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-widest text-gray-500">Urutkan</p>
                <span class="relative block">
                    <select
                        :value="filters.sort"
                        :class="selectClass()"
                        @change="emit('change', { sort: ($event.target as HTMLSelectElement).value })"
                    >
                        <option v-for="o in sortOptions" :key="o.key" :value="o.key">{{ o.label }}</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                </span>
            </div>
        </div>

        <button
            v-if="hasActiveFilter"
            type="button"
            class="mt-3 text-xs font-bold text-teal-dark hover:underline"
            @click="emit('reset')"
        >
            Reset semua filter
        </button>
    </section>
</template>
