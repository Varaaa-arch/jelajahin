<script setup lang="ts">
import { CreditCard, Settings2 } from 'lucide-vue-next';
import { formatIDR, paymentMethodLabel, type PaymentMethodSummary } from '@/types/admin-payment';

defineProps<{ methods: PaymentMethodSummary[] }>();
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 text-base font-black text-gray-900">
            <Settings2 class="h-5 w-5 text-teal-dark" />
            Payment Methods
        </h2>

        <div v-if="methods.length === 0" class="mt-4 text-sm text-gray-400">
            Belum ada metode pembayaran tercatat.
        </div>

        <ul v-else class="mt-4 space-y-2.5">
            <li
                v-for="m in methods"
                :key="m.method"
                class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                    <CreditCard class="h-5 w-5 text-teal-dark" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-gray-900">{{ paymentMethodLabel(m.method) }}</p>
                    <p class="text-xs text-gray-500">{{ m.total }} transaksi · {{ formatIDR(m.amount) }}</p>
                </div>
            </li>
        </ul>
    </section>
</template>
