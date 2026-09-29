<script setup lang="ts">
import { Users } from 'lucide-vue-next';
import type { AdminOrderDetail } from '@/types/admin-order';

defineProps<{ order: AdminOrderDetail }>();
</script>

<template>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
            <Users class="h-5 w-5 text-teal-dark" />
            Passengers &amp; Seating
        </h2>

        <div class="mt-2 overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-gray-500">
                        <th class="bg-gray-50 px-4 py-3 font-semibold">Name</th>
                        <th class="bg-gray-50 px-4 py-3 font-semibold">Type</th>
                        <th class="bg-gray-50 px-4 py-3 font-semibold">Ticket No.</th>
                        <th class="bg-gray-50 px-4 py-3 text-right font-semibold">Seat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="p in order.passengers" :key="p.id" class="text-gray-800">
                        <td class="px-4 py-3.5 font-medium text-gray-900">{{ p.name }}</td>
                        <td class="px-4 py-3.5">{{ p.type }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5">{{ p.ticket_number ?? '-' }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="inline-flex min-w-9 justify-center rounded-md bg-teal/15 px-2 py-1 text-xs font-bold text-teal-dark">
                                {{ p.seat_number ?? 'TBA' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
