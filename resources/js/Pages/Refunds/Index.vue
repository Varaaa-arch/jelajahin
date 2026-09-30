<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps<{
    refunds: any;
}>();

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    processed: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-800',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    processed: 'Diproses',
    cancelled: 'Dibatalkan',
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Refund Saya
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="refunds.data.length === 0" class="text-center text-gray-500">
                            Belum ada refund request.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No. Refund</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">PNR</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="refund in refunds.data" :key="refund.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ refund.refund_number }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ refund.booking?.pnr_code }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm capitalize text-gray-900">{{ refund.refund_type }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">Rp {{ Number(refund.requested_amount).toLocaleString('id-ID') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span :class="['inline-flex rounded-full px-2 text-xs font-semibold leading-5', statusColors[refund.status]]">
                                                {{ statusLabels[refund.status] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ new Date(refund.created_at).toLocaleDateString('id-ID') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <Link :href="route('refunds.show', refund.id)" class="text-indigo-600 hover:text-indigo-900">
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="refunds.links.length > 1" class="mt-4 flex justify-center">
                            <Link
                                v-for="link in refunds.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'mx-1 rounded px-3 py-1 text-sm',
                                    link.active ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
