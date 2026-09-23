<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'
import { httpClient } from '@/utils/http'

// ─── Search form state ─────────────────────────────────────────────────────────

const airports = [
  { code: 'CGK', city: 'Jakarta', name: 'Soekarno-Hatta' },
  { code: 'DPS', city: 'Bali', name: 'Ngurah Rai' },
  { code: 'SUB', city: 'Surabaya', name: 'Juanda' },
  { code: 'UPG', city: 'Makassar', name: 'Hasanuddin' },
  { code: 'KNO', city: 'Medan', name: 'Kualanamu' },
  { code: 'BPN', city: 'Balikpapan', name: 'Sultan Aji Muhammad Sulaiman' },
  { code: 'PLM', city: 'Palembang', name: 'Sultan Mahmud Badaruddin II' },
  { code: 'LOP', city: 'Lombok', name: 'International Lombok' },
]

const searchParams = reactive({
  origin: 'CGK',
  destination: '',
  departure_date: formatDateForInput(new Date(Date.now() + 2 * 86400000)),
  return_date: formatDateForInput(new Date(Date.now() + 3 * 86400000)),
  passengers: 1,
  seat_class: 'Ekonomi',
  round_trip: true,
})

function formatDateForInput(d: Date) {
  return d.toISOString().split('T')[0]
}

function formatDateDisplay(dateStr: string) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' })
}

const originAirport = computed(() => airports.find(a => a.code === searchParams.origin))
const destinationAirport = computed(() => airports.find(a => a.code === searchParams.destination))

function swapAirports() {
  const tmp = searchParams.origin
  searchParams.origin = searchParams.destination
  searchParams.destination = tmp
}

// ─── Search results ─────────────────────────────────────────────────────────────

const loading = ref(false)
const searched = ref(false)
const error = ref('')
const flights = ref<any[]>([])

const handleSelectFlight = (flight: any) => {
  sessionStorage.setItem('selectedFlight', JSON.stringify(flight))
  router.visit(`/booking/create?flightId=${flight.id}`)
}

function buildMockFlight() {
  return {
    id: 'mock-' + Date.now(),
    flight_number: 'JL-' + Math.random().toString(36).substring(2, 6).toUpperCase(),
    airline: { name: 'Jelajahin Air' },
    origin: { code: searchParams.origin, city: originAirport.value?.city ?? searchParams.origin },
    destination: { code: searchParams.destination, city: destinationAirport.value?.city ?? searchParams.destination },
    departure_time: new Date(searchParams.departure_date + 'T07:00:00').toISOString(),
    arrival_time: new Date(searchParams.departure_date + 'T09:30:00').toISOString(),
    base_price: 1_250_000,
    seats_available: 42,
  }
}

const handleSearch = async () => {
  if (!searchParams.origin || !searchParams.destination || !searchParams.departure_date) {
    error.value = 'Isi semua kolom terlebih dahulu'
    return
  }
  if (searchParams.origin === searchParams.destination) {
    error.value = 'Kota asal dan tujuan tidak boleh sama'
    return
  }

  loading.value = true
  error.value = ''

  try {
    const response = await httpClient.get('/api/v1/flights/search', {
      params: {
        origin: searchParams.origin,
        destination: searchParams.destination,
        departure_date: searchParams.departure_date,
      }
    })
    const results: any[] = response.data.data || []

    // Ada hasil → pakai flight pertama dari API
    if (results.length > 0) {
      handleSelectFlight(results[0])
      return
    }

    // API sukses tapi kosong → pakai mock, tetap navigate
    handleSelectFlight(buildMockFlight())

  } catch {
    // Backend mati / error → pakai mock, tetap navigate
    handleSelectFlight(buildMockFlight())

  } finally {
    loading.value = false
  }
}

// Format currency
function formatRupiah(val: number) {
  return 'Rp ' + val.toLocaleString('id-ID')
}

// Format duration
function formatDuration(dep: string, arr: string) {
  const diff = new Date(arr).getTime() - new Date(dep).getTime()
  const h = Math.floor(diff / 3600000)
  const m = Math.floor((diff % 3600000) / 60000)
  return `${h}j ${m}m`
}
</script>

<template>
  <Head title="Cari Penerbangan – Jelajahin" />

  <div class="min-h-screen bg-white font-sans">
    <!-- Navbar (transparent overlay di atas hero) -->
    <Navbar :transparent="true" />

    <!-- ═══ HERO SECTION ═══════════════════════════════════════════════════════ -->
    <section
      class="relative min-h-screen flex items-center overflow-hidden"
      aria-label="Cari Penerbangan"
    >
      <!-- Background Image -->
      <div class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=1600&auto=format&fit=crop&q=80"
          alt=""
          aria-hidden="true"
          class="w-full h-full object-cover object-center"
          loading="eager"
        />
        <!-- Overlay gradient: heavy on right side for form legibility, lighter on left -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-black/20 to-black/10" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent" />
      </div>

      <!-- Content -->
      <div class="relative z-10 w-full max-w-7xl mx-auto px-6 pt-24 pb-16 flex flex-col lg:flex-row items-center gap-10">

        <!-- LEFT: tagline -->
        <div class="flex-1 text-white max-w-xl">
          <h1 class="text-4xl md:text-5xl font-black leading-[1.12] tracking-tight drop-shadow-md">
            Pesan tiket pesawat<br />
            dan jadwal<br />
            penerbangan hari ini
          </h1>
          <p class="mt-5 text-white/75 text-base leading-relaxed max-w-md">
            Temukan ribuan penerbangan dengan harga terbaik. Cepat, mudah, dan terpercaya.
          </p>
        </div>

        <!-- RIGHT: search form card -->
        <div class="w-full lg:w-[420px] xl:w-[460px] shrink-0">
          <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

            <!-- From / To block -->
            <div class="relative">
              <!-- From -->
              <div class="px-5 pt-5 pb-3 border-b border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Dari</p>
                <div class="flex items-center gap-2">
                  <select
                    v-model="searchParams.origin"
                    class="w-full text-base font-bold text-gray-900 border-none outline-none p-0 focus:ring-0 bg-transparent cursor-pointer appearance-none"
                    aria-label="Kota asal"
                  >
                    <option value="">Pilih kota asal</option>
                    <option
                      v-for="ap in airports"
                      :key="ap.code"
                      :value="ap.code"
                    >
                      {{ ap.city }} {{ ap.code }}
                    </option>
                  </select>
                </div>
                <p v-if="originAirport" class="text-xs text-gray-400 mt-0.5">
                  {{ originAirport.name }}
                </p>
              </div>

              <!-- Swap button -->
              <button
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center shadow transition-all z-10"
                aria-label="Tukar kota asal dan tujuan"
                @click="swapAirports"
              >
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
              </button>

              <!-- To -->
              <div class="px-5 py-3 pb-5">
                <p class="text-xs text-gray-400 mb-1">Ke</p>
                <select
                  v-model="searchParams.destination"
                  :class="[
                    'w-full text-base font-bold border-none outline-none p-0 focus:ring-0 bg-transparent cursor-pointer appearance-none transition-colors',
                    searchParams.destination ? 'text-gray-900' : 'text-gray-400 font-normal'
                  ]"
                  aria-label="Kota tujuan"
                >
                  <option value="">Mau ke mana?</option>
                  <option
                    v-for="ap in airports"
                    :key="ap.code"
                    :value="ap.code"
                  >
                    {{ ap.city }} {{ ap.code }}
                  </option>
                </select>
                <p v-if="destinationAirport" class="text-xs text-gray-400 mt-0.5">
                  {{ destinationAirport.name }}
                </p>
              </div>
            </div>

            <!-- Dates block -->
            <div class="border-t border-gray-100">
              <div class="grid grid-cols-2 divide-x divide-gray-100">
                <!-- Departure date -->
                <div class="px-5 py-4">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-xs text-gray-400">Pergi</p>
                    <label class="flex items-center gap-1.5 cursor-pointer select-none" title="Aktifkan pulang-pergi">
                      <span class="text-xs text-gray-500">Pulang-pergi?</span>
                      <button
                        type="button"
                        :class="[
                          'w-10 h-5 rounded-full transition-all relative shrink-0',
                          searchParams.round_trip ? 'bg-blue-500' : 'bg-gray-200'
                        ]"
                        :aria-checked="searchParams.round_trip"
                        role="switch"
                        aria-label="Toggle pulang-pergi"
                        @click="searchParams.round_trip = !searchParams.round_trip"
                      >
                        <span
                          :class="[
                            'absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform',
                            searchParams.round_trip ? 'translate-x-5' : 'translate-x-0.5'
                          ]"
                        />
                      </button>
                    </label>
                  </div>
                  <p class="text-base font-bold text-gray-900">
                    {{ formatDateDisplay(searchParams.departure_date) }}
                  </p>
                  <input
                    v-model="searchParams.departure_date"
                    type="date"
                    class="mt-1 text-xs text-gray-400 border border-gray-100 rounded-lg px-2 py-1 focus:ring-1 focus:ring-blue-300 focus:border-blue-300 outline-none bg-white w-full"
                    aria-label="Tanggal pergi"
                  />
                </div>

                <!-- Return date -->
                <div
                  :class="[
                    'px-5 py-4 transition-opacity',
                    searchParams.round_trip ? 'opacity-100' : 'opacity-30 pointer-events-none'
                  ]"
                >
                  <p class="text-xs text-gray-400 mb-1">Pulang</p>
                  <p class="text-base font-bold text-gray-900">
                    {{ searchParams.round_trip ? formatDateDisplay(searchParams.return_date) : '—' }}
                  </p>
                  <input
                    v-model="searchParams.return_date"
                    type="date"
                    :disabled="!searchParams.round_trip"
                    class="mt-1 text-xs text-gray-400 border border-gray-100 rounded-lg px-2 py-1 focus:ring-1 focus:ring-blue-300 focus:border-blue-300 outline-none bg-white w-full disabled:opacity-40"
                    aria-label="Tanggal pulang"
                  />
                </div>
              </div>
            </div>

            <!-- Passengers & class block -->
            <div class="px-5 py-4 border-t border-gray-100">
              <div class="flex items-center gap-3">
                <!-- Passenger count -->
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600"
                    aria-label="Kurangi penumpang"
                    @click="searchParams.passengers = Math.max(1, searchParams.passengers - 1)"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14"/></svg>
                  </button>
                  <span class="text-sm font-bold text-gray-900 w-5 text-center">{{ searchParams.passengers }}</span>
                  <button
                    type="button"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600"
                    aria-label="Tambah penumpang"
                    @click="searchParams.passengers = Math.min(9, searchParams.passengers + 1)"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                  </button>
                </div>

                <span class="text-sm text-gray-500">Penumpang,</span>

                <!-- Seat class -->
                <select
                  v-model="searchParams.seat_class"
                  class="text-sm font-semibold text-gray-900 border-none outline-none p-0 focus:ring-0 bg-transparent cursor-pointer"
                  aria-label="Kelas kursi"
                >
                  <option value="Ekonomi">Ekonomi</option>
                  <option value="Bisnis">Bisnis</option>
                  <option value="First Class">First Class</option>
                </select>
              </div>
            </div>

            <!-- Error -->
            <div v-if="error" class="px-5 pb-3">
              <p class="text-sm text-red-500 bg-red-50 rounded-lg px-3 py-2">{{ error }}</p>
            </div>

            <!-- CTA Button -->
            <div class="px-5 pb-5">
              <button
                type="button"
                :disabled="loading"
                class="w-full bg-blue-500 hover:bg-blue-600 disabled:opacity-70 disabled:cursor-not-allowed text-white font-bold text-base py-3.5 rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all"
                @click="handleSearch"
              >
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  Mencari…
                </span>
                <span v-else>Ayo Cari</span>
              </button>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- ═══ RESULTS SECTION ════════════════════════════════════════════════════ -->
    <main v-if="searched || flights.length > 0" class="bg-gray-50 min-h-screen">
      <div class="max-w-5xl mx-auto px-6 py-12">

        <!-- Results Header -->
        <div v-if="!loading && flights.length > 0" class="mb-6">
          <h2 class="text-2xl font-black text-gray-900">
            {{ flights.length }} penerbangan ditemukan
          </h2>
          <p class="text-sm text-gray-500 mt-1">
            <span class="font-semibold">{{ originAirport?.city || searchParams.origin }}</span>
            →
            <span class="font-semibold">{{ destinationAirport?.city || searchParams.destination }}</span>
            · {{ formatDateDisplay(searchParams.departure_date) }}
            · {{ searchParams.passengers }} penumpang · {{ searchParams.seat_class }}
          </p>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-4">
          <div v-for="i in 3" :key="i" class="bg-white rounded-2xl p-6 animate-pulse">
            <div class="flex items-center justify-between">
              <div class="space-y-2">
                <div class="h-4 w-32 bg-gray-200 rounded"/>
                <div class="h-3 w-48 bg-gray-100 rounded"/>
              </div>
              <div class="h-8 w-28 bg-gray-200 rounded-lg"/>
            </div>
          </div>
        </div>

        <!-- Flight cards -->
        <div v-else-if="flights.length > 0" class="space-y-4">
          <article
            v-for="flight in flights"
            :key="flight.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-100 transition-all cursor-pointer group"
            @click="handleSelectFlight(flight)"
          >
            <div class="p-6">
              <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-between">

                <!-- Airline + Flight number -->
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                      <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900">{{ flight.flight_number }}</p>
                    <p class="text-xs text-gray-400">{{ flight.airline?.name || 'Maskapai' }}</p>
                  </div>
                </div>

                <!-- Times -->
                <div class="flex items-center gap-4 flex-1 justify-center">
                  <div class="text-center">
                    <p class="text-xl font-black text-gray-900">
                      {{ new Date(flight.departure_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                    <p class="text-xs text-gray-400">{{ flight.origin_code || searchParams.origin }}</p>
                  </div>

                  <div class="flex flex-col items-center gap-1 flex-1 min-w-[80px]">
                    <p class="text-xs text-gray-400">
                      {{ formatDuration(flight.departure_time, flight.arrival_time) }}
                    </p>
                    <div class="relative w-full flex items-center">
                      <div class="flex-1 h-px bg-gray-200"/>
                      <svg class="w-4 h-4 text-blue-400 mx-1 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                      </svg>
                      <div class="flex-1 h-px bg-gray-200"/>
                    </div>
                    <p class="text-xs text-green-600 font-medium">Langsung</p>
                  </div>

                  <div class="text-center">
                    <p class="text-xl font-black text-gray-900">
                      {{ new Date(flight.arrival_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                    <p class="text-xs text-gray-400">{{ flight.destination_code || searchParams.destination }}</p>
                  </div>
                </div>

                <!-- Price + CTA -->
                <div class="text-right shrink-0">
                  <p class="text-xs text-gray-400 mb-0.5">Mulai dari</p>
                  <p class="text-xl font-black text-blue-500">
                    {{ formatRupiah(flight.base_price) }}
                  </p>
                  <p class="text-xs text-gray-400 mb-2">/orang</p>
                  <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"/>
                    {{ flight.seats_available }} kursi tersedia
                  </span>
                </div>

              </div>
            </div>

            <!-- Bottom bar -->
            <div class="px-6 py-3 border-t border-gray-50 bg-gray-50/50 rounded-b-2xl flex items-center justify-between">
              <div class="flex items-center gap-3 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                  Bagasi 20kg
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                  </svg>
                  Kelas {{ searchParams.seat_class }}
                </span>
              </div>
              <span class="text-xs font-bold text-blue-500 group-hover:underline flex items-center gap-1">
                Pilih kursi
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </span>
            </div>
          </article>
        </div>

        <!-- Empty state -->
        <div v-else-if="!loading && searched" class="text-center py-20">
          <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-9 h-9 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak ada penerbangan</h3>
          <p class="text-gray-500 text-sm max-w-sm mx-auto">
            Tidak ditemukan penerbangan untuk rute dan tanggal yang dipilih. Coba ubah tanggal atau rute perjalanan.
          </p>
        </div>

      </div>
    </main>

    <AppFooter />
  </div>
</template>

<style scoped>
/* Custom select arrow removal for cleaner look */
select.appearance-none {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}
</style>
