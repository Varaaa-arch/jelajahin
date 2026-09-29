<script setup lang="ts">
import { Eye, MoveRight, Pencil, Trash2 } from 'lucide-vue-next';
import { formatFlightDate, type AdminFlight } from '@/types/admin-flight';
import OccupancyBar from './OccupancyBar.vue';
import StatusBadge from './StatusBadge.vue';

defineProps<{
    flights: AdminFlight[];
    loading?: boolean;
    offset?: number;
}>();

defineEmits<{
    (e: 'edit', flight: AdminFlight): void;
    (e: 'delete', flight: AdminFlight): void;
}>();

function airlineInitials(code: string): string {
    return (code || '-').slice(0, 2).toUpperCase();
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-600">
                        <th class="px-5 py-3.5 font-semibold">No</th>
                        <th class="px-5 py-3.5 font-semibold">Flight #</th>
                        <th class="px-5 py-3.5 font-semibold">Maskapai</th>
                        <th class="px-5 py-3.5 font-semibold">Rute</th>
                        <th class="px-5 py-3.5 font-semibold">Waktu</th>
                        <th class="px-5 py-3.5 font-semibold">Okupansi</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="`skel-${i}`" class="animate-pulse">
                            <td v-for="c in 8" :key="c" class="px-5 py-4">
                                <div class="h-4 rounded bg-gray-100" />
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <TransitionGroup name="flight-row">
                            <tr
                                v-for="(f, idx) in flights"
                                :key="f.id"
                                class="transition-colors hover:bg-gray-50/70"
                            >
                                <td class="px-5 py-4 text-gray-700">{{ (offset ?? 0) + idx + 1 }}</td>
                                <td class="px-5 py-4 font-semibold text-gray-900">{{ f.flight_number }}</td>
                                <td class="px-5 py-4">
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-[11px] font-black text-navy"
                                        >
                                            {{ airlineInitials(f.airline_code) }}
                                        </span>
                                        <span class="font-medium text-gray-800">{{ f.airline_name }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="flex items-center gap-1.5 font-medium text-gray-900">
                                        {{ f.origin_code }}
                                        <MoveRight class="h-4 w-4 text-gray-500" />
                                        {{ f.destination_code }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-900">{{ formatFlightDate(f.departure_date) }}</p>
                                    <p class="text-xs text-gray-500">{{ f.departure_time }} WIB</p>
                                </td>
                                <td class="px-5 py-4">
                                    <OccupancyBar
                                        :pct="f.occupancy_pct"
                                        :booked="f.seats_booked"
                                        :total="f.seats_total"
                                        :animate-key="f.id + '-' + f.occupancy_pct"
                                    />
                                </td>
                                <td class="px-5 py-4">
                                    <StatusBadge :status="f.status" />
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-navy"
                                            title="Lihat detail"
                                            aria-label="Lihat detail"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition hover:bg-teal/10 hover:text-teal-dark"
                                            title="Ubah"
                                            aria-label="Ubah penerbangan"
                                            @click="$emit('edit', f)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Hapus"
                                            aria-label="Hapus penerbangan"
                                            @click="$emit('delete', f)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </TransitionGroup>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.flight-row-enter-active {
    transition:
        opacity 0.35s ease,
        transform 0.35s ease;
}
.flight-row-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.flight-row-leave-active {
    transition: opacity 0.2s ease;
}
.flight-row-leave-to {
    opacity: 0;
}
.flight-row-move {
    transition: transform 0.35s ease;
}
</style>
