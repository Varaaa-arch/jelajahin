<script setup lang="ts">
import { reactive, watch } from 'vue';
import { X } from 'lucide-vue-next';
import type { AdminOrder } from '@/types/admin-order';

const props = defineProps<{
    open: boolean;
    order: AdminOrder | null;
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'submit', payload: any): void;
}>();

const form = reactive({
    baggage: '',
    insurance: '',
    meals: '',
    special_requests: '',
});

function resetForm(): void {
    form.baggage = props.order?.addons_baggage ?? '';
    form.insurance = props.order?.addons_insurance ?? '';
    form.meals = (props.order?.addons_meals ?? []).join(', ');
    form.special_requests = props.order?.special_requests ?? '';
}

watch(() => props.open, (v) => { if (v) resetForm(); }, { immediate: true });

function submit(): void {
    emit('submit', {
        special_requests: form.special_requests || null,
        addons: {
            baggage: form.baggage || null,
            insurance: form.insurance || null,
            meals: form.meals.split(',').map((s) => s.trim()).filter(Boolean),
        },
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="emit('close')"
            >
                <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <div>
                            <h3 class="text-base font-black text-gray-900">Modify Pesanan</h3>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ order?.pnr }} · {{ order?.booker_name }} · {{ order?.flight_number }}
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

                    <form class="space-y-4 px-6 py-5" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-gray-700">Extra Baggage</span>
                                <select v-model="form.baggage" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
                                    <option value="">Tidak ada</option>
                                    <option value="5kg">5kg</option>
                                    <option value="10kg">10kg</option>
                                    <option value="15kg">15kg</option>
                                    <option value="20kg">20kg</option>
                                    <option value="30kg">30kg</option>
                                </select>
                                <span v-if="errors?.['addons.baggage']" class="mt-1 block text-xs text-red-600">{{ errors['addons.baggage'] }}</span>
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-gray-700">Insurance</span>
                                <select v-model="form.insurance" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
                                    <option value="">Tidak ada</option>
                                    <option value="basic">Basic</option>
                                    <option value="premium">Premium</option>
                                </select>
                                <span v-if="errors?.['addons.insurance']" class="mt-1 block text-xs text-red-600">{{ errors['addons.insurance'] }}</span>
                            </label>
                        </div>

                        <label class="block text-sm">
                            <span class="mb-1 block font-semibold text-gray-700">Meals (pisahkan koma)</span>
                            <input v-model="form.meals" type="text" placeholder="Standard Meals, Vegetarian" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-semibold text-gray-700">Special Requests</span>
                            <textarea v-model="form.special_requests" rows="3" placeholder="Kursi dekat jendela, wheelchair, ..." class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                            <span v-if="errors?.special_requests" class="mt-1 block text-xs text-red-600">{{ errors.special_requests }}</span>
                        </label>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50" @click="emit('close')">
                                BATAL
                            </button>
                            <button type="submit" :disabled="processing" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                                {{ processing ? 'MENYIMPAN...' : 'SIMPAN PERUBAHAN' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
