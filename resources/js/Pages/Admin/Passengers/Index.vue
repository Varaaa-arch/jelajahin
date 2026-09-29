<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Download } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PassengerFilterBar from '@/Components/Admin/Passengers/PassengerFilterBar.vue';
import PassengerTable from '@/Components/Admin/Passengers/PassengerTable.vue';
import PassengerFormModal from '@/Components/Admin/Passengers/PassengerFormModal.vue';
import PassengersEmptyState from '@/Components/Admin/Passengers/PassengersEmptyState.vue';
import FlightsPagination from '@/Components/Admin/Flights/FlightsPagination.vue';
import type {
    AdminPassenger,
    AdminPassengerFilters,
    PaginatedPassengers,
} from '@/types/admin-passenger';

const props = defineProps<{
    passengers: PaginatedPassengers;
    filters: AdminPassengerFilters;
    nationalities: string[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);

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
const formOpen = ref(false);
const selected = ref<AdminPassenger | null>(null);
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);

function queryParams(patch: Partial<AdminPassengerFilters> = {}, targetPage?: number) {
    return {
        q: props.filters.q,
        status: props.filters.status,
        check_in: props.filters.check_in,
        nationality: props.filters.nationality,
        payment: props.filters.payment,
        sort: props.filters.sort,
        date_from: props.filters.date_from,
        date_to: props.filters.date_to,
        per_page: props.filters.per_page,
        ...patch,
        ...(targetPage ? { page: targetPage } : {}),
    };
}

function reload(patch: Partial<AdminPassengerFilters> = {}, targetPage?: number): void {
    loading.value = true;
    router.get('/admin/passengers', queryParams(patch, targetPage), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['passengers', 'filters'],
        onFinish: () => (loading.value = false),
    });
}

function resetFilters(): void {
    router.get(
        '/admin/passengers',
        { per_page: props.filters.per_page },
        { preserveScroll: true, replace: true, only: ['passengers', 'filters'] },
    );
}

function openEdit(passenger: AdminPassenger): void {
    selected.value = passenger;
    formErrors.value = {};
    formOpen.value = true;
}

function submitForm(payload: Parameters<typeof router.post>[1]): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/passengers/${selected.value.id}`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (formOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function exportCsv(): void {
    const rows = [
        ['Name', 'Email', 'Nationality', 'Passport', 'PNR', 'Flight', 'Departure', 'Booking Status', 'Payment', 'Check-in', 'Seat'],
        ...props.passengers.data.map((p) => [
            p.name,
            p.email,
            p.nationality ?? '',
            p.passport_masked,
            p.pnr,
            p.flight_number,
            p.departure_date ?? '',
            p.booking_status,
            p.payment_status,
            p.check_in_status,
            p.seat_number ?? '',
        ]),
    ];
    const csv = rows
        .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `passengers-page-${props.passengers.current_page}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toastTone.value = 'success';
    toastMessage.value = `${props.passengers.data.length} data penumpang diekspor ke CSV.`;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 4200);
}

const offset = computed(() => (props.passengers.from ? props.passengers.from - 1 : 0));
const isEmpty = computed(() => !loading.value && props.passengers.data.length === 0);
</script>

<template>
    <Head title="Kelola Penumpang" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Penumpang</h1>
                <p class="mt-1 text-sm text-gray-500">Manage and monitor passenger manifests and details.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-navy px-4 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                @click="exportCsv"
            >
                <Download class="h-4 w-4" />
                EXPORT DATA
            </button>
        </div>

        <div class="mt-5">
            <PassengerFilterBar
                :filters="filters"
                :nationalities="nationalities"
                @change="reload"
                @reset="resetFilters"
            />
        </div>

        <div class="mt-4">
            <PassengerTable
                :passengers="passengers.data"
                :loading="loading"
                :offset="offset"
                @edit="openEdit"
            />
            <PassengersEmptyState v-if="isEmpty" @reset="resetFilters" />
            <FlightsPagination
                v-else
                :meta="passengers"
                item-label="penumpang"
                show-per-page
                @page="(p) => reload({}, p)"
                @per-page="(n) => reload({ per_page: n })"
            />
        </div>

        <PassengerFormModal
            :open="formOpen"
            :passenger="selected"
            :processing="formProcessing"
            :errors="formErrors"
            @close="formOpen = false"
            @submit="submitForm"
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
