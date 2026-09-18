<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Back Button -->
      <button @click="goBack" class="mb-6 flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Hasil Pencarian
      </button>

      <!-- Loading flight -->
      <div v-if="flightLoading" class="bg-white rounded-2xl shadow-lg p-8 mb-8 animate-pulse">
        <div class="grid grid-cols-4 gap-4">
          <div v-for="i in 4" :key="i" class="h-10 bg-gray-200 rounded"></div>
        </div>
      </div>

      <!-- Flight Info Card -->
      <div v-else-if="flight" class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <!-- Airline header -->
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 text-sm">
            {{ flight.airline?.code ?? '✈' }}
          </div>
          <div>
            <p class="font-bold text-gray-800">{{ flight.airline?.name ?? 'Maskapai' }}</p>
            <p class="text-sm text-blue-600 font-mono">{{ flight.flight_number }}</p>
          </div>
          <span class="ml-auto px-3 py-1 rounded-full text-xs font-semibold capitalize"
            :class="statusClass(flight.status)">
            {{ flight.status }}
          </span>
        </div>

        <!-- Route -->
        <div class="flex items-center justify-between mb-5">
          <div>
            <p class="text-3xl font-bold text-gray-800">{{ flight.origin?.code ?? '---' }}</p>
            <p class="text-sm text-gray-500">{{ flight.origin?.city }}</p>
            <p class="text-xl font-semibold text-gray-700 mt-1">{{ flight.departure_time?.substring(0, 5) }}</p>
          </div>
          <div class="flex-1 text-center px-6">
            <div class="flex items-center">
              <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
              <svg class="w-6 h-6 text-blue-400 mx-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
              </svg>
              <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
              {{ flight.route?.estimated_duration_minutes
                  ? formatDuration(flight.route.estimated_duration_minutes)
                  : flight.estimated_duration_minutes
                    ? formatDuration(Number(flight.estimated_duration_minutes))
                    : '' }}
            </p>
            <p class="text-xs text-gray-400">{{ flight.departure_date }}</p>
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold text-gray-800">{{ flight.destination?.code ?? '---' }}</p>
            <p class="text-sm text-gray-500">{{ flight.destination?.city }}</p>
            <p class="text-xl font-semibold text-gray-700 mt-1">{{ flight.arrival_time?.substring(0, 5) }}</p>
          </div>
        </div>

        <!-- Price & Availability -->
        <div class="flex items-center justify-between bg-blue-50 rounded-xl p-4">
          <div>
            <p class="text-sm text-gray-500">Harga per orang (mulai dari)</p>
            <p class="text-2xl font-bold text-blue-600">
              Rp {{ formatPrice(flight.base_price) }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Kursi tersedia</p>
            <p class="text-xl font-bold" :class="flight.seats_available < 10 ? 'text-orange-500' : 'text-gray-700'">
              {{ flight.seats_available }}
              <span v-if="flight.seats_available < 10" class="text-sm font-normal text-orange-500">tersisa!</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Flight not found -->
      <div v-else-if="!flightLoading" class="bg-white rounded-2xl shadow-lg p-10 mb-6 text-center text-gray-400">
        <p class="text-4xl mb-3">✈️</p>
        <p>Data penerbangan tidak ditemukan.</p>
      </div>

      <!-- Seat Map -->
      <SeatMap
        v-if="flight"
        :flightId="props.flightId"
        @seats-selected="handleSeatsSelected"
      />

      <!-- CTA Buttons -->
      <div v-if="selectedSeats.length > 0" class="mt-6 flex gap-4">
        <button
          @click="cancelSelection"
          class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
        >
          Batal
        </button>
        <button
          @click="proceedToBooking"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors shadow-md hover:shadow-lg"
        >
          Lanjutkan ke Booking — {{ selectedSeats.length }} kursi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import SeatMap from '@/Pages/Flight/SeatMap.vue'
import { httpClient } from '@/utils/http'
import type { Flight } from '@/types/flight'
import type { LockedSeat } from '@/composables/useSeatLock'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
}>()

// ─── State ────────────────────────────────────────────────────────────────────
const flight = ref<Flight | null>(null)
const flightLoading = ref(true)
const selectedSeats = ref<LockedSeat[]>([])

// ─── Handlers ────────────────────────────────────────────────────────────────

const handleSeatsSelected = (seats: LockedSeat[]) => {
  selectedSeats.value = seats
}

const proceedToBooking = () => {
  if (!flight.value) return

  // Simpan data seat lengkap ke sessionStorage agar Create.vue bisa baca
  sessionStorage.setItem('selectedSeats', JSON.stringify(selectedSeats.value))

  // Simpan juga data flight agar Create.vue tidak perlu fetch ulang
  sessionStorage.setItem('selectedFlight', JSON.stringify(flight.value))

  router.visit(`/booking/create?flightId=${props.flightId}`)
}

const cancelSelection = () => {
  // SeatMap.vue & useSeatLock akan auto-unlock via event dari parent
  // Cukup reset array lokal — SeatMap handles unlocking internally
  selectedSeats.value = []
}

const goBack = () => {
  window.history.back()
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

const formatPrice = (price: number) =>
  new Intl.NumberFormat('id-ID').format(Math.round(price))

const formatDuration = (minutes: number) => {
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}j ${m}m` : `${m}m`
}

const statusClass = (status: string) => {
  const map: Record<string, string> = {
    scheduled: 'bg-blue-100 text-blue-700',
    boarding: 'bg-green-100 text-green-700',
    in_flight: 'bg-indigo-100 text-indigo-700',
    landed: 'bg-gray-100 text-gray-700',
    cancelled: 'bg-red-100 text-red-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-700'
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  try {
    const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
    flight.value = res.data.data ?? res.data
  } catch (err) {
    console.error('[Detail.vue] Failed to load flight:', err)
  } finally {
    flightLoading.value = false
  }
})
</script>
