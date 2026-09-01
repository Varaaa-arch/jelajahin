<template>
  <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6 border-l-4 border-blue-600">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
      <!-- Airline Info -->
      <div class="md:col-span-1">
        <div class="text-sm text-gray-600">Maskapai</div>
        <div class="font-semibold text-gray-900">{{ flight.flight_number }}</div>
        <div class="text-xs text-gray-500">{{ flight.status }}</div>
      </div>

      <!-- Flight Times -->
      <div class="md:col-span-2">
        <div class="flex items-center justify-between">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">
              {{ formatTime(flight.departure_time) }}
            </div>
            <div class="text-xs text-gray-600">Keberangkatan</div>
          </div>

          <div class="flex-1 px-4">
            <div class="border-t-2 border-gray-300"></div>
            <div class="text-xs text-center text-gray-600 mt-1">
              {{ flight.estimated_duration_minutes || '~' }} menit
            </div>
          </div>

          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">
              {{ formatTime(flight.arrival_time) }}
            </div>
            <div class="text-xs text-gray-600">Tiba</div>
          </div>
        </div>
      </div>

      <!-- Price & Availability -->
      <div class="md:col-span-1 text-center">
        <div class="text-sm text-gray-600 mb-1">Harga Mulai</div>
        <div class="text-2xl font-bold text-blue-600">
          Rp {{ formatPrice(flight.base_price) }}
        </div>
        <div class="text-xs text-green-600 mt-2">
          {{ flight.seats_available }} kursi tersedia
        </div>
      </div>

      <!-- Select Button -->
      <div class="md:col-span-1">
        <button 
          @click="emit('select', flight)"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
        >
          Pilih
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Flight } from '@/types'

defineProps<{
  flight: Flight
}>()

const emit = defineEmits<{
  select: [flight: Flight]
}>()

const formatTime = (time: string) => {
  if (!time) return '-'
  return time.substring(0, 5)
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(Math.round(price))
}
</script>
