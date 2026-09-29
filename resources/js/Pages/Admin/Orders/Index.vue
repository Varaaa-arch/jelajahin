<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Download } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import OrderFilterBar from '@/Components/Admin/Orders/OrderFilterBar.vue';
import OrderTable from '@/Components/Admin/Orders/OrderTable.vue';
import OrderModifyModal from '@/Components/Admin/Orders/OrderModifyModal.vue';
import OrderRescheduleModal from '@/Components/Admin/Orders/OrderRescheduleModal.vue';
import OrderStatusModal from '@/Components/Admin/Orders/OrderStatusModal.vue';
import FlightsPagination from '@/Components/Admin/Flights/FlightsPagination.vue';
import type {
    AdminOrder,
    AdminOrderFilters,
    AirlineOption,
    PaginatedOrders,
    RescheduleFlightOption,
    RouteOption,
} from '@/types/admin-order';

const props = defineProps<{
    orders: PaginatedOrders;
    filters: AdminOrderFilters;
    airlines: AirlineOption[];
    routes: RouteOption[];
    rescheduleFlights: RescheduleFlightOption[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);
const pageErrors = computed(() => (page.props.errors as Record<string, string> | undefined) ?? {});

const showToast = ref(false);
const toastMessage = ref('');
const toastTone = ref<'success' | 'error'>('success');

watch([flashSuccess, flashError], ([s, e]) => {
    if (s || e) {
        toastTone.value = s ? 'success' : 'error';
        toastMessage.value = (s ?? e) as string;
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 4200);
    }
}, { immediate: true });

const loading = ref(false);
const selected = ref<AdminOrder | null>(null);
const modifyOpen = ref(false);
const rescheduleOpen = ref(false);
const statusOpen = ref(false);
const statusMode = ref<'status' | 'cancel'>('status');
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);

function queryParams(patch: Partial<AdminOrderFilters> = {}, targetPage?: number) {
    return {
        q: props.filters.q,
        order_status: props.filters.order_status,
        payment_status: props.filters.payment_status,
        airline_id: props.filters.airline_id,
        route_id: props.filters.route_id,
        date_from: props.filters.date_from,
        date_to: props.filters.date_to,
        sort: props.filters.sort,
        per_page: props.filters.per_page,
        ...patch,
        ...(targetPage ? { page: targetPage } : {}),
    };
}

function reload(patch: Partial<AdminOrderFilters> = {}, targetPage?: number): void {
    loading.value = true;
    router.get('/admin/orders', queryParams(patch, targetPage), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['orders', 'filters'],
        onFinish: () => (loading.value = false),
    });
}

function resetFilters(): void {
    router.get(
        '/admin/orders',
        { per_page: props.filters.per_page },
        { preserveScroll: true, replace: true, only: ['orders', 'filters'] },
    );
}

function openModify(order: AdminOrder): void {
    selected.value = order;
    formErrors.value = {};
    modifyOpen.value = true;
}

function openReschedule(order: AdminOrder): void {
    selected.value = order;
    formErrors.value = {};
    rescheduleOpen.value = true;
}

function openStatus(order: AdminOrder): void {
    selected.value = order;
    statusMode.value = 'status';
    formErrors.value = {};
    statusOpen.value = true;
}

function openCancel(order: AdminOrder): void {
    selected.value = order;
    statusMode.value = 'cancel';
    formErrors.value = {};
    statusOpen.value = true;
}

function submitModify(payload: Record<string, unknown>): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/orders/${selected.value.id}`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (modifyOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function submitReschedule(payload: { new_flight_id: string }): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/orders/${selected.value.id}/reschedule`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (rescheduleOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function submitStatus(payload: { status: string }): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/orders/${selected.value.id}/status`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (statusOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function viewReceipt(order: AdminOrder, doc: 'eticket' | 'invoice'): void {
    window.open(`/admin/orders/${order.id}/receipt?doc=${doc}`, '_blank');
}

function exportCsv(): void {
    const rows = [
        ['PNR', 'Passenger', 'Email', 'Route', 'Flight', 'Flight Date', 'Amount', 'Payment', 'Status'],
        ...props.orders.data.map((o) => [
            o.pnr,
            o.booker_name,
            o.booker_email,
            `${o.origin_code} to ${o.destination_code}`,
            o.flight_number,
            o.departure_date ?? '',
            String(o.total_price),
            o.payment_status,
            o.order_status,
        ]),
    ];
    const csv = rows
        .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `orders-page-${props.orders.current_page}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toastTone.value = 'success';
    toastMessage.value = `${props.orders.data.length} data pesanan diekspor ke CSV.`;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 4200);
}

const offset = computed(() => (props.orders.from ? props.orders.from - 1 : 0));
const isEmpty = computed(() => !loading.value && props.orders.data.length === 0);
</script>

<template>
    <Head title="Kelola Pesanan" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Pesanan</h1>
                <p class="mt-1 text-sm text-gray-500">Centralized oversight of all flight bookings and transactions.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 self-start rounded-xl border border-gray-800 bg-white px-4 py-2.5 text-xs font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                @click="exportCsv"
            >
                <Download class="h-4 w-4" />
                EXPORT ORDERS
            </button>
        </div>

        <div class="mt-5">
            <OrderFilterBar
                :filters="filters"
                :airlines="airlines"
                :routes="routes"
                @change="reload"
                @reset="resetFilters"
            />
        </div>

        <div class="mt-4">
            <OrderTable
                :orders="orders.data"
                :loading="loading"
                :offset="offset"
                @receipt="viewReceipt"
                @modify="openModify"
                @cancel="openCancel"
                @reschedule="openReschedule"
                @status="openStatus"
            />
            <div v-if="isEmpty" class="rounded-b-2xl border border-t-0 border-gray-200 bg-white px-5 py-8 text-center">
                <button type="button" class="text-xs font-bold text-teal-dark hover:underline" @click="resetFilters">
                    Reset semua filter
                </button>
            </div>
            <FlightsPagination
                v-else
                :meta="orders"
                item-label="pesanan"
                show-per-page
                @page="(p) => reload({}, p)"
                @per-page="(n) => reload({ per_page: n })"
            />
        </div>

        <OrderModifyModal
            :open="modifyOpen"
            :order="selected"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="modifyOpen = false"
            @submit="submitModify"
        />

        <OrderRescheduleModal
            :open="rescheduleOpen"
            :order="selected"
            :flights="rescheduleFlights"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="rescheduleOpen = false"
            @submit="submitReschedule"
        />

        <OrderStatusModal
            :open="statusOpen"
            :order="selected"
            :mode="statusMode"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="statusOpen = false"
            @submit="submitStatus"
        />

        <Transition name="toast-slide">
            <div
                v-if="showToast"
                class="fixed bottom-6 right-6 z-[60] flex max-w-sm items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 shadow-2xl"
            >
                <CircleCheck v-if="toastTone === 'success'" class="mt-0.5 h-5 w-5 shrink-0 text-teal-dark" />
                <CircleAlert v-else class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                <div class="text-sm">
                    <p class="font-bold text-gray-900">{{ toastTone === 'success' ? 'Berhasil' : 'Gagal' }}</p>
                    <p class="mt-0.5 text-gray-600">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.toast-slide-enter-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.toast-slide-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}
.toast-slide-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.97);
}
.toast-slide-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
