<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { TriangleAlert, X } from 'lucide-vue-next';
import { formatIDR, paymentBadge, paymentMethodLabel, type AdminPayment } from '@/types/admin-payment';

const props = defineProps<{
    open: boolean;
    payment: AdminPayment | null;
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'submit', payload: any): void;
}>();

const confirmAction = ref<'expired' | 'refunded' | null>(null);

watch(() => props.open, (v) => { if (v) confirmAction.value = null; }, { immediate: true });

const canExpire = computed(() => props.payment?.status === 'pending');
const canRefund = computed(() => props.payment?.status === 'success');

const ACTION_COPY: Record<string, { title: string; desc: string; button: string; danger: boolean }> = {
    expired: {
        title: 'Expire payment ini?',
        desc: 'Payment menjadi expired dan booking terkait ikut dibatalkan (kursi dikembalikan).',
        button: 'YA, EXPIRE',
        danger: false,
    },
    refunded: {
        title: 'Refund payment ini?',
        desc: 'Payment menjadi refunded dan booking terkait menjadi refunded (kursi dikembalikan).',
        button: 'YA, REFUND',
        danger: true,
    },
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="emit('close')"
            >
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <div>
                            <h3 class="text-base font-black text-gray-900">Detail Transaksi</h3>
                            <p class="mt-0.5 font-mono text-xs text-gray-500">{{ payment?.transaction_id }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                            aria-label="Tutup"
                            @click="emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-3 px-6 py-5 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Booking Ref</span>
                            <a :href="`/admin/orders/${payment?.booking_id}`" class="font-bold text-teal-dark hover:underline">
                                {{ payment?.pnr }}
                            </a>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Customer</span>
                            <span class="text-right font-medium text-gray-900">
                                {{ payment?.booker_name }}
                                <span class="block text-xs font-normal text-gray-500">{{ payment?.booker_email }}</span>
                            </span>
                        </div>
                        <div v-if="payment?.origin_code" class="flex justify-between gap-3">
                            <span class="text-gray-500">Route</span>
                            <span class="font-medium text-gray-900">{{ payment.origin_code }} → {{ payment.destination_code }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Method</span>
                            <span class="font-medium text-gray-900">{{ paymentMethodLabel(payment?.payment_method) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Amount</span>
                            <span class="font-black text-gray-900">{{ formatIDR(payment?.amount ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-500">Status</span>
                            <span
                                class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold"
                                :class="paymentBadge(payment?.status ?? '').classes"
                            >
                                {{ paymentBadge(payment?.status ?? '').label }}
                            </span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Paid At</span>
                            <span class="font-medium text-gray-900">{{ payment?.paid_at ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Expires At</span>
                            <span class="font-medium text-gray-900">{{ payment?.expires_at ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">Created</span>
                            <span class="font-medium text-gray-900">{{ payment?.created_at ?? '-' }}</span>
                        </div>

                        <div v-if="confirmAction" class="flex items-start gap-2 rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                            <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
                            <div>
                                <p class="font-bold">{{ ACTION_COPY[confirmAction].title }}</p>
                                <p class="mt-0.5 font-normal">{{ ACTION_COPY[confirmAction].desc }}</p>
                            </div>
                        </div>

                        <p v-if="errors?.status" class="text-xs text-red-600">{{ errors.status }}</p>

                        <div class="flex flex-wrap justify-end gap-2 pt-1">
                            <button
                                v-if="canExpire && confirmAction !== 'expired'"
                                type="button"
                                class="rounded-xl border border-amber-500 px-4 py-2.5 text-xs font-bold text-amber-700 hover:bg-amber-50"
                                @click="confirmAction = 'expired'"
                            >
                                EXPIRE
                            </button>
                            <button
                                v-if="canRefund && confirmAction !== 'refunded'"
                                type="button"
                                class="rounded-xl border border-red-500 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50"
                                @click="confirmAction = 'refunded'"
                            >
                                REFUND
                            </button>
                            <button
                                v-if="confirmAction"
                                type="button"
                                :disabled="processing"
                                class="rounded-xl px-4 py-2.5 text-xs font-bold text-white disabled:opacity-50"
                                :class="ACTION_COPY[confirmAction].danger ? 'bg-red-600 hover:bg-red-700' : 'bg-navy hover:bg-navy-mid'"
                                @click="emit('submit', { status: confirmAction })"
                            >
                                {{ processing ? 'MEMPROSES...' : ACTION_COPY[confirmAction].button }}
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50"
                                @click="emit('close')"
                            >
                                TUTUP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
