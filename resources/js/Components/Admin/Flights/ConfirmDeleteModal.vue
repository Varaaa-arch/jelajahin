<script setup lang="ts">
import { TriangleAlert, X } from 'lucide-vue-next';
import type { AdminFlight } from '@/types/admin-flight';

defineProps<{
    open: boolean;
    flight: AdminFlight | null;
    processing?: boolean;
}>();

defineEmits<{ (e: 'close'): void; (e: 'confirm'): void }>();
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="$emit('close')"
            >
                <Transition name="modal-pop" appear>
                    <div v-if="open" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                                <TriangleAlert class="h-5 w-5" />
                            </span>
                            <button
                                type="button"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100"
                                aria-label="Tutup"
                                @click="$emit('close')"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <h3 class="mt-4 text-base font-black text-gray-900">Hapus penerbangan?</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">
                            <strong class="text-gray-900">{{ flight?.flight_number }}</strong>
                            ({{ flight?.origin_code }} → {{ flight?.destination_code }}) akan dihapus permanen
                            beserta {{ flight?.seats_total ?? 0 }} data kursinya. Tindakan ini tidak bisa dibatalkan.
                        </p>
                        <div class="mt-5 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xl border border-gray-300 px-5 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50"
                                @click="$emit('close')"
                            >
                                BATAL
                            </button>
                            <button
                                type="button"
                                :disabled="processing"
                                class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-red-700 disabled:opacity-50"
                                @click="$emit('confirm')"
                            >
                                {{ processing ? 'MENGHAPUS...' : 'YA, HAPUS' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-pop-enter-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.modal-pop-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.modal-pop-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.97);
}
.modal-pop-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
</style>
