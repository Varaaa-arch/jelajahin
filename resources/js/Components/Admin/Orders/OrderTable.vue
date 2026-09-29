<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronUp, EllipsisVertical, Plane, ConciergeBell, Users } from 'lucide-vue-next';
import {
    ORDER_STATUS_META,
    PAYMENT_STATUS_META,
    formatIDR,
    formatOrderDate,
    type AdminOrder,
} from '@/types/admin-order';

const props = defineProps<{
    orders: AdminOrder[];
    loading?: boolean;
    offset?: number;
}>();

defineEmits<{
    (e: 'receipt', order: AdminOrder, doc: 'eticket' | 'invoice'): void;
    (e: 'modify', order: AdminOrder): void;
    (e: 'cancel', order: AdminOrder): void;
    (e: 'reschedule', order: AdminOrder): void;
    (e: 'status', order: AdminOrder): void;
}>();

const expandedId = ref<string | null>(null);
const menuId = ref<string | null>(null);

function toggle(id: string): void {
    expandedId.value = expandedId.value === id ? null : id;
}

function orderMeta(status: string): { label: string; classes: string } {
    return ORDER_STATUS_META[status] ?? { label: status, classes: 'bg-gray-200/70 text-gray-700' };
}

function payMeta(status: string): { label: string; classes: string } {
    return PAYMENT_STATUS_META[status] ?? { label: status, classes: 'bg-gray-200/70 text-gray-700' };
}
</script>

<template>
    <div class="overflow-hidden rounded-t-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-600">
                        <th class="w-12 px-5 py-3.5 font-semibold">#</th>
                        <th class="px-5 py-3.5 font-semibold">PNR</th>
                        <th class="px-5 py-3.5 font-semibold">Passenger</th>
                        <th class="px-5 py-3.5 font-semibold">Route</th>
                        <th class="px-5 py-3.5 font-semibold">Flight Date</th>
                        <th class="px-5 py-3.5 font-semibold">Amount</th>
                        <th class="px-5 py-3.5 font-semibold">Payment</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="loading">
                        <tr v-for="i in 6" :key="`skel-${i}`" class="animate-pulse">
                            <td v-for="c in 9" :key="c" class="px-5 py-4">
                                <div class="h-4 rounded bg-gray-100" />
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <template v-for="(o, idx) in orders" :key="o.id">
                            <tr
                                class="cursor-pointer transition-colors hover:bg-gray-50/70"
                                :class="expandedId === o.id ? 'bg-gray-50/50' : ''"
                                @click="toggle(o.id)"
                            >
                                <td class="px-5 py-4 text-gray-700">{{ (offset ?? 0) + idx + 1 }}</td>
                                <td class="px-5 py-4" @click.stop>
                                    <Link
                                        :href="`/admin/orders/${o.id}`"
                                        class="inline-flex items-center gap-1 font-bold text-teal-dark hover:underline"
                                    >
                                        {{ o.pnr }}
                                        <ChevronUp
                                            class="h-3.5 w-3.5 transition-transform"
                                            :class="expandedId === o.id ? '' : 'rotate-180'"
                                        />
                                    </Link>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-gray-900">{{ o.booker_name }}</p>
                                    <p v-if="o.passenger_count > 1" class="text-xs text-gray-500">
                                        +{{ o.passenger_count - 1 }} penumpang lain
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-800">
                                    {{ o.origin_code }} → {{ o.destination_code }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-800">
                                    {{ formatOrderDate(o.departure_date) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">
                                    {{ formatIDR(o.total_price) }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                        :class="payMeta(o.payment_status).classes"
                                    >
                                        {{ payMeta(o.payment_status).label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                        :class="orderMeta(o.order_status).classes"
                                        :title="
                                            ['cancelled', 'refunded', 'completed'].includes(o.order_status)
                                                ? orderMeta(o.order_status).label
                                                : 'Ubah status pesanan'
                                        "
                                        @click.stop="$emit('status', o)"
                                    >
                                        {{ orderMeta(o.order_status).label }}
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-right" @click.stop>
                                    <div class="relative inline-block">
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-navy"
                                            aria-label="Aksi pesanan"
                                            @click="menuId = menuId === o.id ? null : o.id"
                                        >
                                            <EllipsisVertical class="h-4 w-4" />
                                        </button>
                                        <div
                                            v-if="menuId === o.id"
                                            class="absolute right-0 z-20 w-44 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 text-left shadow-xl"
                                        >
                                            <Link
                                                :href="`/admin/orders/${o.id}`"
                                                class="block w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                @click="menuId = null"
                                            >
                                                View Detail
                                            </Link>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                @click="$emit('receipt', o, 'eticket'); menuId = null"
                                            >
                                                View E-Ticket
                                            </button>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                @click="$emit('receipt', o, 'invoice'); menuId = null"
                                            >
                                                View Invoice
                                            </button>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                @click="$emit('modify', o); menuId = null"
                                            >
                                                Modify
                                            </button>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                @click="$emit('reschedule', o); menuId = null"
                                            >
                                                Reschedule
                                            </button>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-left text-xs font-bold text-red-600 hover:bg-red-50"
                                                @click="$emit('cancel', o); menuId = null"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="expandedId === o.id" :key="`${o.id}-detail`">
                                <td colspan="9" class="border-l-4 border-l-teal bg-gray-50/60 px-5 py-4">
                                    <div
                                        class="grid grid-cols-1 gap-4 lg:grid-cols-3"
                                        @click.stop
                                    >
                                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                                            <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">
                                                <Users class="h-4 w-4" /> Passengers
                                            </p>
                                            <ul class="mt-3 space-y-2">
                                                <li
                                                    v-for="p in o.passengers"
                                                    :key="p.id"
                                                    class="flex items-center justify-between gap-2 text-sm"
                                                >
                                                    <span class="font-medium text-gray-900">{{ p.name }}</span>
                                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                                        Seat {{ p.seat_number ?? 'TBA' }}
                                                    </span>
                                                </li>
                                            </ul>
                                            <p v-if="o.special_requests" class="mt-3 text-xs text-gray-500">
                                                Request: {{ o.special_requests }}
                                            </p>
                                        </div>
                                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                                            <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">
                                                <Plane class="h-4 w-4" /> Flight Info
                                            </p>
                                            <div class="mt-3 flex items-start gap-3">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal/15 text-xs font-black text-teal-dark">
                                                    {{ o.airline_code.slice(0, 2).toUpperCase() }}
                                                </span>
                                                <div class="text-sm">
                                                    <p class="font-bold text-gray-900">
                                                        {{ o.airline_name }} {{ o.flight_number }}
                                                    </p>
                                                    <p class="mt-1 inline-flex items-center gap-1.5 text-xs text-gray-600">
                                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-semibold">
                                                            {{ o.origin_code }} {{ o.departure_time ?? '' }}
                                                        </span>
                                                        →
                                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-semibold">
                                                            {{ o.destination_code }} {{ o.arrival_time ?? '' }}
                                                        </span>
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        Booking: {{ formatOrderDate(o.booking_date) }} · {{ o.booker_email }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-4">
                                            <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gray-500">
                                                <ConciergeBell class="h-4 w-4" /> Add-ons
                                            </p>
                                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                                <span v-if="o.addons_baggage && o.addons_baggage !== 'none'" class="rounded-lg border border-gray-300 px-2.5 py-1 font-medium text-gray-700">
                                                    Extra Baggage {{ o.addons_baggage }}
                                                </span>
                                                <span v-if="o.addons_insurance && o.addons_insurance !== 'none'" class="rounded-lg border border-gray-300 px-2.5 py-1 font-medium text-gray-700">
                                                    {{ o.addons_insurance === 'premium' ? 'Premium Insurance' : o.addons_insurance }}
                                                </span>
                                                <span v-for="m in o.addons_meals" :key="m" class="rounded-lg border border-gray-300 px-2.5 py-1 font-medium text-gray-700">
                                                    {{ m }}
                                                </span>
                                                <span v-if="!o.addons_baggage && !o.addons_insurance && o.addons_meals.length === 0" class="text-gray-400">
                                                    Tidak ada add-ons
                                                </span>
                                            </div>
                                            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                                <button
                                                    type="button"
                                                    class="rounded-lg border border-gray-800 px-3 py-2 text-[11px] font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                                                    @click="$emit('receipt', o, 'invoice')"
                                                >
                                                    VIEW RECEIPT
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-lg border border-gray-800 px-3 py-2 text-[11px] font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                                                    @click="$emit('modify', o)"
                                                >
                                                    MODIFY
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-lg border border-red-500 px-3 py-2 text-[11px] font-bold tracking-wide text-red-600 transition hover:bg-red-600 hover:text-white"
                                                    @click="$emit('cancel', o)"
                                                >
                                                    CANCEL
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-lg bg-navy px-3 py-2 text-[11px] font-bold tracking-wide text-white transition hover:bg-navy-mid"
                                                    @click="$emit('reschedule', o)"
                                                >
                                                    RESCHEDULE
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div v-if="!loading && orders.length === 0" class="rounded-b-2xl border border-t-0 border-gray-200 bg-white px-5 py-12 text-center">
        <p class="text-sm font-bold text-gray-900">Tidak ada pesanan ditemukan</p>
        <p class="mt-1 text-xs text-gray-500">Coba ubah kata kunci atau reset filter.</p>
    </div>
</template>

<style scoped>
tr {
    animation: none;
}
</style>
