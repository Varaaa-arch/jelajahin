<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-4xl font-bold text-gray-900 mb-8">Cari Penerbangan</h1>

      <!-- Search Form -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
        <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Dari</label>
            <select v-model="searchParams.origin" class="w-full px-4 py-2 border rounded-lg">
              <option value="">Pilih</option>
              <option value="CGK">Jakarta</option>
              <option value="DPS">Bali</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
            <select v-model="searchParams.destination" class="w-full px-4 py-2 border rounded-lg">
              <option value="">Pilih</option>
              <option value="CGK">Jakarta</option>
              <option value="DPS">Bali</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
            <input v-model="searchParams.departure_date" type="date" class="w-full px-4 py-2 border rounded-lg" />
          </div>

          <div class="flex items-end">
            <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-2 rounded-lg">
              {{ loading ? 'Mencari...' : 'Cari' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Error -->
      <div v-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-6">
        {{ error }}
      </div>

      <!-- Results -->
      <div v-if="flights.length > 0" class="space-y-4">
        <h2 class="text-2xl font-bold mb-6">{{ flights.length }} Penerbangan</h2>
        <div
          v-for="flight in flights"
          :key="flight.id"
          @click="handleSelectFlight(flight.id)"
          class="bg-white p-6 rounded-lg shadow cursor-pointer hover:shadow-lg transition"
        >
          <div class="flex justify-between items-center">
            <div>
              <p class="font-semibold">{{ flight.flight_number }}</p>
              <p class="text-sm text-gray-600">{{ flight.departure_time }} - {{ flight.arrival_time }}</p>
            </div>
            <div class="text-right">
              <p class="font-bold text-blue-600">Rp {{ (flight.base_price).toLocaleString('id-ID') }}</p>
              <p class="text-sm text-green-600">{{ flight.seats_available }} kursi</p>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!loading && searched" class="text-center py-12 text-gray-600">
        Tidak ada penerbangan
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { httpClient } from '@/utils/http'

const loading = ref(false)
const searched = ref(false)
const error = ref('')
const flights = ref<any[]>([])

const searchParams = reactive({
  origin: '',
  destination: '',
  departure_date: '',
})

const handleSelectFlight = (flightId: string) => {
  router.get(route('flight.detail', { flightId }))
}

const handleSearch = async () => {
  if (!searchParams.origin || !searchParams.destination || !searchParams.departure_date) {
    error.value = 'Isi semua field'
    return
  }

  loading.value = true
  error.value = ''
  searched.value = true

  try {
    const response = await httpClient.get('/api/v1/flights/search', {
      params: {
        origin: searchParams.origin,
        destination: searchParams.destination,
        departure_date: searchParams.departure_date,
      }
    })
    flights.value = response.data.data || []
  } catch (err: any) {
    error.value = err.message || 'Gagal mencari'
  } finally {
    loading.value = false
  }
}
</script>
