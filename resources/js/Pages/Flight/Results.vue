<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'
import { httpClient } from '@/utils/http'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  origin?: string
  destination?: string
  dates?: string
  passengers?: string
}>()

// ─── Airports ─────────────────────────────────────────────────────────────────
const airports = [
  { code: 'CGK', city: 'Jakarta',   name: 'Soekarno-Hatta' },
  { code: 'HLP', city: 'Jakarta',   name: 'Halim Perdanakusuma' },
  { code: 'DPS', city: 'Denpasar',  name: 'Ngurah Rai' },
  { code: 'SUB', city: 'Surabaya',  name: 'Juanda' },
  { code: 'KNO', city: 'Medan',     name: 'Kualanamu' },
  { code: 'UPG', city: 'Makassar',  name: 'Sultan Hasanuddin' },
]

function airportLabel(code: string) {
  const a = airports.find(x => x.code === code)
  return a ? `${a.city} (${a.code})` : code
}

// ─── State ────────────────────────────────────────────────────────────────────
const origin      = ref(props.origin ?? '')
const destination = ref(props.destination ?? '')
const date        = ref(props.dates ?? '')
const passengerCount = ref(1)

const loading  = ref(false)
const searched = ref(false)
const flights  = ref<any[]>([])
const error    = ref('')

// ─── Parse passengers ─────────────────────────────────────────────────────────
onMounted(() => {
  if (props.passengers) {
    const m = props.passengers.match(/(\d+)\s*Dewasa/)
    if (m) passengerCount.value = parseInt(m[1])
  }
  if (origin.value && destination.value && date.value) {
    search()
  }
})

// ─── Search ───────────────────────────────────────────────────────────────────
async function search() {
  if (!origin.value || !destination.value || !date.value) {
    error.value = 'Isi semua kolom'
    return
  }
  loading.value = true
  searched.value = false
  error.value = ''
  flights.value = []

  try {
    const res = await httpClient.get('/api/v1/flights/search', {
      params: { origin: origin.value, destination: destination.value, departure_date: date.value }
    })
    flights.value = res.data?.data ?? []
    searched.value = true
  } catch {
    error.value = 'Gagal menghubungi server. Pastikan Go service berjalan.'
    searched.value = true
  } finally {
    loading.value = false
  }
}

// ─── Select flight ────────────────────────────────────────────────────────────
function pilihPenerbangan(flight: any) {
  router.visit(route('booking.review', {
    flightId:       flight.id,
    passengerCount: passengerCount.value,
    adultCount:     passengerCount.value,
    childCount:     0,
    infantCount:    0,
  }))
}

// ─── Format helpers ───────────────────────────────────────────────────────────
function formatTime(t?: string) {
  if (!t) return '—'
  return t.slice(0, 5)
}

function formatDuration(mins?: string | number) {
  const m = parseInt(String(mins ?? 0))
  if (!m) return '—'
  return `${Math.floor(m / 60)}j ${m % 60}m`
}

function formatCurrency(n: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  }).format(n)
}

function formatDate(d: string) {
  if (!d) return '—'
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  }).format(new Date(d))
}

const passengerOptions = ['1 Dewasa','2 Dewasa','3 Dewasa','4 Dewasa','2 Dewasa, 1 Anak','2 Dewasa, 2 Anak']
</script>

<template>
  <Head title="Hasil Pencarian Penerbangan — Jelajahin" />

  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Navbar />

    <main class="flex-1 pt-24 pb-16">
      <div class="max-w-5xl mx-auto px-4 sm:px-6">

        <!-- Search bar (ringkas) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
          <div class="flex flex-wrap gap-3 items-end">

            <!-- Asal -->
            <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
              <label class="text-[11px] font-bold uppercase text-gray-400 tracking-wide">Asal</label>
              <select v-model="origin" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white">
                <option value="" disabled>Pilih asal</option>
                <option v-for="ap in airports" :key="ap.code" :value="ap.code">{{ ap.city }} ({{ ap.code }})</option>
              </select>
            </div>

            <!-- Swap -->
            <button
              class="mb-0.5 p-2 rounded-xl border border-gray-200 hover:border-teal hover:text-teal transition-colors text-gray-400"
              @click="[origin, destination] = [destination, origin]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
              </svg>
            </button>

            <!-- Tujuan -->
            <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
              <label class="text-[11px] font-bold uppercase text-gray-400 tracking-wide">Tujuan</label>
              <select v-model="destination" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white">
                <option value="" disabled>Pilih tujuan</option>
                <option v-for="ap in airports" :key="ap.code" :value="ap.code">{{ ap.city }} ({{ ap.code }})</option>
              </select>
            </div>

            <!-- Tanggal -->
            <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
              <label class="text-[11px] font-bold uppercase text-gray-400 tracking-wide">Tanggal</label>
              <input
                v-model="date"
                type="date"
                min="2026-09-27"
                class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white"
              />
            </div>

            <!-- Penumpang -->
            <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
              <label class="text-[11px] font-bold uppercase text-gray-400 tracking-wide">Penumpang</label>
              <select v-model="passengerCount" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white">
                <option v-for="n in [1,2,3,4,5,6]" :key="n" :value="n">{{ n }} Penumpang</option>
              </select>
            </div>

            <!-- Search btn -->
            <button
              class="bg-teal text-white font-bold px-6 py-2.5 rounded-xl hover:bg-teal/90 transition-colors flex items-center gap-2 shrink-0"
              @click="search"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
              </svg>
              Cari
            </button>
          </div>

          <p v-if="error" class="text-xs text-red-500 mt-2">{{ error }}</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-24 gap-4">
          <div class="w-12 h-12 border-4 border-teal border-t-transparent rounded-full animate-spin" />
          <p class="text-gray-500 text-sm">Mencari penerbangan terbaik untukmu…</p>
        </div>

        <!-- Hasil -->
        <template v-else-if="searched">

          <!-- Header hasil -->
          <div class="flex items-center justify-between mb-4" v-if="flights.length > 0">
            <div>
              <h1 class="text-lg font-bold text-gray-900">
                {{ airportLabel(origin) }} → {{ airportLabel(destination) }}
              </h1>
              <p class="text-sm text-gray-400">{{ formatDate(date) }} · {{ passengerCount }} penumpang</p>
            </div>
            <span class="text-sm font-semibold text-teal bg-teal/10 px-3 py-1 rounded-full">
              {{ flights.length }} penerbangan
            </span>
          </div>

          <!-- Tidak ada hasil -->
          <div v-if="flights.length === 0" class="text-center py-24">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
              </svg>
            </div>
            <p class="font-bold text-gray-700">Tidak ada penerbangan ditemukan</p>
            <p class="text-sm text-gray-400 mt-1">Coba ubah tanggal atau rute pencarian</p>
          </div>

          <!-- Flight cards -->
          <div v-else class="space-y-3">
            <div
              v-for="flight in flights"
              :key="flight.id"
              class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-teal/30 transition-all cursor-pointer group"
              @click="pilihPenerbangan(flight)"
            >
              <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">

                <!-- Maskapai -->
                <div class="flex items-center gap-3 w-44 shrink-0">
                  <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900">{{ flight.airline?.name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ flight.flight_number }} · Ekonomi</p>
                  </div>
                </div>

                <!-- Route -->
                <div class="flex-1 flex items-center gap-4">
                  <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ formatTime(flight.departure_time) }}</p>
                    <p class="text-xs font-semibold text-gray-500">{{ flight.origin?.code ?? origin }}</p>
                  </div>

                  <div class="flex-1 flex flex-col items-center gap-1">
                    <p class="text-xs text-gray-400">{{ formatDuration(flight.estimated_duration_minutes) }}</p>
                    <div class="flex items-center w-full">
                      <div class="flex-1 h-px bg-gray-200" />
                      <svg class="w-3.5 h-3.5 text-teal mx-1 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                      </svg>
                      <div class="flex-1 h-px bg-gray-200" />
                    </div>
                    <p class="text-xs text-gray-400">Langsung</p>
                  </div>

                  <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ formatTime(flight.arrival_time) }}</p>
                    <p class="text-xs font-semibold text-gray-500">{{ flight.destination?.code ?? destination }}</p>
                  </div>
                </div>

                <!-- Harga + CTA -->
                <div class="flex flex-col items-end gap-2 shrink-0">
                  <div class="text-right">
                    <p class="text-xl font-bold text-teal">{{ formatCurrency(flight.base_price) }}</p>
                    <p class="text-xs text-gray-400">per orang</p>
                  </div>
                  <button class="bg-teal text-white text-sm font-bold px-5 py-2 rounded-xl group-hover:bg-teal/90 transition-colors">
                    Pilih
                  </button>
                </div>

              </div>

              <!-- Footer card -->
              <div class="px-5 py-2.5 border-t border-gray-50 flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                  {{ flight.seats_available ?? '—' }} kursi tersedia
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  {{ formatDate(flight.departure_date ?? date) }}
                </span>
              </div>

            </div>
          </div>

        </template>

        <!-- Belum search -->
        <div v-else class="text-center py-24 text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
          </svg>
          <p class="font-semibold">Pilih asal, tujuan, dan tanggal lalu klik Cari</p>
        </div>

      </div>
    </main>

    <AppFooter />
  </div>
</template>
