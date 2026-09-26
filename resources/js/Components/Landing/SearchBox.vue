<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const activeTab = ref<'flight' | 'hotel' | 'train' | 'car' | 'event'>('flight')

const origin = ref('')
const destination = ref('')
const dates = ref('')
const passengers = ref('2 Dewasa, 1 Anak')

// Airport options dari database
const airports = [
  { code: 'CGK', label: 'Jakarta (CGK) — Soekarno-Hatta' },
  { code: 'HLP', label: 'Jakarta (HLP) — Halim Perdanakusuma' },
  { code: 'DPS', label: 'Denpasar (DPS) — Ngurah Rai' },
  { code: 'SUB', label: 'Surabaya (SUB) — Juanda' },
  { code: 'KNO', label: 'Medan (KNO) — Kualanamu' },
  { code: 'UPG', label: 'Makassar (UPG) — Sultan Hasanuddin' },
]

const tabs = [
  {
    id: 'flight' as const,
    label: 'Penerbangan',
    icon: `<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>`,
  },
  {
    id: 'hotel' as const,
    label: 'Hotel',
    icon: `<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>`,
  },
  {
    id: 'train' as const,
    label: 'Kereta Api',
    icon: `<rect x="4" y="3" width="16" height="14" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="M8 19l-2 3"/><path d="M16 19l2 3"/>`,
  },
  {
    id: 'car' as const,
    label: 'Sewa Mobil',
    icon: `<path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v9a2 2 0 01-2 2h-3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>`,
  },
  {
    id: 'event' as const,
    label: 'Acara & Atraksi',
    icon: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>`,
  },
]

const placeholders = {
  flight: { origin: 'Jakarta (CGK)', dest: 'Tokyo (HND)', dates: '15 Nov – 22 Nov' },
  hotel:  { origin: 'Nama Hotel / Kota', dest: 'Bali, Indonesia', dates: 'Check-in – Check-out' },
  train:  { origin: 'Stasiun Gambir', dest: 'Stasiun Tugu, Jogja', dates: '20 Nov' },
  car:    { origin: 'Lokasi Pengambilan', dest: 'Tujuan Perjalanan', dates: 'Tanggal Sewa' },
  event:  { origin: 'Kota atau Venue', dest: 'Nama Acara', dates: 'Tanggal Acara' },
}

const currentPlaceholders = computed(() => placeholders[activeTab.value])

const passengerOptions = [
  '1 Dewasa',
  '2 Dewasa',
  '2 Dewasa, 1 Anak',
  '3 Dewasa',
  '4 Dewasa, 2 Anak',
]

const originError = ref(false)
const destError = ref(false)
const isSearching = ref(false)

function setTab(id: typeof activeTab.value) {
  activeTab.value = id
  origin.value = ''
  destination.value = ''
  dates.value = ''
}

function clearError(field: 'origin' | 'dest') {
  if (field === 'origin') originError.value = false
  if (field === 'dest') destError.value = false
}

function handleSearch() {
  originError.value = !origin.value.trim()
  destError.value = !destination.value.trim()

  if (originError.value || destError.value) return

  if (activeTab.value === 'flight') {
    isSearching.value = true
    router.visit(route('flights.results'), {
      method: 'get',
      data: {
        origin: origin.value,
        destination: destination.value,
        dates: dates.value,
        passengers: passengers.value,
      },
      onFinish: () => { isSearching.value = false },
    })
  }
}
</script>

<template>
  <section class="relative z-10" aria-label="Pencarian tiket">
    <div class="max-w-7xl mx-auto px-6">
      <div class="bg-white rounded-2xl shadow-2xl p-7 border border-gray-100">

        <!-- Tabs -->
        <div
          class="flex gap-0.5 border-b border-gray-200 mb-6 overflow-x-auto scrollbar-none"
          role="tablist"
          aria-label="Kategori pencarian"
        >
          <button
            v-for="tab in tabs"
            :key="tab.id"
            role="tab"
            :aria-selected="activeTab === tab.id"
            :class="[
              'flex items-center gap-2 px-4 py-2.5 pb-3 text-sm font-semibold whitespace-nowrap rounded-t-md border-b-2 -mb-px transition-all',
              activeTab === tab.id
                ? 'text-teal border-teal'
                : 'text-gray-500 border-transparent hover:text-navy'
            ]"
            @click="setTab(tab.id)"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
              <!-- eslint-disable-next-line vue/no-v-html -->
              <g v-html="tab.icon" />
            </svg>
            {{ tab.label }}
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSearch" aria-label="Form pencarian">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] gap-4 items-end">

            <!-- Origin -->
            <div class="flex flex-col gap-1.5">
              <label for="search-origin" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Asal</label>
              <div
                :class="[
                  'flex items-center gap-2.5 border-1.5 rounded-xl px-3.5 py-2.5 transition-all',
                  originError
                    ? 'border-red-400 ring-2 ring-red-100'
                    : 'border-gray-200 focus-within:border-teal focus-within:ring-2 focus-within:ring-teal/10'
                ]"
              >
                <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="12" cy="10" r="3"/><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 10-16 0c0 3 2.7 6.9 8 11.7z"/>
                </svg>
                <select
                  id="search-origin"
                  v-model="origin"
                  class="border-none outline-none text-sm font-medium text-navy w-full p-0 focus:ring-0 bg-transparent cursor-pointer"
                  aria-label="Kota asal"
                  @change="clearError('origin')"
                >
                  <option value="" disabled>Pilih kota asal</option>
                  <option v-for="ap in airports" :key="ap.code" :value="ap.code">{{ ap.label }}</option>
                </select>
              </div>
              <span v-if="originError" class="text-xs text-red-500">Pilih kota asal</span>
            </div>

            <!-- Destination -->
            <div class="flex flex-col gap-1.5">
              <label for="search-dest" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tujuan</label>
              <div
                :class="[
                  'flex items-center gap-2.5 border-1.5 rounded-xl px-3.5 py-2.5 transition-all',
                  destError
                    ? 'border-red-400 ring-2 ring-red-100'
                    : 'border-gray-200 focus-within:border-teal focus-within:ring-2 focus-within:ring-teal/10'
                ]"
              >
                <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="12" cy="10" r="3"/><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 10-16 0c0 3 2.7 6.9 8 11.7z"/>
                </svg>
                <select
                  id="search-dest"
                  v-model="destination"
                  class="border-none outline-none text-sm font-medium text-navy w-full p-0 focus:ring-0 bg-transparent cursor-pointer"
                  aria-label="Kota tujuan"
                  @change="clearError('dest')"
                >
                  <option value="" disabled>Pilih kota tujuan</option>
                  <option v-for="ap in airports" :key="ap.code" :value="ap.code">{{ ap.label }}</option>
                </select>
              </div>
              <span v-if="destError" class="text-xs text-red-500">Pilih kota tujuan</span>
            </div>

            <!-- Dates -->
            <div class="flex flex-col gap-1.5">
              <label for="search-dates" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</label>
              <div class="flex items-center gap-2.5 border border-gray-200 rounded-xl px-3.5 py-2.5 focus-within:border-teal focus-within:ring-2 focus-within:ring-teal/10 transition-all">
                <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                  <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <input
                  id="search-dates"
                  v-model="dates"
                  type="date"
                  min="2026-09-27"
                  class="border-none outline-none text-sm font-medium text-navy w-full p-0 focus:ring-0 bg-transparent cursor-pointer"
                  aria-label="Tanggal perjalanan"
                />
              </div>
            </div>

            <!-- Passengers -->
            <div class="flex flex-col gap-1.5">
              <label for="search-passengers" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Penumpang</label>
              <div class="flex items-center gap-2.5 border border-gray-200 rounded-xl px-3.5 py-2.5 focus-within:border-teal focus-within:ring-2 focus-within:ring-teal/10 transition-all">
                <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                <select
                  id="search-passengers"
                  v-model="passengers"
                  class="border-none outline-none text-sm font-medium text-navy w-full p-0 focus:ring-0 bg-transparent cursor-pointer"
                  aria-label="Jumlah penumpang"
                >
                  <option v-for="opt in passengerOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
            </div>

            <!-- CTA -->
            <div class="sm:col-span-2 lg:col-span-1">
              <button
                type="submit"
                :disabled="isSearching"
                class="flex items-center justify-center gap-2.5 w-full bg-gradient-to-br from-teal to-teal-dark text-white font-bold text-sm px-7 py-3 rounded-xl shadow-lg shadow-teal/30 hover:-translate-y-0.5 hover:shadow-teal/50 disabled:opacity-70 disabled:cursor-not-allowed transition-all"
                aria-label="Cari dan jelajahi"
              >
                <svg v-if="!isSearching" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ isSearching ? 'Mencari…' : 'CARI &amp; JELAJAHI' }}
              </button>
            </div>

          </div>

          <!-- Promo badge -->
          <div class="mt-4 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold px-4 py-2 rounded-xl">
            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Ticket Flash Sale: <span class="font-bold">Rp 0 DP, Bayar Nanti</span>
          </div>
        </form>

      </div>
    </div>
  </section>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { scrollbar-width: none; }
</style>
