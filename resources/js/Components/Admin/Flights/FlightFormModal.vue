<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { X } from 'lucide-vue-next';
import type {
    AdminFlight,
    FlightAircraftOption,
    FlightDbStatus,
    FlightRouteOption,
} from '@/types/admin-flight';

const props = defineProps<{
    open: boolean;
    mode: 'create' | 'edit';
    flight?: AdminFlight | null;
    routes: FlightRouteOption[];
    aircrafts: FlightAircraftOption[];
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'submit', payload: any): void;
}>();

const form = reactive({
    flight_number: '',
    route_id: '',
    aircraft_id: '',
    departure_date: '',
    departure_time: '',
    arrival_time: '',
    base_price: '' as string | number,
    tax_surcharge: 0 as string | number,
    fuel_surcharge: 0 as string | number,
    status: 'scheduled' as FlightDbStatus,
});

const statuses: { key: FlightDbStatus; label: string }[] = [
    { key: 'scheduled', label: 'Terjadwal' },
    { key: 'boarding', label: 'Boarding' },
    { key: 'in_flight', label: 'Aktif (In Flight)' },
    { key: 'landed', label: 'Nonaktif (Landed)' },
    { key: 'cancelled', label: 'Batal' },
];

const filteredAircrafts = computed(() => {
    const route = props.routes.find((r) => r.id === form.route_id);
    if (!route) return props.aircrafts;
    const sameAirline = props.aircrafts.filter((a) => a.airline_id === route.airline_id);
    return sameAirline.length > 0 ? sameAirline : props.aircrafts;
});

function resetForm(): void {
    form.flight_number = props.flight?.flight_number ?? '';
    form.route_id = props.flight?.route_id ?? '';
    form.aircraft_id = props.flight?.aircraft_id ?? '';
    form.departure_date = props.flight?.departure_date ?? '';
    form.departure_time = props.flight?.departure_time ?? '';
    form.arrival_time = props.flight?.arrival_time ?? '';
    form.base_price = props.flight?.base_price ?? '';
    form.tax_surcharge = 0;
    form.fuel_surcharge = 0;
    form.status =
        (props.flight?.status as FlightDbStatus | undefined) ?? 'scheduled';
}

watch(() => props.open, (v) => { if (v) resetForm(); }, { immediate: true });
watch(() => props.flight, () => { if (props.open) resetForm(); });

function routeLabel(r: FlightRouteOption): string {
    const o = r.origin_airport?.code ?? '?';
    const d = r.destination_airport?.code ?? '?';
    const al = r.airline?.code ?? '';
    return `${o} → ${d} · ${al}${r.flight_number_prefix} · ${r.id.slice(0, 8)}`;
}

function onSubmit(): void {
    emit('submit', { ...form, base_price: Number(form.base_price) || 0 });
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
                <Transition name="modal-pop" appear>
                    <div
                        v-if="open"
                        class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
                    >
                        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                            <div>
                                <h3 class="text-base font-black text-gray-900">
                                    {{ mode === 'create' ? 'Tambah Penerbangan Baru' : 'Ubah Penerbangan' }}
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ mode === 'create' ? 'Kursi otomatis dibuat dari layout pesawat.' : `ID: ${flight?.id?.slice(0, 8) ?? '-'}` }}
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

                        <form class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2" @submit.prevent="onSubmit">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Nomor Penerbangan</label>
                                <input
                                    v-model="form.flight_number"
                                    type="text"
                                    placeholder="GA-123"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                                <p v-if="errors?.flight_number" class="mt-1 text-xs text-red-600">{{ errors.flight_number }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Status</label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option v-for="s in statuses" :key="s.key" :value="s.key">{{ s.label }}</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Rute</label>
                                <select
                                    v-model="form.route_id"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="" disabled>Pilih rute</option>
                                    <option v-for="r in routes" :key="r.id" :value="r.id">{{ routeLabel(r) }}</option>
                                </select>
                                <p v-if="errors?.route_id" class="mt-1 text-xs text-red-600">{{ errors.route_id }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Pesawat</label>
                                <select
                                    v-model="form.aircraft_id"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                >
                                    <option value="" disabled>Pilih pesawat</option>
                                    <option v-for="a in filteredAircrafts" :key="a.id" :value="a.id">
                                        {{ a.registration_number }} · {{ a.id.slice(0, 8) }}
                                    </option>
                                </select>
                                <p v-if="errors?.aircraft_id" class="mt-1 text-xs text-red-600">{{ errors.aircraft_id }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Tanggal Berangkat</label>
                                <input
                                    v-model="form.departure_date"
                                    type="date"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-gray-700">Jam Berangkat</label>
                                    <input
                                        v-model="form.departure_time"
                                        type="time"
                                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-gray-700">Jam Tiba</label>
                                    <input
                                        v-model="form.arrival_time"
                                        type="time"
                                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-700">Harga Dasar (Rp)</label>
                                <input
                                    v-model="form.base_price"
                                    type="number"
                                    min="0"
                                    placeholder="750000"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-gray-700">Pajak</label>
                                    <input
                                        v-model="form.tax_surcharge"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-gray-700">Fuel</label>
                                    <input
                                        v-model="form.fuel_surcharge"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                                    />
                                </div>
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
                                    {{ processing ? 'MENYIMPAN...' : mode === 'create' ? 'SIMPAN PENERBANGAN' : 'SIMPAN PERUBAHAN' }}
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
