<script setup lang="ts">
import { TriangleAlert, X } from 'lucide-vue-next';
import type { AdminUser } from '@/types/admin-user';

defineProps<{
    open: boolean;
    user: AdminUser | null;
    processing?: boolean;
    errors?: Record<string, string>;
}>();

defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="$emit('close')"
            >
                <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-black text-gray-900">Hapus Pengguna</h3>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                            aria-label="Tutup"
                            @click="$emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-3 px-6 py-5 text-sm">
                        <p class="text-gray-700">
                            Hapus <span class="font-bold text-gray-900">{{ user?.name }}</span>
                            ({{ user?.email }})?
                        </p>
                        <div v-if="(user?.bookings_count ?? 0) > 0" class="flex items-start gap-2 rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                            <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
                            Pengguna ini memiliki {{ user?.bookings_count }} booking — penghapusan akan ditolak server. Suspend saja bila perlu.
                        </div>
                        <p v-if="errors?.user" class="text-xs text-red-600">{{ errors.user }}</p>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50" @click="$emit('close')">
                                BATAL
                            </button>
                            <button
                                type="button"
                                :disabled="processing"
                                class="rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-red-700 disabled:opacity-50"
                                @click="$emit('confirm')"
                            >
                                {{ processing ? 'MENGHAPUS...' : 'YA, HAPUS' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
