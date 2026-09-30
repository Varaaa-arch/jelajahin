<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    refund: any;
}>();

const form = useForm({});

const cancelRefund = () => {
    if (confirm('Apakah Anda yakin ingin membatalkan refund request ini?')) {
        form.delete(route('refunds.cancel', props.refund.id));
    }
};

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
                Detail Refund
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">No. Refund</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.refund_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span :class="['inline-flex rounded-full px-2 text-xs font-semibold leading-5', statusColors[refund.status]]">
                                        {{ statusLabels[refund.status] }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">PNR Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.booking?.pnr_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipe Refund</dt>
                                <dd class="mt-1 text-sm capitalize text-gray-900">{{ refund.refund_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Jumlah Diminta</dt>
                                <dd class="mt-1 text-sm text-gray-900">Rp {{ Number(refund.requested_amount).toLocaleString('id-ID') }}</dd>
                            </div>
                            <div v-if="refund.approved_amount">
                                <dt class="text-sm font-medium text-gray-500">Jumlah Disetujui</dt>
                                <dd class="mt-1 text-sm text-gray-900">Rp {{ Number(refund.approved_amount).toLocaleString('id-ID') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.reason }}</dd>
                            </div>
                            <div v-if="refund.admin_notes" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Catatan Admin</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.admin_notes }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal Request</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(refund.created_at).toLocaleDateString('id-ID') }}</dd>
                            </div>
                            <div v-if="refund.processed_at">
                                <dt class="text-sm font-medium text-gray-500">Tanggal Diproses</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(refund.processed_at).toLocaleDateString('id-ID') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex gap-3">
                            <Link :href="route('refunds.index')" class="rounded bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                                Kembali
                            </Link>
                            <button
                                v-if="refund.status === 'pending'"
                                @click="cancelRefund"
                                class="rounded bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                            >
                                Batalkan Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
