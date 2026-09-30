<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.user?.role === 'admin');

interface Notif {
    id: string;
    type: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
}

defineProps<{
    notifications: { data: Notif[]; current_page: number; last_page: number };
    unreadCount: number;
}>();

function markRead(id: string) {
    router.post(`/notifications/${id}/read`, {}, { preserveScroll: true });
}

function markAll() {
    router.post('/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <component :is="isAdmin ? AdminLayout : AuthenticatedLayout">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Notifikasi</h1>
                <p class="text-sm text-gray-500">{{ unreadCount }} belum dibaca</p>
            </div>
            <button
                type="button"
                class="rounded-xl bg-navy px-4 py-2 text-sm font-bold text-white hover:opacity-90"
                @click="markAll"
            >
                Tandai semua dibaca
            </button>
        </div>

        <div class="space-y-3">
            <div
                v-for="n in notifications.data"
                :key="n.id"
                class="rounded-2xl border p-4"
                :class="n.read_at ? 'border-gray-100 bg-white' : 'border-teal/30 bg-teal/5'"
            >
                <p class="text-sm font-bold text-gray-900">{{ n.data?.message ?? 'Notifikasi' }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    {{ new Date(n.created_at).toLocaleString('id-ID') }}
                    <span v-if="n.data?.url"> · <Link :href="n.data.url" class="text-teal-700 hover:underline">Buka</Link></span>
                </p>
                <button
                    v-if="!n.read_at"
                    type="button"
                    class="mt-2 text-xs font-bold text-teal-700 hover:underline"
                    @click="markRead(n.id)"
                >
                    Tandai dibaca
                </button>
            </div>
            <div v-if="notifications.data.length === 0" class="rounded-2xl border border-dashed p-10 text-center text-sm text-gray-500">
                Belum ada notifikasi.
            </div>
        </div>
    </div>
    </component>
</template>
