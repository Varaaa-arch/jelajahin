<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';
import { formatOrderDate, type AdminOrder, type RescheduleFlightOption } from '@/types/admin-order';

const props = defineProps<{
    open: boolean;
    order: AdminOrder | null;
    flights: RescheduleFlightOption[];
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit', payload: { new_flight_id: string }): void;
}>();

const selectedId = ref('');

const candidates = computed(() => {
    if (!props.order) return [];
    return props.flights.filter(
        (f) => f.route_id === props.order!.route_id && f.id !== props.order!.flight_id,
    );
});

watch(() => props.open, (v) => { if (v) selectedId.value = ''; }, { immediate: true });
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
                            <h3 class="text-base font-black text-gray-900">Reschedule Pesanan</h3>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ order?.pnr }} · {{ order?.origin_code }} → {{ order?.destination_code }} ·
                                {{ order?.passenger_count }} penumpang
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

                    <form class="space-y-3 px-6 py-5" @submit.prevent="selectedId && emit('submit', { new_flight_id: selectedId })">
                        <p class="text-xs text-gray-500">
                            Hanya penerbangan rute yang sama ({{ order?.origin_code }} → {{ order?.destination_code }})
                            dengan kursi cukup yang ditampilkan.
                        </p>

                        <div v-if="candidates.length === 0" class="rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                            Tidak ada penerbangan pengganti yang tersedia untuk rute ini.
                        </div>

                        <label
                            v-for="f in candidates"
                            :key="f.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm transition"
                            :class="selectedId === f.id ? 'border-navy bg-navy/5' : 'border-gray-200 hover:border-navy/50'"
                        >
                            <input v-model="selectedId" type="radio" :value="f.id" class="accent-[#0b2a3a]" />
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">{{ f.flight_number }} · {{ f.airline_name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ formatOrderDate(f.departure_date) }} · {{ f.departure_time ?? '' }} · Sisa {{ f.seats_available }} kursi
                                </p>
                            </div>
                        </label>

                        <p v-if="errors?.new_flight_id" class="text-xs text-red-600">{{ errors.new_flight_id }}</p>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50" @click="emit('close')">
                                BATAL
                            </button>
                            <button type="submit" :disabled="processing || !selectedId" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                                {{ processing ? 'MEMPROSES...' : 'PINDAHKAN PESANAN' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
