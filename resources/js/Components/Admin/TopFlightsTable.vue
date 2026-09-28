<script setup lang="ts">
export interface TopFlight {
    rank: number;
    route: string;
    flightNo: string;
    bookings: number;
}

defineProps<{
    flights: TopFlight[];
}>();
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between px-5 py-4">
            <h3 class="text-base font-bold text-gray-900">Top Most Booked Flights</h3>
            <a href="#" class="text-xs font-bold tracking-wide text-gray-700 hover:text-teal-dark">
                View All
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[480px] text-left text-sm">
                <thead>
                    <tr class="border-y border-gray-200 bg-gray-50/60 text-xs text-gray-600">
                        <th class="px-5 py-3 font-semibold">Rank</th>
                        <th class="px-5 py-3 font-semibold">Route</th>
                        <th class="px-5 py-3 font-semibold">Flight No.</th>
                        <th class="px-5 py-3 font-semibold">Bookings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="f in flights" :key="f.flightNo" class="hover:bg-gray-50/60">
                        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ f.rank }}</td>
                        <td class="px-5 py-3.5 text-gray-800">{{ f.route }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ f.flightNo }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ f.bookings }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
