<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';

interface NotifItem {
    id: string;
    type: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
}

const unread = ref(0);
const items = ref<NotifItem[]>([]);
const open = ref(false);
let timer: number | undefined;

async function fetchUnread() {
    try {
        const res = await fetch('/notifications/unread-count', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) return;
        const json = await res.json();
        unread.value = json.unread ?? 0;
        items.value = json.latest ?? [];
    } catch {
        /* abaikan, bell tetap tampil */
    }
}

async function markRead(id: string) {
    try {
        await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            },
        });
        await fetchUnread();
    } catch { /* ignore */ }
}

async function markAll() {
    try {
        await fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            },
        });
        await fetchUnread();
    } catch { /* ignore */ }
}

function toggle(e: Event) {
    e.stopPropagation();
    open.value = !open.value;
    if (open.value) fetchUnread();
}

function close() {
    open.value = false;
}

onMounted(() => {
    fetchUnread();
    timer = window.setInterval(fetchUnread, 30000);
    document.addEventListener('click', close);
});

onUnmounted(() => {
    if (timer) window.clearInterval(timer);
    document.removeEventListener('click', close);
});

function label(n: NotifItem): string {
    return n.data?.message ?? n.data?.pnr_code ?? 'Notifikasi baru';
}
</script>

<template>
    <div class="relative" @click.stop>
        <button
            type="button"
            class="relative rounded-full p-2 text-gray-600 transition hover:bg-gray-100"
            aria-label="Notifikasi"
            @click="toggle"
        >
            <Bell class="h-5 w-5" />
            <span
                v-if="unread > 0"
                class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white"
            >
                {{ unread > 9 ? '9+' : unread }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                <button type="button" class="text-xs font-semibold text-teal-700 hover:underline" @click="markAll">
                    Tandai dibaca
                </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <div v-if="items.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                    Belum ada notifikasi.
                </div>
                <button
                    v-for="n in items"
                    :key="n.id"
                    type="button"
                    class="flex w-full items-start gap-3 border-b border-gray-50 px-4 py-3 text-left transition hover:bg-gray-50"
                    @click="markRead(n.id)"
                >
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="n.read_at ? 'bg-gray-200' : 'bg-teal-500'" />
                    <span>
                        <span class="block text-sm font-semibold text-gray-800">{{ label(n) }}</span>
                        <span class="mt-0.5 block text-xs text-gray-400">
                            {{ new Date(n.created_at).toLocaleString('id-ID') }}
                        </span>
                    </span>
                </button>
            </div>
            <Link href="/notifications" class="block bg-gray-50 px-4 py-2.5 text-center text-xs font-bold text-gray-700 hover:bg-gray-100">
                Lihat semua
            </Link>
        </div>
    </div>
</template>
