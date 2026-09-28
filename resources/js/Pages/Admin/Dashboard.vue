<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, Download } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard, { type StatItem } from '@/Components/Admin/StatCard.vue';
import RevenueChart from '@/Components/Admin/RevenueChart.vue';
import BookingDonut, { type DonutSlice } from '@/Components/Admin/BookingDonut.vue';
import TopFlightsTable, { type TopFlight } from '@/Components/Admin/TopFlightsTable.vue';
import RecentTransactionsTable, { type Txn } from '@/Components/Admin/RecentTransactionsTable.vue';
import SystemAlerts, { type AlertItem } from '@/Components/Admin/SystemAlerts.vue';
import QuickActions from '@/Components/Admin/QuickActions.vue';

const props = defineProps<{
    stats: StatItem[];
    revenueTrend: { labels: string[]; values: number[]; unit: string };
    bookingsByStatus: DonutSlice[];
    topFlights: TopFlight[];
    recentTransactions: Txn[];
    alerts: AlertItem[];
}>();

const periods = ['Today', 'This Week', 'This Month', 'This Year', 'Custom'] as const;
const activePeriod = ref<(typeof periods)[number]>('This Month');

function exportReport(): void {
    const rows = [
        ['Metric', 'Value'],
        ...props.stats.map((s) => [s.label, s.value]),
        [],
        ['Route', 'Flight No', 'Bookings'],
        ...props.topFlights.map((f) => [f.route, f.flightNo, String(f.bookings)]),
        [],
        ['ID', 'PNR', 'Passenger', 'Amount'],
        ...props.recentTransactions.map((t) => [t.id, t.pnr, t.passenger, t.amount]),
    ];
    const csv = rows.map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `admin-report-${activePeriod.value.toLowerCase().replace(/\s+/g, '-')}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

const donutLegend = computed(() => props.bookingsByStatus);
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <!-- Header + filter -->
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Performance Overview</h1>
                <p class="mt-1 text-sm text-gray-500">Real-time metrics and system status.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex flex-wrap items-center gap-1 rounded-xl border border-gray-300 bg-white p-1">
                    <button
                        v-for="p in periods"
                        :key="p"
                        type="button"
                        @click="activePeriod = p"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="
                            activePeriod === p
                                ? 'border border-gray-300 bg-white text-gray-900 shadow-sm'
                                : 'text-gray-500 hover:text-gray-800'
                        "
                    >
                        {{ p }}
                        <Calendar v-if="p === 'Custom'" class="h-3.5 w-3.5" />
                    </button>
                </div>
                <button
                    type="button"
                    @click="exportReport"
                    class="inline-flex items-center gap-2 rounded-lg bg-navy px-4 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                >
                    <Download class="h-4 w-4" />
                    EXPORT REPORT
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <StatCard v-for="s in stats" :key="s.key" :stat="s" />
        </div>

        <!-- Charts -->
        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 xl:col-span-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">Revenue Trend</h3>
                    <span class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                        Last 30 Days
                    </span>
                </div>
                <div class="mt-4">
                    <RevenueChart :labels="revenueTrend.labels" :values="revenueTrend.values" />
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 xl:col-span-2">
                <h3 class="text-base font-bold text-gray-900">Bookings by Status</h3>
                <div class="mt-4">
                    <BookingDonut :slices="bookingsByStatus" />
                </div>
                <div class="mt-4 flex items-center justify-center gap-5 text-xs">
                    <span v-for="s in donutLegend" :key="s.label" class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: s.color }" />
                        <span class="text-gray-700">
                            {{ s.label }}<br />
                            <strong class="text-gray-900">{{ s.value }}%</strong>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <TopFlightsTable :flights="topFlights" />
            <RecentTransactionsTable :transactions="recentTransactions" />
        </div>

        <!-- Alerts -->
        <div class="mt-6">
            <SystemAlerts :alerts="alerts" />
        </div>

        <!-- Quick actions -->
        <div class="mt-4">
            <QuickActions />
        </div>
    </AdminLayout>
</template>
