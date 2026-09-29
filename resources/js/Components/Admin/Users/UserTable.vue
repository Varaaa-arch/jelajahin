<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';
import { formatJoinDate, roleMeta, userStatusMeta, type AdminUser } from '@/types/admin-user';

defineProps<{
    users: AdminUser[];
    loading?: boolean;
    offset?: number;
}>();

defineEmits<{
    (e: 'edit', user: AdminUser): void;
    (e: 'delete', user: AdminUser): void;
}>();

function providerLabel(provider?: string | null): string | null {
    if (!provider) return null;
    return provider.charAt(0).toUpperCase() + provider.slice(1);
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-600">
                        <th class="w-12 px-5 py-3.5 font-semibold">#</th>
                        <th class="px-5 py-3.5 font-semibold">Nama Lengkap</th>
                        <th class="px-5 py-3.5 font-semibold">Kontak</th>
                        <th class="px-5 py-3.5 font-semibold">Tipe</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Total Bookings</th>
                        <th class="px-5 py-3.5 font-semibold">Bergabung</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="loading">
                        <tr v-for="i in 6" :key="`skel-${i}`" class="animate-pulse">
                            <td v-for="c in 8" :key="c" class="px-5 py-4">
                                <div class="h-4 rounded bg-gray-100" />
                            </td>
                        </tr>
                    </template>
                    <template v-else-if="users.length === 0">
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm font-bold text-gray-900">Tidak ada pengguna ditemukan</p>
                                <p class="mt-1 text-xs text-gray-500">Coba ubah kata kunci atau reset filter.</p>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="(u, idx) in users" :key="u.id" class="transition-colors hover:bg-gray-50/70">
                            <td class="px-5 py-4 text-gray-700">{{ (offset ?? 0) + idx + 1 }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        v-if="!u.avatar"
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy text-sm font-black text-white"
                                    >
                                        {{ u.initial }}
                                    </span>
                                    <img
                                        v-else
                                        :src="u.avatar"
                                        :alt="u.name"
                                        class="h-10 w-10 shrink-0 rounded-full object-cover"
                                    />
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ u.name }}
                                            <span v-if="u.is_self" class="ml-1 rounded bg-navy/10 px-1.5 py-0.5 text-[10px] font-bold text-navy">YOU</span>
                                        </p>
                                        <p v-if="providerLabel(u.provider)" class="text-xs text-gray-500">
                                            via {{ providerLabel(u.provider) }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-gray-800">{{ u.email }}</p>
                                <p v-if="!u.verified" class="text-xs text-amber-600">Belum verifikasi</p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                    :class="roleMeta(u.role).classes"
                                >
                                    {{ roleMeta(u.role).label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-semibold"
                                    :class="userStatusMeta(u.status).classes"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="userStatusMeta(u.status).dot" />
                                    {{ userStatusMeta(u.status).label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                {{ u.bookings_count > 0 ? u.bookings_count : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ formatJoinDate(u.joined_at) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-gray-500 transition hover:bg-teal/10 hover:text-teal-dark"
                                        title="Ubah"
                                        aria-label="Ubah pengguna"
                                        @click="$emit('edit', u)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="u.is_self"
                                        class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600 disabled:opacity-30"
                                        title="Hapus"
                                        aria-label="Hapus pengguna"
                                        @click="$emit('delete', u)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
