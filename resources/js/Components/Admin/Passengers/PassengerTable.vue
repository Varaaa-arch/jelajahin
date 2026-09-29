<script setup lang="ts">
import { ref } from 'vue';
import { ChevronDown, Eye, Pencil } from 'lucide-vue-next';
import {
    BOOKING_STATUS_META,
    formatPassengerDate,
    type AdminPassenger,
} from '@/types/admin-passenger';

const props = defineProps<{
    passengers: AdminPassenger[];
    loading?: boolean;
    offset?: number;
}>();

defineEmits<{
    (e: 'edit', passenger: AdminPassenger): void;
}>();

const expandedId = ref<string | null>(null);

function toggle(id: string): void {
    expandedId.value = expandedId.value === id ? null : id;
}

function statusMeta(status: string): { label: string; classes: string } {
    return BOOKING_STATUS_META[status] ?? BOOKING_STATUS_META.pending;
}

function docLabel(p: AdminPassenger): string {
    if (p.passport_number || p.passport_masked !== '-') return p.passport_masked;
    return p.identity_masked;
}

function nationalityLabel(p: AdminPassenger): string {
    return p.nationality ? `${p.nationality}` : '-';
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-600">
                        <th class="w-12 px-5 py-3.5 font-semibold">#</th>
                        <th class="px-5 py-3.5 font-semibold">Passenger Name</th>
                        <th class="px-5 py-3.5 font-semibold">Contact</th>
                        <th class="px-5 py-3.5 font-semibold">ID / Ref</th>
                        <th class="px-5 py-3.5 font-semibold">Flight Info</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="loading">
                        <tr v-for="i in 6" :key="`skel-${i}`" class="animate-pulse">
                            <td v-for="c in 7" :key="c" class="px-5 py-4">
                                <div class="h-4 rounded bg-gray-100" />
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <template v-for="(p, idx) in passengers" :key="p.id">
                            <Transition name="passenger-row" appear>
                                <tr
                                    class="transition-colors hover:bg-gray-50/70"
                                    :class="expandedId === p.id ? 'bg-gray-50/50' : ''"
                                >
                                    <td class="px-5 py-4 text-gray-700">{{ (offset ?? 0) + idx + 1 }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ p.name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ p.identity_type ?? 'ID' }} ({{ nationalityLabel(p) }})
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-gray-800">{{ p.email }}</p>
                                        <p class="text-xs text-gray-500">{{ docLabel(p) }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-gray-900">{{ docLabel(p) }}</p>
                                        <p class="text-xs text-gray-500">PNR: {{ p.pnr }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ p.flight_number }}</p>
                                        <p class="text-xs text-gray-500">{{ formatPassengerDate(p.departure_date) }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                            :class="statusMeta(p.booking_status).classes"
                                        >
                                            {{ statusMeta(p.booking_status).label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-navy"
                                                :title="expandedId === p.id ? 'Tutup detail' : 'Lihat detail'"
                                                :aria-label="expandedId === p.id ? 'Tutup detail' : 'Lihat detail'"
                                                @click="toggle(p.id)"
                                            >
                                                <Eye v-if="expandedId !== p.id" class="h-4 w-4" />
                                                <ChevronDown
                                                    v-else
                                                    class="h-4 w-4 rotate-180 transition-transform"
                                                />
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg p-2 text-gray-500 transition hover:bg-teal/10 hover:text-teal-dark"
                                                title="Ubah"
                                                aria-label="Ubah penumpang"
                                                @click="$emit('edit', p)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </Transition>
                            <tr v-if="expandedId === p.id" :key="`${p.id}-detail`">
                                <td colspan="7" class="bg-gray-50/60 px-5 py-3">
                                    <Transition name="detail-expand" appear>
                                        <div
                                            class="grid grid-cols-1 gap-5 rounded-xl border border-gray-200 bg-white p-5 text-sm sm:grid-cols-2 xl:grid-cols-4"
                                        >
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                    Travel Docs
                                                </p>
                                                <p class="mt-2 text-gray-800">
                                                    Passport: {{ p.passport_masked }}
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    {{ p.identity_type ?? 'ID' }}: {{ p.identity_masked }}
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    Nationality: {{ p.nationality ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                    Flight Extras
                                                </p>
                                                <p class="mt-2 text-gray-800">
                                                    Seat: {{ p.seat_number ?? '-' }}
                                                    <span v-if="p.seat_class">({{ p.seat_class }})</span>
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    Baggage: {{ p.baggage_allowance_kg ?? 0 }}kg
                                                    <span v-if="p.carry_on_allowance_kg">
                                                        + {{ p.carry_on_allowance_kg }}kg Cabin
                                                    </span>
                                                    <span v-if="p.addons_baggage && p.addons_baggage !== 'none'">
                                                        · Extra {{ p.addons_baggage }}
                                                    </span>
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    Rute:
                                                    {{ p.origin_code ?? '-' }} → {{ p.destination_code ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                    Add-ons &amp; Requests
                                                </p>
                                                <p class="mt-2 text-gray-800">
                                                    Meals:
                                                    {{ p.addons_meals.length > 0 ? p.addons_meals.join(', ') : '-' }}
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    Insurance: {{ p.addons_insurance ?? '-' }}
                                                </p>
                                                <p class="mt-1 text-gray-800">
                                                    Request: {{ p.special_requests ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                    Booking Contact
                                                </p>
                                                <p class="mt-2 text-gray-800">{{ p.booker_name }}</p>
                                                <p class="mt-1 text-gray-800">{{ p.email }}</p>
                                                <p class="mt-1 text-gray-800">
                                                    Payment: {{ p.payment_status }}
                                                    <span v-if="p.payment_method">({{ p.payment_method }})</span>
                                                    · Check-in:
                                                    {{ p.check_in_status === 'checked_in' ? 'Done' : 'Not yet' }}
                                                </p>
                                            </div>
                                        </div>
                                    </Transition>
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.passenger-row-enter-active {
    transition:
        opacity 0.35s ease,
        transform 0.35s ease;
}
.passenger-row-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.detail-expand-enter-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.detail-expand-enter-from {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
