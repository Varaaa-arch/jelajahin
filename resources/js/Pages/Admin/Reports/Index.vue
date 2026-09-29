<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CalendarRange, Download } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RevenueChart from '@/Components/Admin/RevenueChart.vue';
import BookingDonut from '@/Components/Admin/BookingDonut.vue';
import TopFlightsTable from '@/Components/Admin/TopFlightsTable.vue';
import { formatIDR } from '@/types/admin-order';
import { formatCompactIDR } from '@/types/admin-payment';
import type {
    BookingStatusSlice,
    OccupancyReport,
    PaymentSummaryRow,
    ReportFilters,
    ReportSummary,
    RevenueTrendPoint,
    TopFlight,
    TopRoute,
} from '@/types/admin-report';

const props = defineProps<{
    filters: ReportFilters;
    summary: ReportSummary;
    revenueTrend: RevenueTrendPoint;
    bookingsByStatus: BookingStatusSlice[];
    topRoutes: TopRoute[];
    topFlights: TopFlight[];
    paymentSummary: PaymentSummaryRow[];
    occupancy: OccupancyReport;
}>();

const loading = ref(false);

function reload(patch: Partial<ReportFilters> = {}): void {
    loading.value = true;
    router.get('/admin/reports', { date_from: props.filters.date_from, date_to: props.filters.date_to, ...patch }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => (loading.value = false),
    });
}

function csv(rows: string[][], filename: string): void {
    const text = rows.map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([text], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

function exportSection(kind: 'revenue' | 'status' | 'routes' | 'payments' | 'occupancy'): void {
    const range = `${props.filters.date_from}_${props.filters.date_to}`;
    if (kind === 'revenue') {
        csv(
            [['Period', 'Revenue (M IDR)'], ...props.revenueTrend.labels.map((l, i) => [l, String(props.revenueTrend.values[i])])],
            `report-revenue-${range}.csv`,
        );
    } else if (kind === 'status') {
        csv(
            [['Status', 'Count', 'Pct'], ...props.bookingsByStatus.map((s) => [s.label, String(s.count), String(s.pct)])],
            `report-status-${range}.csv`,
        );
    } else if (kind === 'routes') {
        csv(
            [['Route', 'Airline', 'Bookings', 'Revenue'], ...props.topRoutes.map((r) => [r.codes, r.airline, String(r.bookings), String(r.revenue)])],
            `report-routes-${range}.csv`,
        );
    } else if (kind === 'payments') {
        csv(
            [['Status', 'Count', 'Amount'], ...props.paymentSummary.map((p) => [p.status, String(p.total), String(p.amount)])],
            `report-payments-${range}.csv`,
        );
    } else {
        csv(
            [['Flight', 'Date', 'Booked', 'Total', 'Pct'], ...props.occupancy.flights.map((o) => [o.flight_number, o.departure_date ?? '', String(o.booked), String(o.total), String(o.pct)])],
            `report-occupancy-${range}.csv`,
        );
    }
}

const donutSlices = () => props.bookingsByStatus.map((s) => ({ label: s.label, value: s.pct, color: s.color }));

function clampPct(pct: number): number {
    return Math.min(Math.max(pct, 0), 100);
}
</script>

<template>
    <Head title="Reports" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Reports</h1>
                <p class="mt-1 text-sm text-gray-500">Sales, bookings, payments &amp; occupancy insights.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 self-start rounded-2xl border border-gray-200 bg-white px-4 py-3">
                <CalendarRange class="h-4 w-4 text-gray-500" />
                <input
                    :value="filters.date_from"
                    type="date"
                    aria-label="Dari tanggal"
                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm focus:border-teal focus:outline-none"
                    @change="reload({ date_from: ($event.target as HTMLInputElement).value })"
                />
                <span class="text-gray-400">-</span>
                <input
                    :value="filters.date_to"
                    type="date"
                    aria-label="Sampai tanggal"
                    class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm focus:border-teal focus:outline-none"
                    @change="reload({ date_to: ($event.target as HTMLInputElement).value })"
                />
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Revenue</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ formatCompactIDR(summary.revenue) }}</p>
                <p class="mt-1 text-xs text-gray-500">Settled payments</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Bookings</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ summary.bookings }}</p>
                <p class="mt-1 text-xs text-gray-500">Avg ticket {{ formatIDR(summary.avg_ticket) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Refunded</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ formatCompactIDR(summary.refunded) }}</p>
                <p class="mt-1 text-xs text-gray-500">Returned to customers</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Avg Occupancy</p>
                <p class="mt-2 text-2xl font-black text-gray-900">
                    {{ occupancy.average_pct === null ? '-' : `${occupancy.average_pct}%` }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Across listed flights</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 xl:col-span-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">Revenue Trend</h3>
                    <button type="button" class="inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline" @click="exportSection('revenue')">
                        <Download class="h-3.5 w-3.5" /> CSV
                    </button>
                </div>
                <div class="mt-4">
                    <RevenueChart :labels="revenueTrend.labels" :values="revenueTrend.values" />
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">Bookings by Status</h3>
                    <button type="button" class="inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline" @click="exportSection('status')">
                        <Download class="h-3.5 w-3.5" /> CSV
                    </button>
                </div>
                <div class="mt-4">
                    <BookingDonut :slices="donutSlices()" />
                </div>
                <ul class="mt-4 space-y-1.5 text-xs text-gray-600">
                    <li v-for="s in bookingsByStatus" :key="s.status" class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: s.color }" />
                            {{ s.label }}
                        </span>
                        <span class="font-bold text-gray-900">{{ s.count }} ({{ s.pct }}%)</span>
                    </li>
                    <li v-if="bookingsByStatus.length === 0" class="text-gray-400">Belum ada booking pada periode ini.</li>
                </ul>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-bold text-gray-900">Top Routes</h3>
                    <button type="button" class="inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline" @click="exportSection('routes')">
                        <Download class="h-3.5 w-3.5" /> CSV
                    </button>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-gray-500">
                            <th class="px-5 py-3 font-semibold">Route</th>
                            <th class="px-5 py-3 text-right font-semibold">Bookings</th>
                            <th class="px-5 py-3 text-right font-semibold">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="r in topRoutes" :key="r.codes + r.airline">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-gray-900">{{ r.route }}</p>
                                <p class="text-xs text-gray-500">{{ r.codes }} · {{ r.airline }}</p>
                            </td>
                            <td class="px-5 py-3 text-right font-bold">{{ r.bookings }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">{{ formatCompactIDR(r.revenue) }}</td>
                        </tr>
                        <tr v-if="topRoutes.length === 0">
                            <td colspan="3" class="px-5 py-8 text-center text-xs text-gray-400">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-bold text-gray-900">Payment Summary</h3>
                    <button type="button" class="inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline" @click="exportSection('payments')">
                        <Download class="h-3.5 w-3.5" /> CSV
                    </button>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-gray-500">
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Count</th>
                            <th class="px-5 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in paymentSummary" :key="p.status">
                            <td class="px-5 py-3 font-semibold capitalize text-gray-900">{{ p.status.replace('_', ' ') }}</td>
                            <td class="px-5 py-3 text-right">{{ p.total }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">{{ formatCompactIDR(p.amount) }}</td>
                        </tr>
                        <tr v-if="paymentSummary.length === 0">
                            <td colspan="3" class="px-5 py-8 text-center text-xs text-gray-400">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <TopFlightsTable :flights="topFlights" />
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-bold text-gray-900">Flight Occupancy</h3>
                    <button type="button" class="inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline" @click="exportSection('occupancy')">
                        <Download class="h-3.5 w-3.5" /> CSV
                    </button>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li v-for="o in occupancy.flights" :key="o.flight_number + (o.departure_date ?? '')" class="px-5 py-3">
                        <div class="flex items-center justify-between text-sm">
                            <p class="font-semibold text-gray-900">{{ o.flight_number }} <span class="font-normal text-gray-500">· {{ o.departure_date ?? '' }}</span></p>
                            <p class="text-xs font-bold text-gray-700">{{ o.booked }}/{{ o.total }} ({{ o.pct }}%)</p>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-teal" :style="{ width: `${clampPct(o.pct)}%` }" />
                        </div>
                    </li>
                    <li v-if="occupancy.flights.length === 0" class="px-5 py-8 text-center text-xs text-gray-400">
                        Belum ada data kursi pada periode ini.
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="loading" class="mt-4 text-center text-xs text-gray-500">Memuat laporan...</div>
    </AdminLayout>
</template>
