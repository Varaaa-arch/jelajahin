<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, Landmark, Wallet } from 'lucide-vue-next';
import { formatIDR, paymentBadge, paymentMethodLabel, type AdminPayment } from '@/types/admin-payment';

defineProps<{
    payments: AdminPayment[];
    loading?: boolean;
    offset?: number;
}>();

defineEmits<{
    (e: 'view', payment: AdminPayment): void;
}>();

function methodIcon(method?: string | null) {
    const m = (method ?? '').toLowerCase();
    if (m.includes('bank') || m.includes('transfer') || m.includes('virtual')) return Landmark;
    if (m.includes('wallet') || m.includes('gopay') || m.includes('ovo') || m.includes('dana')) return Wallet;
    return CreditCard;
}

function formatDateTime(iso?: string | null): string {
    if (!iso) return '-';
    const d = new Date(iso.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return iso;
    return d.toLocaleString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <h2 class="border-b border-gray-200 px-5 py-4 text-base font-black text-gray-900">Recent Transactions</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1020px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/60 text-xs text-gray-600">
                        <th class="w-12 px-5 py-3.5 font-semibold">#</th>
                        <th class="px-5 py-3.5 font-semibold">Transaction ID</th>
                        <th class="px-5 py-3.5 font-semibold">Booking Ref</th>
                        <th class="px-5 py-3.5 font-semibold">Amount</th>
                        <th class="px-5 py-3.5 font-semibold">Method</th>
                        <th class="px-5 py-3.5 font-semibold">Date &amp; Time</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="loading">
                        <tr v-for="i in 6" :key="`skel-${i}`" class="animate-pulse">
                            <td v-for="c in 8" :key="c" class="px-5 py-4">
                                <div class="h-4 rounded bg-gray-100" />
                            </td>
                        </tr>
                    </template>
                    <template v-else-if="payments.length === 0">
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm font-bold text-gray-900">Tidak ada transaksi ditemukan</p>
                                <p class="mt-1 text-xs text-gray-500">Coba ubah kata kunci atau reset filter.</p>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr
                            v-for="(pay, idx) in payments"
                            :key="pay.id"
                            class="transition-colors hover:bg-gray-50/70"
                        >
                            <td class="px-5 py-4 text-gray-700">{{ (offset ?? 0) + idx + 1 }}</td>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-gray-900">
                                {{ pay.transaction_id }}
                            </td>
                            <td class="px-5 py-4">
                                <Link :href="`/admin/orders/${pay.booking_id}`" class="font-semibold text-teal-dark hover:underline">
                                    {{ pay.pnr }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">
                                {{ formatIDR(pay.amount) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 text-gray-700">
                                    <component :is="methodIcon(pay.payment_method)" class="h-4 w-4 text-gray-500" />
                                    {{ paymentMethodLabel(pay.payment_method) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ formatDateTime(pay.paid_at ?? pay.created_at) }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold"
                                    :class="paymentBadge(pay.status).classes"
                                >
                                    {{ paymentBadge(pay.status).label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    class="text-xs font-bold tracking-wide text-teal-dark hover:underline"
                                    @click="$emit('view', pay)"
                                >
                                    VIEW
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
