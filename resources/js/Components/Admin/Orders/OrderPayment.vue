<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next';
import { PAYMENT_STATUS_META, type AdminOrderDetail } from '@/types/admin-order';

defineProps<{ order: AdminOrderDetail }>();

function payMeta(status: string): { label: string; classes: string } {
    return PAYMENT_STATUS_META[status] ?? { label: status, classes: 'bg-gray-200/70 text-gray-700' };
}

function methodLabel(method?: string | null): string {
    if (!method) return 'Unknown method';
    return method
        .split('_')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
            <CreditCard class="h-5 w-5 text-teal-dark" />
            Payment Information
        </h2>

        <div v-if="order.payments.length === 0" class="mt-4 text-sm text-gray-400">
            Belum ada riwayat pembayaran.
        </div>

        <div v-else class="mt-4 space-y-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 min-w-14 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-gray-50 px-2 text-xs font-black text-gray-800">
                    {{ (order.payments[0].payment_method ?? 'N/A').slice(0, 10).toUpperCase() }}
                </span>
                <div class="text-sm">
                    <p class="font-bold text-gray-900">{{ methodLabel(order.payments[0].payment_method) }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">Ref: {{ order.payments[0].transaction_id }}</p>
                    <span
                        class="mt-1.5 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold"
                        :class="payMeta(order.payments[0].status).classes"
                    >
                        {{ payMeta(order.payments[0].status).label }}
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3 text-sm">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Payment Date</p>
                <p class="mt-1 font-medium text-gray-900">{{ order.payments[0].paid_at ?? '-' }}</p>
                <p v-if="order.invoice_number" class="mt-1 text-xs text-gray-500">
                    Invoice: {{ order.invoice_number }}
                </p>
            </div>

            <div v-if="order.payments.length > 1" class="border-t border-gray-100 pt-3">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">History</p>
                <ul class="mt-2 space-y-1.5 text-xs text-gray-600">
                    <li v-for="pay in order.payments.slice(1)" :key="pay.id" class="flex items-center justify-between gap-2">
                        <span>{{ pay.transaction_id }} · {{ pay.created_at ?? '-' }}</span>
                        <span class="font-semibold">{{ pay.status }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
