<script setup lang="ts">
import { computed } from 'vue';
import { ReceiptText } from 'lucide-vue-next';
import { formatIDR, type AdminOrderDetail } from '@/types/admin-order';

const props = defineProps<{ order: AdminOrderDetail }>();

const fareLabel = computed(() => {
    const parts: string[] = [];
    const c = props.order.type_counts;
    if (c.adult > 0) parts.push(`${c.adult} Adult${c.adult > 1 ? 's' : ''}`);
    if (c.child > 0) parts.push(`${c.child} Child`);
    if (c.infant > 0) parts.push(`${c.infant} Infant`);
    return parts.length > 0 ? `Base Fare (${parts.join(', ')})` : 'Base Fare';
});
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
            <ReceiptText class="h-5 w-5 text-teal-dark" />
            Price Breakdown
        </h2>

        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-600">{{ fareLabel }}</dt>
                <dd class="whitespace-nowrap font-medium text-gray-900">{{ formatIDR(order.price.base) }}</dd>
            </div>
            <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-600">Taxes &amp; Fees</dt>
                <dd class="whitespace-nowrap font-medium text-gray-900">{{ formatIDR(order.price.tax) }}</dd>
            </div>
            <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-600">Add-ons (Insurance, Meals)</dt>
                <dd class="whitespace-nowrap font-medium text-gray-900">{{ formatIDR(order.price.addons) }}</dd>
            </div>
            <div v-if="order.price.discount > 0" class="flex items-start justify-between gap-3">
                <dt class="text-gray-600">Discount</dt>
                <dd class="whitespace-nowrap font-medium text-emerald-600">-{{ formatIDR(order.price.discount) }}</dd>
            </div>
        </dl>

        <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
            <p class="text-sm font-black tracking-wide text-gray-900">TOTAL FARE</p>
            <p class="text-lg font-black text-teal-dark">{{ formatIDR(order.price.total) }}</p>
        </div>
    </section>
</template>
