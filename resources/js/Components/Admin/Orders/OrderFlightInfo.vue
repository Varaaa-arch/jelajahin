<script setup lang="ts">
import { Plane, PlaneTakeoff } from 'lucide-vue-next';
import { formatOrderDate, type AdminOrderDetail } from '@/types/admin-order';

defineProps<{ order: AdminOrderDetail }>();
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 pb-4">
            <h2 class="flex items-center gap-2 text-base font-black text-gray-900">
                <PlaneTakeoff class="h-5 w-5 text-teal-dark" />
                Flight Information
            </h2>
            <p class="text-sm text-gray-600">
                PNR: <span class="font-black text-gray-900">{{ order.pnr }}</span>
            </p>
        </div>

        <div class="mt-4 flex items-start gap-3">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-black text-gray-900">
                {{ order.airline_code.slice(0, 2).toUpperCase() }}
            </span>
            <div>
                <p class="font-black text-gray-900">{{ order.airline_name }}</p>
                <p class="mt-0.5 text-sm text-gray-500">
                    Flight {{ order.flight_number }} • {{ order.aircraft_label }}
                </p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4">
            <div>
                <p class="text-2xl font-black text-gray-900">{{ order.departure_time ?? '--:--' }}</p>
                <p class="mt-1 text-sm font-bold text-gray-900">{{ order.origin_code }}</p>
                <p class="text-xs text-gray-500">{{ order.origin_city }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-700">{{ formatOrderDate(order.departure_date) }}</p>
            </div>
            <div class="flex w-36 flex-col items-center sm:w-52">
                <p class="text-xs font-semibold text-gray-500">{{ order.duration_label }}</p>
                <div class="mt-1 flex w-full items-center gap-1">
                    <span class="h-px flex-1 bg-gray-300" />
                    <Plane class="h-4 w-4 shrink-0 text-teal-dark" />
                    <span class="h-px flex-1 bg-gray-300" />
                </div>
                <p class="mt-1 text-xs font-semibold text-teal-dark">Direct</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-black text-gray-900">{{ order.arrival_time ?? '--:--' }}</p>
                <p class="mt-1 text-sm font-bold text-gray-900">{{ order.destination_code }}</p>
                <p class="text-xs text-gray-500">{{ order.destination_city }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-700">{{ formatOrderDate(order.departure_date) }}</p>
            </div>
        </div>
    </section>
</template>
