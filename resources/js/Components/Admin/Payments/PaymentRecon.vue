<script setup lang="ts">
import { CalendarClock, Download } from 'lucide-vue-next';
import { formatIDR, type PaymentRecon } from '@/types/admin-payment';

defineProps<{ recon: PaymentRecon }>();

defineEmits<{
    (e: 'change', date: string): void;
    (e: 'export'): void;
}>();

function today(): string {
    return new Date().toISOString().slice(0, 10);
}

function discrepancyLabel(value: number): string {
    const abs = formatIDR(Math.abs(value));
    if (value < 0) return `- ${abs}`;
    if (value > 0) return `+ ${abs}`;
    return abs;
}
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-base font-black text-gray-900">
                <CalendarClock class="h-5 w-5 text-teal-dark" />
                Daily Reconciliation
            </h2>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-dark hover:underline"
                @click="$emit('export')"
            >
                <Download class="h-3.5 w-3.5" />
                EXPORT REPORT
            </button>
        </div>

        <label class="mt-3 block text-xs">
            <span class="mb-1 block font-semibold text-gray-500">Tanggal</span>
            <input
                :value="recon.date"
                type="date"
                :max="today()"
                class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                @change="$emit('change', ($event.target as HTMLInputElement).value)"
            />
        </label>

        <div class="mt-3 space-y-2.5 text-sm">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3">
                <span class="text-gray-600">Expected Revenue</span>
                <span class="font-bold text-gray-900">{{ formatIDR(recon.expected) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3">
                <span class="text-gray-600">Actual Settled</span>
                <span class="font-bold text-emerald-600">{{ formatIDR(recon.actual) }}</span>
            </div>
            <div
                class="flex items-center justify-between rounded-xl border px-4 py-3"
                :class="recon.discrepancy === 0 ? 'border-gray-200' : 'border-red-200 bg-red-50/50'"
            >
                <span class="text-gray-600">Discrepancy</span>
                <span class="font-bold" :class="recon.discrepancy === 0 ? 'text-gray-900' : 'text-red-600'">
                    {{ discrepancyLabel(recon.discrepancy) }}
                </span>
            </div>
        </div>
    </section>
</template>
