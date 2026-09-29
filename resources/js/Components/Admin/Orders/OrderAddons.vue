<script setup lang="ts">
import { Luggage, ShieldCheck, UtensilsCrossed, ConciergeBell } from 'lucide-vue-next';
import type { AdminOrderDetail } from '@/types/admin-order';

defineProps<{ order: AdminOrderDetail }>();
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
            <ConciergeBell class="h-5 w-5 text-teal-dark" />
            Services &amp; Add-ons
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100">
                    <Luggage class="h-5 w-5 text-teal-dark" />
                </span>
                <div class="text-sm">
                    <p class="font-bold text-gray-900">Baggage</p>
                    <p class="mt-1 text-gray-600">
                        {{ order.addons_baggage && order.addons_baggage !== 'none' ? `Extra ${order.addons_baggage}` : 'Standard allowance' }}
                    </p>
                    <p v-if="order.passengers[0]?.seat_class" class="text-xs text-gray-500">
                        {{ order.passengers[0].seat_class }}
                    </p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100">
                    <ShieldCheck class="h-5 w-5 text-teal-dark" />
                </span>
                <div class="text-sm">
                    <p class="font-bold text-gray-900">Insurance</p>
                    <p class="mt-1 text-gray-600">{{ order.addons_insurance && order.addons_insurance !== 'none' ? order.addons_insurance : '-' }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100">
                    <UtensilsCrossed class="h-5 w-5 text-teal-dark" />
                </span>
                <div class="text-sm">
                    <p class="font-bold text-gray-900">Meals</p>
                    <p v-if="order.addons_meals.length > 0" class="mt-1 text-gray-600">
                        {{ order.addons_meals.join(', ') }}
                    </p>
                    <p v-else class="mt-1 text-gray-400">-</p>
                </div>
            </div>
        </div>

        <p v-if="order.special_requests" class="mt-4 rounded-xl bg-gray-50 px-4 py-3 text-xs text-gray-600">
            <span class="font-bold text-gray-800">Special request:</span> {{ order.special_requests }}
        </p>
    </section>
</template>
