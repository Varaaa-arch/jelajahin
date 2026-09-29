<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Plus } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FlightFilterBar from '@/Components/Admin/Flights/FlightFilterBar.vue';
import FlightTable from '@/Components/Admin/Flights/FlightTable.vue';
import FlightsPagination from '@/Components/Admin/Flights/FlightsPagination.vue';
import FlightsEmptyState from '@/Components/Admin/Flights/FlightsEmptyState.vue';
import FlightFormModal from '@/Components/Admin/Flights/FlightFormModal.vue';
import ConfirmDeleteModal from '@/Components/Admin/Flights/ConfirmDeleteModal.vue';
import type {
    AdminFlight,
    AdminFlightFilters,
    FlightAircraftOption,
    FlightOption,
    FlightRouteOption,
    PaginatedFlights,
} from '@/types/admin-flight';

const props = defineProps<{
    flights: PaginatedFlights;
    filters: AdminFlightFilters;
    airlines: FlightOption[];
    routes: FlightRouteOption[];
    aircrafts: FlightAircraftOption[];
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
const formMode = ref<'create' | 'edit'>('create');
const selected = ref<AdminFlight | null>(null);
const deleteTarget = ref<AdminFlight | null>(null);
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);
const deleteProcessing = ref(false);

function reload(patch: Partial<AdminFlightFilters> = {}, targetPage?: number): void {
    loading.value = true;
    router.get(
        '/admin/flights',
        {
            q: props.filters.q,
            airline_id: props.filters.airline_id,
            status_tab: props.filters.status_tab,
            sort: props.filters.sort,
            date_from: props.filters.date_from,
            date_to: props.filters.date_to,
            occupancy_min: props.filters.occupancy_min,
            ...patch,
            ...(targetPage ? { page: targetPage } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['flights', 'filters'],
            onFinish: () => (loading.value = false),
        },
    );
}

function resetFilters(): void {
    router.get(
        '/admin/flights',
        { status_tab: 'semua', sort: 'terbaru', occupancy_min: 0 },
        {
            preserveScroll: true,
            replace: true,
            only: ['flights', 'filters'],
        },
    );
}

function openCreate(): void {
    formMode.value = 'create';
    selected.value = null;
    formErrors.value = {};
    formOpen.value = true;
}

function openEdit(flight: AdminFlight): void {
    formMode.value = 'edit';
    selected.value = flight;
    formErrors.value = {};
    formOpen.value = true;
}

function submitForm(payload: Parameters<typeof router.post>[1]): void {
    formProcessing.value = true;
    formErrors.value = {};
    if (formMode.value === 'create') {
        router.post('/admin/flights', payload, {
            preserveScroll: true,
            onError: (e) => (formErrors.value = e as Record<string, string>),
            onSuccess: () => (formOpen.value = false),
            onFinish: () => (formProcessing.value = false),
        });
    } else if (selected.value) {
        router.put(`/admin/flights/${selected.value.id}`, payload, {
            preserveScroll: true,
            onError: (e) => (formErrors.value = e as Record<string, string>),
            onSuccess: () => (formOpen.value = false),
            onFinish: () => (formProcessing.value = false),
        });
    }
}

function confirmDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/admin/flights/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => (deleteTarget.value = null),
        onError: () => {
            toastTone.value = 'error';
            toastMessage.value = 'Gagal menghapus penerbangan.';
            showToast.value = true;
        },
        onFinish: () => (deleteProcessing.value = false),
    });
}

const offset = computed(() =>
    props.flights.from ? props.flights.from - 1 : 0,
);
const isEmpty = computed(() => !loading.value && props.flights.data.length === 0);
</script>

<template>
    <Head title="Kelola Penerbangan" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Penerbangan</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola jadwal, status, dan kapasitas penerbangan.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-navy px-4 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" />
                TAMBAH PENERBANGAN BARU
            </button>
        </div>

        <div class="mt-5">
            <FlightFilterBar :filters="filters" :airlines="airlines" @change="reload" @reset="resetFilters" />
        </div>

        <div class="mt-4">
            <FlightTable
                :flights="flights.data"
                :loading="loading"
                :offset="offset"
                @edit="openEdit"
                @delete="(f) => (deleteTarget = f)"
            />
            <FlightsEmptyState v-if="isEmpty" @reset="resetFilters" />
            <FlightsPagination v-else :meta="flights" @page="(p) => reload({}, p)" />
        </div>

        <FlightFormModal
            :open="formOpen"
            :mode="formMode"
            :flight="selected"
            :routes="routes"
            :aircrafts="aircrafts"
            :processing="formProcessing"
            :errors="formErrors"
            @close="formOpen = false"
            @submit="submitForm"
        />

        <ConfirmDeleteModal
            :open="!!deleteTarget"
            :flight="deleteTarget"
            :processing="deleteProcessing"
            @close="deleteTarget = null"
            @confirm="confirmDelete"
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
