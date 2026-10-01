<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { TriangleAlert, X } from 'lucide-vue-next';
import { ORDER_TRANSITIONS, type AdminOrder } from '@/types/admin-order';

const props = defineProps<{
    open: boolean;
    order: AdminOrder | null;
    mode: 'status' | 'cancel';
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit', payload: { status: string; rejection_reason?: string }): void;
}>();

const selected = ref('');
const rejectionReason = ref('');

const showReason = computed(() => {
    return (
        props.order?.order_status === 'awaiting_confirmation' &&
        selected.value === 'cancelled'
    );
});

const options = computed(() => {
    if (!props.order) return [];
    const allowed = ORDER_TRANSITIONS[props.order.order_status] ?? [];
    if (props.mode === 'cancel') {
        return allowed.includes('cancelled') ? ['cancelled'] : [];
    }
    return allowed;
});

const DESCRIPTIONS: Record<string, string> = {
    awaiting_confirmation: 'Pembayaran sukses. Menunggu persetujuan admin sebelum tiket teraktif.',
    confirmed: 'Konfirmasi pesanan. Kursi ter-booked & e-tiket tergenerate.',
    completed: 'Tandai perjalanan selesai. Status terminal.',
    cancelled: 'Batalkan pesanan dan kembalikan kursi ke tersedia. Jika menolak pesanan yang sudah dibayar, refund otomatis dibuat.',
    refund_requested: 'Catat permintaan refund dari penumpang.',
    refunded: 'Selesaikan refund. Kursi dikembalikan & payment jadi refunded.',
};

watch(() => props.open, (v) => {
    if (v) {
        selected.value = props.mode === 'cancel' ? 'cancelled' : '';
        rejectionReason.value = '';
    }
}, { immediate: true });

function onSubmit(): void {
    if (!selected.value) return;
    emit('submit', {
        status: selected.value,
        ...(showReason.value && rejectionReason.value.trim()
            ? { rejection_reason: rejectionReason.value.trim() }
            : {}),
    });
}

const title = computed(() => (props.mode === 'cancel' ? 'Cancel Pesanan' : 'Ubah Status Pesanan'));
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="emit('close')"
            >
                <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-black text-gray-900">{{ title }}</h3>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                            aria-label="Tutup"
                            @click="emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form class="space-y-3 px-6 py-5" @submit.prevent="onSubmit">
                        <p class="text-xs text-gray-500">
                            {{ order?.pnr }} · {{ order?.booker_name }} · status saat ini:
                            <span class="font-bold text-gray-800">{{ order?.order_status }}</span>
                        </p>

                        <div v-if="options.length === 0" class="flex items-start gap-2 rounded-xl bg-gray-100 px-4 py-3 text-xs font-semibold text-gray-600">
                            <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
                            Tidak ada transisi yang diizinkan dari status ini.
                        </div>

                        <div v-else class="space-y-2">
                            <label
                                v-for="s in options"
                                :key="s"
                                class="block cursor-pointer rounded-xl border px-4 py-3 text-sm transition"
                                :class="selected === s ? (s === 'cancelled' ? 'border-red-500 bg-red-50' : 'border-navy bg-navy/5') : 'border-gray-200 hover:border-navy/50'"
                            >
                                <span class="flex items-center gap-2">
                                    <input v-model="selected" type="radio" :value="s" class="accent-[#0b2a3a]" />
                                    <span class="font-bold capitalize text-gray-900">{{ s.replace('_', ' ') }}</span>
                                </span>
                                <span class="mt-1 block pl-6 text-xs text-gray-500">{{ DESCRIPTIONS[s] }}</span>
                            </label>
                        </div>

                        <p v-if="errors?.status" class="text-xs text-red-600">{{ errors.status }}</p>

                        <div v-if="showReason" class="space-y-1.5">
                            <label for="rejection-reason" class="text-xs font-bold text-gray-700">
                                Alasan penolakan <span class="font-normal text-gray-400">(opsional, dikirim ke user + refund)</span>
                            </label>
                            <textarea
                                id="rejection-reason"
                                v-model="rejectionReason"
                                rows="3"
                                maxlength="1000"
                                placeholder="cth. Kursi tidak tersedia / data penumpang tidak valid..."
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-navy focus:outline-none"
                            />
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50" @click="emit('close')">
                                BATAL
                            </button>
                            <button
                                type="submit"
                                :disabled="processing || !selected"
                                class="rounded-xl px-4 py-2.5 text-xs font-bold text-white disabled:opacity-50"
                                :class="selected === 'cancelled' ? 'bg-red-600 hover:bg-red-700' : 'bg-navy hover:bg-navy-mid'"
                            >
                                {{ processing ? 'MEMPROSES...' : mode === 'cancel' ? 'YA, BATALKAN' : 'SIMPAN STATUS' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
