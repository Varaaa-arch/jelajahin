<script setup lang="ts">
import { ref } from 'vue';
import { CalendarClock, ChevronDown, PencilLine, Printer, Settings2, XCircle } from 'lucide-vue-next';
import type { AdminOrderDetail } from '@/types/admin-order';

defineProps<{ order: AdminOrderDetail }>();

defineEmits<{
    (e: 'reschedule'): void;
    (e: 'modify'): void;
    (e: 'receipt', doc: 'eticket' | 'invoice'): void;
    (e: 'cancel'): void;
}>();

const receiptOpen = ref(false);
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
            <Settings2 class="h-5 w-5 text-teal-dark" />
            Actions
        </h2>

        <div class="mt-4 flex flex-col gap-2.5">
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-navy px-4 py-3 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                @click="$emit('reschedule')"
            >
                <CalendarClock class="h-4 w-4" />
                RESCHEDULE
            </button>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-800 bg-white px-4 py-3 text-xs font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                @click="$emit('modify')"
            >
                <PencilLine class="h-4 w-4" />
                MODIFY ORDER
            </button>
            <div class="relative">
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-800 bg-white px-4 py-3 text-xs font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                    @click="receiptOpen = !receiptOpen"
                >
                    <Printer class="h-4 w-4" />
                    PRINT RECEIPT
                    <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="receiptOpen ? 'rotate-180' : ''" />
                </button>
                <div
                    v-if="receiptOpen"
                    class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl"
                >
                    <button
                        type="button"
                        class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-gray-700 hover:bg-gray-50"
                        @click="$emit('receipt', 'eticket'); receiptOpen = false"
                    >
                        E-Ticket PDF
                    </button>
                    <button
                        type="button"
                        class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-gray-700 hover:bg-gray-50"
                        @click="$emit('receipt', 'invoice'); receiptOpen = false"
                    >
                        Invoice PDF
                    </button>
                </div>
            </div>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-100 px-4 py-3 text-xs font-bold tracking-wide text-red-700 transition hover:bg-red-600 hover:text-white"
                @click="$emit('cancel')"
            >
                <XCircle class="h-4 w-4" />
                CANCEL ORDER
            </button>
        </div>
    </section>
</template>
