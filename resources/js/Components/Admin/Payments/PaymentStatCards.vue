<script setup lang="ts">
import { ArrowLeftRight, Banknote, CircleAlert, Clock3, TrendingUp } from 'lucide-vue-next';
import { formatCompactIDR, formatIDR, type PaymentStats } from '@/types/admin-payment';

defineProps<{ stats: PaymentStats }>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Total Revenue</p>
                <Banknote class="h-5 w-5 text-teal-dark" />
            </div>
            <p class="mt-2 text-2xl font-black text-gray-900">{{ formatCompactIDR(stats.revenue) }}</p>
            <p v-if="stats.revenue_growth_pct !== null" class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-600">
                <TrendingUp class="h-3.5 w-3.5" />
                {{ stats.revenue_growth_pct >= 0 ? '+' : '' }}{{ stats.revenue_growth_pct }}% from last month
            </p>
            <p v-else class="mt-1 text-xs text-gray-400">Settled payments (success)</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Pending Payments</p>
                <Clock3 class="h-5 w-5 text-amber-600" />
            </div>
            <p class="mt-2 text-2xl font-black text-gray-900">{{ stats.pending_count }}</p>
            <p class="mt-1 text-xs text-gray-500">Amount: {{ formatCompactIDR(stats.pending_amount) }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Failed Payments</p>
                <CircleAlert class="h-5 w-5 text-red-600" />
            </div>
            <p class="mt-2 text-2xl font-black text-gray-900">{{ stats.failed_count }}</p>
            <p class="mt-1 text-xs text-gray-500">Amount: {{ formatCompactIDR(stats.failed_amount) }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Refunds Processed</p>
                <ArrowLeftRight class="h-5 w-5 text-gray-500" />
            </div>
            <p class="mt-2 text-2xl font-black text-gray-900">{{ stats.refunded_count }}</p>
            <p class="mt-1 text-xs text-gray-500">Amount: {{ formatIDR(stats.refunded_amount) }}</p>
        </div>
    </div>
</template>
