<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface BookingFragment {
    id: string;
    pnr_code: string;
    status: string;
    total_price: number;
    passenger_count: number;
    created_at: string;
    flight: {
        flight_number: string | null;
        departure_date: string | null;
        departure_date_display: string | null;
        departure_time: string | null;
        arrival_time: string | null;
        airline: string | null;
        airline_code: string | null;
        origin: string | null;
        origin_airport: string | null;
        destination: string | null;
        destination_airport: string | null;
    };
    payment: { status: string; amount: number } | null;
    invoice: string | null;
    eticket_count: number;
    etickets: { eticket_number: string; passenger_name: string; seat_number: string | null }[];
    passengers: string[];
}

interface Props {
    summary: {
        total_bookings: number;
        upcoming: number;
        completed: number;
        total_spent: number;
        member_since: string | null;
    };
    upcoming: BookingFragment[];
    history: BookingFragment[];
}

const props = defineProps<Props>();

const expanded = ref<Record<string, boolean>>({});

const page = usePage();
const authUser = computed(() => page.props.auth.user as { name: string; email: string } | undefined);

const displayName = computed(() => authUser.value?.name ?? 'Member');
const email = computed(() => authUser.value?.email ?? '');
const initial = computed(() => (displayName.value.charAt(0) || 'J').toUpperCase());

const formatRupiah = (value: number): string =>
    'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);

const statusMeta = (status: string): { label: string; classes: string } => {
    const map: Record<string, { label: string; classes: string }> = {
        pending: {
            label: 'Menunggu Pembayaran',
            classes: 'bg-amber-100 text-amber-700',
        },
        confirmed: {
            label: 'Dikonfirmasi',
            classes: 'bg-teal-100 text-teal-700',
        },
        completed: {
            label: 'Selesai',
            classes: 'bg-emerald-100 text-emerald-700',
        },
        cancelled: {
            label: 'Dibatalkan',
            classes: 'bg-red-100 text-red-600',
        },
    };

    return map[status] ?? { label: status, classes: 'bg-gray-100 text-gray-600' };
};

const paymentMeta = (status: string): { label: string; classes: string } => {
    const map: Record<string, { label: string; classes: string }> = {
        success: { label: 'Lunas', classes: 'text-emerald-600' },
        pending: { label: 'Menunggu', classes: 'text-amber-600' },
        expired: { label: 'Kedaluwarsa', classes: 'text-red-500' },
        failed: { label: 'Gagal', classes: 'text-red-500' },
        deny: { label: 'Ditolak', classes: 'text-red-500' },
    };

    return map[status] ?? { label: status, classes: 'text-gray-500' };
};

const documents = computed<BookingFragment[]>(() =>
    [...props.upcoming, ...props.history].filter(
        (b) => b.eticket_count > 0 || b.invoice !== null
    )
);

const toggleExpanded = (id: string): void => {
    expanded.value[id] = !expanded.value[id];
};

const statsCards = computed(() => [
    {
        label: 'Total Pemesanan',
        value: String(props.summary.total_bookings),
        icon: 'M3 10h18M7 15h2m4 0h2m4 0h2M5 19V5a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H5z',
        accent: 'bg-navy text-white',
    },
    {
        label: 'Penerbangan Akan Datang',
        value: String(props.summary.upcoming),
        icon: 'M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z',
        accent: 'bg-teal text-white',
    },
    {
        label: 'Perjalanan Selesai',
        value: String(props.summary.completed),
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        accent: 'bg-emerald-500 text-white',
    },
    {
        label: 'Total Belanja',
        value: formatRupiah(props.summary.total_spent),
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        accent: 'bg-amber-400 text-navy',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Hero / Member card -->
        <section class="relative overflow-hidden rounded-3xl bg-navy p-8 text-white shadow-xl">
            <div
                class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-teal/20 blur-3xl"
            />
            <div
                class="absolute -bottom-20 right-32 h-56 w-56 rounded-full bg-teal/10 blur-2xl"
            />
            <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-teal to-emerald-400 text-3xl font-black uppercase shadow-lg shadow-gray-900/10"
                    >
                        {{ initial }}
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-widest text-teal-300">
                            Member Jelajahin
                        </p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight">
                            Halo, {{ displayName }}
                        </h1>
                        <p class="mt-1 text-sm text-white/60">
                            {{ email }} · Member sejak {{ summary.member_since ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div
                        class="rounded-2xl border border-white/10 bg-white/5 px-6 py-4 backdrop-blur"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/50">
                            Total Pemesanan
                        </p>
                        <p class="mt-1 text-2xl font-black text-teal-300">
                            {{ summary.total_bookings }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-white/10 bg-white/5 px-6 py-4 backdrop-blur"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/50">
                            Total Belanja
                        </p>
                        <p class="mt-1 text-2xl font-black text-white">
                            {{ formatRupiah(summary.total_spent) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick stats -->
        <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in statsCards"
                :key="card.label"
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-lg"
                    :class="card.accent"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :d="card.icon"
                        />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        {{ card.label }}
                    </p>
                    <p class="mt-0.5 text-xl font-black text-gray-900">
                        {{ card.value }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Upcoming bookings -->
        <section id="pemesanan" class="mt-10 scroll-mt-24">
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-gray-900">
                        Pemesanan Aktif
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Penerbangan yang akan datang dan menunggu pembayaran.
                    </p>
                </div>
                <span
                    class="rounded-full bg-teal-50 px-3 py-1 text-sm font-bold text-teal-700"
                >
                    {{ summary.upcoming }} aktif
                </span>
            </div>

            <div v-if="upcoming.length === 0" class="mt-5">
                <div
                    class="flex flex-col items-center rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-14 text-center"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-800">Belum ada pemesanan aktif</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                        Temukan dan pesan tiket pesawat pertamamu untuk mulai menjelajahi Nusantara.
                    </p>
                    <Link
                        :href="route('flights.search')"
                        class="mt-6 rounded-xl bg-teal px-6 py-3 text-sm font-bold text-white shadow-lg shadow-gray-900/10 transition hover:bg-teal-600"
                    >
                        Cari Penerbangan
                    </Link>
                </div>
            </div>

            <div v-else class="mt-5 space-y-4">
                <div
                    v-for="booking in upcoming"
                    :key="booking.id"
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm"
                >
                    <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-navy text-white"
                            >
                                <span class="text-sm font-black">{{ booking.flight.airline_code }}</span>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-black text-gray-900">
                                        {{ booking.flight.origin }}
                                        <span class="text-gray-400">→</span>
                                        {{ booking.flight.destination }}
                                    </p>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                        :class="statusMeta(booking.status).classes"
                                    >
                                        {{ statusMeta(booking.status).label }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ booking.flight.airline }} · {{ booking.flight.flight_number }} ·
                                    {{ booking.flight.departure_date_display }} · {{ booking.flight.departure_time }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Harga</p>
                                <p class="text-lg font-black text-teal-700">
                                    {{ formatRupiah(booking.total_price) }}
                                </p>
                            </div>
                            <template v-if="booking.status === 'pending'">
                                <Link
                                    :href="route('booking.payment', {
                                        bookingId: booking.id,
                                        pnr: booking.pnr_code,
                                        total: booking.total_price,
                                        flight: booking.flight.flight_number,
                                        passengers: booking.passenger_count,
                                        method: 'credit_card',
                                    })"
                                    class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-amber-600"
                                >
                                    Bayar Sekarang
                                </Link>
                            </template>
                            <button
                                v-if="booking.eticket_count > 0"
                                type="button"
                                @click="toggleExpanded(booking.id)"
                                class="rounded-xl border border-teal-200 bg-teal-50 px-5 py-2.5 text-sm font-bold text-teal-700 transition hover:bg-teal-100"
                            >
                                {{ expanded[booking.id] ? 'Tutup E-Ticket' : 'Lihat E-Ticket' }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="expanded[booking.id] && booking.eticket_count > 0"
                        class="border-t border-gray-100 bg-gray-50 px-6 py-5"
                    >
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                            E-Ticket ({{ booking.eticket_count }})
                        </p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="ticket in booking.etickets"
                                :key="ticket.eticket_number"
                                class="flex flex-col rounded-2xl border border-teal-200 bg-white p-4"
                            >
                                <span class="text-sm font-black text-teal-700">
                                    {{ ticket.eticket_number }}
                                </span>
                                <span class="mt-1 text-sm font-semibold text-gray-800">
                                    {{ ticket.passenger_name }}
                                </span>
                                <span class="mt-1 text-xs text-gray-500">
                                    Kursi {{ ticket.seat_number ?? '-' }} ·
                                    {{ booking.flight.flight_number }} ·
                                    {{ booking.flight.departure_date_display }} {{ booking.flight.departure_time }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- E-Ticket & Invoice -->
        <section id="eticket" class="mt-10 scroll-mt-24">
            <div>
                <h2 class="text-xl font-black tracking-tight text-gray-900">
                    E-Ticket & Invoice
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kumpulan bukti pembayaran dan tiket elektronik kamu.
                </p>
            </div>

            <div v-if="documents.length === 0" class="mt-5">
                <div
                    class="flex flex-col items-center rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-12 text-center"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 110 2v3a2 2 0 002 2h14a2 2 0 002-2v-3a1 1 0 110-2V7a2 2 0 00-2-2H5z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-800">Belum ada E-Ticket</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                        E-Ticket dan invoice akan muncul di sini setelah pemesanan dikonfirmasi.
                    </p>
                </div>
            </div>

            <div v-else class="mt-5 space-y-4">
                <div
                    v-for="booking in documents"
                    :key="'doc-' + booking.id"
                    class="rounded-3xl border border-gray-100 bg-white shadow-sm"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 p-6">
                        <div>
                            <p class="font-black text-gray-900">
                                {{ booking.flight.origin }} →
                                {{ booking.flight.destination }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ booking.flight.flight_number }} ·
                                {{ booking.flight.departure_date_display }} ·
                                {{ booking.flight.departure_time }} ·
                                {{ booking.pnr_code }}
                            </p>
                        </div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold"
                            :class="statusMeta(booking.status).classes"
                        >
                            {{ statusMeta(booking.status).label }}
                        </span>
                    </div>

                    <div
                        v-if="booking.eticket_count > 0"
                        class="border-t border-gray-100 bg-gray-50 px-6 py-4"
                    >
                        <div class="grid gap-3 lg:grid-cols-2">
                            <div
                                v-for="ticket in booking.etickets"
                                :key="booking.id + ticket.eticket_number"
                                class="flex items-center gap-4 rounded-2xl border border-teal-200 bg-white p-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal text-white"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 110 2v3a2 2 0 002 2h14a2 2 0 002-2v-3a1 1 0 110-2V7a2 2 0 00-2-2H5z"
                                        />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-teal-700">
                                        {{ ticket.eticket_number }}
                                    </p>
                                    <p class="truncate text-sm font-semibold text-gray-700">
                                        {{ ticket.passenger_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Kursi {{ ticket.seat_number ?? '-' }} · {{ booking.flight.departure_date_display }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="booking.invoice"
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-3"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                Invoice {{ booking.invoice }}
                            </span>
                            <span
                                v-if="booking.payment"
                                class="text-xs font-semibold"
                                :class="paymentMeta(booking.payment.status).classes"
                            >
                                {{ paymentMeta(booking.payment.status).label }} ·
                                {{ formatRupiah(booking.payment.amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- History -->
        <section v-if="history.length > 0" class="mt-10">
            <h2 class="text-xl font-black tracking-tight text-gray-900">
                Riwayat Pemesanan
            </h2>
            <div class="mt-5 space-y-3">
                <div
                    v-for="booking in history"
                    :key="'his-' + booking.id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-gray-100 bg-white px-6 py-5 shadow-sm"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-black text-gray-500"
                        >
                            {{ booking.flight.airline_code }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">
                                {{ booking.flight.origin }} → {{ booking.flight.destination }}
                            </p>
                            <p class="mt-0.5 text-sm text-gray-500">
                                {{ booking.flight.flight_number }} ·
                                {{ booking.flight.departure_date_display }} ·
                                {{ booking.pnr_code }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold"
                            :class="statusMeta(booking.status).classes"
                        >
                            {{ statusMeta(booking.status).label }}
                        </span>
                        <span class="text-sm font-black text-gray-700">
                            {{ formatRupiah(booking.total_price) }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>