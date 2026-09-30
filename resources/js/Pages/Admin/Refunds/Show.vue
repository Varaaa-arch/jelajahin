<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    refund: any;
}>();

const approveForm = useForm({
    approved_amount: props.refund.requested_amount,
});

const rejectForm = useForm({
    admin_notes: '',
});

const processForm = useForm({});

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
    <AdminLayout>
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
                                <dt class="text-sm font-medium text-gray-500">User</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.user?.name }} ({{ refund.user?.email }})</dd>
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
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal Request</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(refund.created_at).toLocaleDateString('id-ID') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.reason }}</dd>
                            </div>
                            <div v-if="refund.admin_notes" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Catatan Admin</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ refund.admin_notes }}</dd>
                            </div>
                        </dl>

                        <div v-if="refund.status === 'pending'" class="mt-6 space-y-4 border-t pt-6">
                            <form @submit.prevent="approveForm.put(route('admin.refunds.approve', refund.id))" class="space-y-3">
                                <h3 class="font-medium text-gray-900">Setujui Refund</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jumlah Disetujui</label>
                                    <input
                                        v-model="approveForm.approved_amount"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <button type="submit" class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                                    Setujui
                                </button>
                            </form>

                            <form @submit.prevent="rejectForm.put(route('admin.refunds.reject', refund.id))" class="space-y-3">
                                <h3 class="font-medium text-gray-900">Tolak Refund</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
                                    <textarea
                                        v-model="rejectForm.admin_notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <button type="submit" class="rounded bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">
                                    Tolak
                                </button>
                            </form>
                        </div>

                        <div v-if="refund.status === 'approved'" class="mt-6 border-t pt-6">
                            <form @submit.prevent="processForm.put(route('admin.refunds.process', refund.id))">
                                <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                                    Proses Refund
                                </button>
                            </form>
                        </div>

                        <div class="mt-6">
                            <Link :href="route('admin.refunds.index')" class="rounded bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                                Kembali
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
