<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Download } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaymentStatCards from '@/Components/Admin/Payments/PaymentStatCards.vue';
import PaymentFilterBar from '@/Components/Admin/Payments/PaymentFilterBar.vue';
import PaymentTable from '@/Components/Admin/Payments/PaymentTable.vue';
import PaymentDetailModal from '@/Components/Admin/Payments/PaymentDetailModal.vue';
import PaymentRecon from '@/Components/Admin/Payments/PaymentRecon.vue';
import PaymentMethods from '@/Components/Admin/Payments/PaymentMethods.vue';
import FlightsPagination from '@/Components/Admin/Flights/FlightsPagination.vue';
import { formatIDR } from '@/types/admin-payment';
import type {
    AdminPayment,
    AdminPaymentFilters,
    PaginatedPayments,
    PaymentMethodSummary,
    PaymentRecon as PaymentReconData,
    PaymentStats,
} from '@/types/admin-payment';

const props = defineProps<{
    payments: PaginatedPayments;
    filters: AdminPaymentFilters;
    stats: PaymentStats;
    methods: PaymentMethodSummary[];
    recon: PaymentReconData;
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
const selected = ref<AdminPayment | null>(null);
const detailOpen = ref(false);
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);

function queryParams(patch: Partial<AdminPaymentFilters> = {}, targetPage?: number) {
    return {
        q: props.filters.q,
        status: props.filters.status,
        method: props.filters.method,
        date_from: props.filters.date_from,
        date_to: props.filters.date_to,
        sort: props.filters.sort,
        per_page: props.filters.per_page,
        recon_date: props.filters.recon_date,
        ...patch,
        ...(targetPage ? { page: targetPage } : {}),
    };
}

function reload(patch: Partial<AdminPaymentFilters> = {}, targetPage?: number): void {
    loading.value = true;
    router.get('/admin/payments', queryParams(patch, targetPage), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['payments', 'filters', 'stats', 'methods', 'recon'],
        onFinish: () => (loading.value = false),
    });
}

function resetFilters(): void {
    router.get(
        '/admin/payments',
        { per_page: props.filters.per_page, recon_date: props.filters.recon_date },
        { preserveScroll: true, replace: true, only: ['payments', 'filters', 'stats', 'methods', 'recon'] },
    );
}

function openDetail(payment: AdminPayment): void {
    selected.value = payment;
    formErrors.value = {};
    detailOpen.value = true;
}

function submitStatus(payload: { status: string }): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/payments/${selected.value.id}`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (detailOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function exportCsv(): void {
    const rows = [
        ['Transaction ID', 'PNR', 'Customer', 'Amount', 'Method', 'Status', 'Paid At', 'Created'],
        ...props.payments.data.map((p) => [
            p.transaction_id,
            p.pnr,
            p.booker_email,
            String(p.amount),
            p.payment_method ?? '',
            p.status,
            p.paid_at ?? '',
            p.created_at ?? '',
        ]),
    ];
    const csv = rows
        .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `payments-page-${props.payments.current_page}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toastTone.value = 'success';
    toastMessage.value = `${props.payments.data.length} data pembayaran diekspor ke CSV.`;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 4200);
}

function exportRecon(): void {
    const rows = [
        ['Date', 'Expected Revenue', 'Actual Settled', 'Discrepancy'],
        [props.recon.date, String(props.recon.expected), String(props.recon.actual), String(props.recon.discrepancy)],
    ];
    const csv = rows
        .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reconciliation-${props.recon.date}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toastTone.value = 'success';
    toastMessage.value = `Laporan rekonsiliasi ${props.recon.date} diekspor (${formatIDR(props.recon.actual)} settled).`;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 4200);
}

const offset = computed(() => (props.payments.from ? props.payments.from - 1 : 0));
</script>

<template>
    <Head title="Kelola Pembayaran" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Pembayaran</h1>
                <p class="mt-1 text-sm text-gray-500">Overview and management of all financial transactions.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-navy px-4 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                @click="exportCsv"
            >
                <Download class="h-4 w-4" />
                EXPORT PAYMENTS
            </button>
        </div>

        <div class="mt-5">
            <PaymentStatCards :stats="stats" />
        </div>

        <div class="mt-4">
            <PaymentFilterBar :filters="filters" :methods="methods" @change="reload" @reset="resetFilters" />
        </div>

        <div class="mt-4">
            <PaymentTable :payments="payments.data" :loading="loading" :offset="offset" @view="openDetail" />
            <FlightsPagination
                :meta="payments"
                item-label="transaksi"
                show-per-page
                @page="(p) => reload({}, p)"
                @per-page="(n) => reload({ per_page: n })"
            />
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <PaymentRecon :recon="recon" @change="(d) => reload({ recon_date: d })" @export="exportRecon" />
            <PaymentMethods :methods="methods" />
        </div>

        <PaymentDetailModal
            :open="detailOpen"
            :payment="selected"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="detailOpen = false"
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
