<script setup lang="ts">
import { reactive, watch } from 'vue';
import { X } from 'lucide-vue-next';
import type { AdminPassenger, CheckInStatus } from '@/types/admin-passenger';

const props = defineProps<{
    open: boolean;
    passenger: AdminPassenger | null;
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'submit', payload: any): void;
}>();

const form = reactive({
    title: '',
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: '',
    identity_type: '',
    identity_number: '',
    nationality: '',
    passport_number: '',
    check_in_status: 'not_checked_in' as CheckInStatus,
});

function resetForm(): void {
    form.title = props.passenger?.title ?? '';
    form.first_name = props.passenger?.first_name ?? '';
    form.last_name = props.passenger?.last_name ?? '';
    form.date_of_birth = props.passenger?.date_of_birth ?? '';
    form.gender = props.passenger?.gender ?? '';
    form.identity_type = props.passenger?.identity_type ?? '';
    form.identity_number = props.passenger?.identity_number ?? '';
    form.nationality = props.passenger?.nationality ?? '';
    form.passport_number = props.passenger?.passport_number ?? '';
    form.check_in_status = props.passenger?.check_in_status ?? 'not_checked_in';
}

watch(() => props.open, (v) => { if (v) resetForm(); }, { immediate: true });
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="emit('close')"
            >
                <Transition name="modal-pop" appear>
                    <div
                        v-if="open"
                        class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
                    >
                        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                            <div>
                                <h3 class="text-base font-black text-gray-900">Ubah Data Penumpang</h3>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ passenger?.name }} · PNR {{ passenger?.pnr }} · {{ passenger?.flight_number }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                                aria-label="Tutup"
                                @click="emit('close')"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <form
                            class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2"
                            @submit.prevent="emit('submit', { ...form })"
                        >
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Title</label>
                                <select
                                    v-model="form.title"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="">-</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Check-in</label>
                                <select
                                    v-model="form.check_in_status"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="not_checked_in">Not checked-in</option>
                                    <option value="checked_in">Checked-in</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">First Name</label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                                <p v-if="errors?.first_name" class="mt-1 text-xs text-red-600">{{ errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Last Name</label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                                <p v-if="errors?.last_name" class="mt-1 text-xs text-red-600">{{ errors.last_name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Date of Birth</label>
                                <input
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Gender</label>
                                <select
                                    v-model="form.gender"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="">-</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Identity Type</label>
                                <select
                                    v-model="form.identity_type"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="">-</option>
                                    <option value="KTP">KTP</option>
                                    <option value="Passport">Passport</option>
                                    <option value="SIM">SIM</option>
                                    <option value="ID">ID</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Identity Number</label>
                                <input
                                    v-model="form.identity_number"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Nationality</label>
                                <input
                                    v-model="form.nationality"
                                    type="text"
                                    placeholder="Indonesia"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Passport Number</label>
                                <input
                                    v-model="form.passport_number"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>

                            <div class="flex items-center justify-end gap-2 sm:col-span-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-gray-300 px-5 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50"
                                    @click="emit('close')"
                                >
                                    BATAL
                                </button>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="rounded-xl bg-navy px-5 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid disabled:opacity-50"
                                >
                                    {{ processing ? 'MENYIMPAN...' : 'SIMPAN PERUBAHAN' }}
                                </button>
                            </div>
                        </form>
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
