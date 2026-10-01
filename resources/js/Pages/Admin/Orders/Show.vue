<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, CircleCheckBig } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import OrderFlightInfo from '@/Components/Admin/Orders/OrderFlightInfo.vue';
import OrderPassengers from '@/Components/Admin/Orders/OrderPassengers.vue';
import OrderAddons from '@/Components/Admin/Orders/OrderAddons.vue';
import OrderPrice from '@/Components/Admin/Orders/OrderPrice.vue';
import OrderPayment from '@/Components/Admin/Orders/OrderPayment.vue';
import OrderActions from '@/Components/Admin/Orders/OrderActions.vue';
import OrderModifyModal from '@/Components/Admin/Orders/OrderModifyModal.vue';
import OrderRescheduleModal from '@/Components/Admin/Orders/OrderRescheduleModal.vue';
import OrderStatusModal from '@/Components/Admin/Orders/OrderStatusModal.vue';
import {
    ORDER_STATUS_META,
    type AdminOrderDetail,
    type RescheduleFlightOption,
} from '@/types/admin-order';

const props = defineProps<{
    order: AdminOrderDetail;
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

const modifyOpen = ref(false);
const rescheduleOpen = ref(false);
const statusOpen = ref(false);
const statusMode = ref<'status' | 'cancel'>('status');
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);

const statusMeta = computed(
    () => ORDER_STATUS_META[props.order.order_status] ?? { label: props.order.order_status, classes: 'bg-gray-200/70 text-gray-700' },
);

function resetAndOpen(target: 'modify' | 'reschedule' | 'status' | 'cancel'): void {
    formErrors.value = {};
    modifyOpen.value = target === 'modify';
    rescheduleOpen.value = target === 'reschedule';
    if (target === 'status' || target === 'cancel') {
        statusMode.value = target;
        statusOpen.value = true;
    }
}

function putAction(url: string, payload: Record<string, unknown>, onOk: () => void): void {
    formProcessing.value = true;
    formErrors.value = {};
    router.put(url, { ...payload, redirect_to: 'show' }, {
        preserveScroll: true,
        only: ['order', 'rescheduleFlights', 'flash', 'errors'],
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: onOk,
        onFinish: () => (formProcessing.value = false),
    });
}

function viewReceipt(doc: 'eticket' | 'invoice'): void {
    window.open(`/admin/orders/${props.order.id}/receipt?doc=${doc}`, '_blank');
}

function approveOrder(): void {
    putAction(`/admin/orders/${props.order.id}/status`, { status: 'confirmed' }, () => {});
}
</script>

<template>
    <Head :title="`Pesanan #${order.pnr}`" />

    <AdminLayout>
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500" aria-label="Breadcrumb">
            <Link href="/admin/dashboard" class="hover:text-navy">Dashboard</Link>
            <span aria-hidden="true">›</span>
            <Link href="/admin/orders" class="hover:text-navy">Manage Orders</Link>
            <span aria-hidden="true">›</span>
            <span class="border-b-2 border-navy pb-0.5 font-bold text-navy">Order #{{ order.pnr }}</span>
        </nav>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Pesanan #{{ order.pnr }}</h1>
                <p class="mt-1 text-sm text-gray-500">Placed on {{ order.placed_at ?? '-' }}</p>
            </div>
            <span
                class="inline-flex items-center gap-1.5 self-start rounded-full border border-teal/30 bg-teal/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wide"
                :class="statusMeta.classes"
            >
                <CircleCheckBig class="h-4 w-4" />
                {{ statusMeta.label }}
            </span>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="flex flex-col gap-4 xl:col-span-2">
                <OrderFlightInfo :order="order" />
                <OrderPassengers :order="order" />
                <OrderAddons :order="order" />
            </div>
            <div class="flex flex-col gap-4">
                <OrderPrice :order="order" />
                <OrderPayment :order="order" />
                <OrderActions
                    :order="order"
                    @reschedule="resetAndOpen('reschedule')"
                    @modify="resetAndOpen('modify')"
                    @receipt="viewReceipt"
                    @cancel="resetAndOpen('cancel')"
                    @approve="approveOrder"
                />
            </div>
        </div>

        <OrderModifyModal
            :open="modifyOpen"
            :order="order"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="modifyOpen = false"
            @submit="(p) => putAction(`/admin/orders/${order.id}`, p, () => (modifyOpen = false))"
        />

        <OrderRescheduleModal
            :open="rescheduleOpen"
            :order="order"
            :flights="rescheduleFlights"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="rescheduleOpen = false"
            @submit="(p) => putAction(`/admin/orders/${order.id}/reschedule`, p, () => (rescheduleOpen = false))"
        />

        <OrderStatusModal
            :open="statusOpen"
            :order="order"
            :mode="statusMode"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="statusOpen = false"
            @submit="(p) => putAction(`/admin/orders/${order.id}/status`, p, () => (statusOpen = false))"
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
