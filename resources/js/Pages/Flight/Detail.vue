<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Back Button -->
      <button @click="goBack" class="mb-6 text-blue-600 hover:text-blue-800 flex items-center gap-2">
        ← Kembali
      </button>

      <!-- Flight Info -->
      <div v-if="flight" class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm text-gray-600">Penerbangan</p>
            <p class="font-bold">{{ flight.flight_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Keberangkatan</p>
            <p class="font-bold">{{ flight.departure_time }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tiba</p>
            <p class="font-bold">{{ flight.arrival_time }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Harga Dasar</p>
            <p class="font-bold text-blue-600">Rp {{ flight.base_price.toLocaleString('id-ID') }}</p>
          </div>
        </div>
      </div>

      <!-- Seat Map -->
      <SeatMap 
        v-if="flight"
        :flightId="flight.id"
        @seats-selected="handleSeatsSelected"
      />

      <!-- Action Buttons -->
      <div v-if="selectedSeats.length > 0" class="mt-8 flex gap-4">
        <button 
          @click="proceedToBooking"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg"
        >
          Lanjutkan ke Booking ({{ selectedSeats.length }} kursi)
        </button>
        <button 
          @click="cancelSeats"
          class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 rounded-lg"
        >
          Batal
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import SeatMap from '@/Pages/Flight/SeatMap.vue'
import { httpClient } from '@/utils/http'

const props = defineProps<{ flightId: string }>()

const flight = ref<any>(null)
const selectedSeats = ref<Array<{seatNumber: string, seatId: string}>>([])

onMounted(async () => {
  const flightId = props.flightId
  try {
    const response = await httpClient.get(`/api/v1/flights/${flightId}`)
    flight.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch flight:', error)
  }
})

const handleSeatsSelected = (seats: Array<{seatNumber: string, seatId: string}>) => {
  selectedSeats.value = seats
}

const proceedToBooking = () => {
  console.log('Proceed to booking with seats:', selectedSeats.value)
  // router.push({ name: 'booking.create', params: { flightId: flight.value.id } })
}

const cancelSeats = () => {
  selectedSeats.value = []
}

const goBack = () => {
  window.history.back()
}
</script>
