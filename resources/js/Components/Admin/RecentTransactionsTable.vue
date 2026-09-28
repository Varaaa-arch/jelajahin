<script setup lang="ts">
import { ListFilter } from 'lucide-vue-next';

export interface Txn {
    id: string;
    pnr: string;
    passenger: string;
    amount: string;
}

defineProps<{
    transactions: Txn[];
}>();
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between px-5 py-4">
            <h3 class="text-base font-bold text-gray-900">Recent Transactions</h3>
            <button type="button" class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100" aria-label="Filter transaksi">
                <ListFilter class="h-4 w-4" />
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead>
                    <tr class="border-y border-gray-200 bg-gray-50/60 text-xs text-gray-600">
                        <th class="px-5 py-3 font-semibold">ID / PNR</th>
                        <th class="px-5 py-3 font-semibold">Passenger</th>
                        <th class="px-5 py-3 font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="t in transactions" :key="t.id" class="hover:bg-gray-50/60">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-gray-900">{{ t.id }}</p>
                            <p class="text-xs text-gray-500">PNR: {{ t.pnr }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-gray-800">{{ t.passenger }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ t.amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
