<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    announcements: { data: Array<{ id: string; subject: string; message: string; target: string; sent_count: number; created_at: string }> };
    stats: { total_users: number; total_admins: number };
}>();

const form = useForm({
    subject: '',
    message: '',
    target: 'all',
});

function submit() {
    form.post('/admin/broadcast', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-black text-gray-900">Broadcast Email + Notifikasi</h1>
            <p class="text-sm text-gray-500">Kirim pengumuman ke semua user via email (SMTP) + bell notifikasi. User: {{ stats.total_users }}, Admin: {{ stats.total_admins }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-6">
                <label class="mb-1 block text-sm font-bold text-gray-700">Subjek email</label>
                <input v-model="form.subject" type="text" class="mb-4 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-teal focus:outline-none" placeholder="Contoh: Promo akhir tahun 30%" />
                <div v-if="form.errors.subject" class="mb-2 text-xs text-red-600">{{ form.errors.subject }}</div>

                <label class="mb-1 block text-sm font-bold text-gray-700">Target</label>
                <select v-model="form.target" class="mb-4 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                    <option value="all">Semua (user + admin)</option>
                    <option value="user">User saja</option>
                    <option value="admin">Admin saja (test)</option>
                </select>

                <label class="mb-1 block text-sm font-bold text-gray-700">Pesan</label>
                <textarea v-model="form.message" rows="6" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-teal focus:outline-none" placeholder="Tulis pengumuman..." />
                <div v-if="form.errors.message" class="mb-2 text-xs text-red-600">{{ form.errors.message }}</div>

                <button type="submit" :disabled="form.processing" class="mt-4 w-full rounded-xl bg-navy py-2.5 text-sm font-bold text-white disabled:opacity-50">
                    {{ form.processing ? 'Mengirim...' : 'Kirim broadcast' }}
                </button>
                <p class="mt-2 text-xs text-gray-400">Pengiriman masuk antrean queue (database). Pastikan <code>php artisan queue:work</code> jalan.</p>
            </form>

            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-sm font-black uppercase tracking-widest text-gray-500">Riwayat</h2>
                <div v-if="announcements.data.length === 0" class="text-sm text-gray-500">Belum ada broadcast.</div>
                <div v-for="a in announcements.data" :key="a.id" class="mb-3 rounded-xl bg-gray-50 p-4">
                    <p class="text-sm font-bold text-gray-900">{{ a.subject }}</p>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ a.message }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ a.target }} · {{ a.sent_count }} terkirim · {{ new Date(a.created_at).toLocaleString('id-ID') }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
