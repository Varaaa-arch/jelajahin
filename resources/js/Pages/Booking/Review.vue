<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import { httpClient } from '@/utils/http'
import type { Flight } from '@/types/flight'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
  passengerCount?: number
  adultCount?: number
  childCount?: number
  infantCount?: number
}>()

// ─── Stepper ─────────────────────────────────────────────────────────────────
const steps = [
  { number: 1, label: 'Penerbangan' },
  { number: 2, label: 'Data Penumpang' },
  { number: 3, label: 'Kursi' },
  { number: 4, label: 'Bagasi & Ekstra' },
  { number: 5, label: 'Review Akhir' },
  { number: 6, label: 'Pembayaran' },
  { number: 7, label: 'E-Tiket' },
]
const currentStep = 1

// ─── State ───────────────────────────────────────────────────────────────────
const flight = ref<Flight | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const adults   = ref(props.adultCount   ?? props.passengerCount ?? 1)
const children = ref(props.childCount   ?? 0)
const infants  = ref(props.infantCount  ?? 0)

// ─── Fetch flight ─────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const res = await httpClient.get(`/flights/${props.flightId}`)
    flight.value = res.data?.data ?? res.data
  } catch (e) {
    error.value = 'Gagal memuat data penerbangan.'
  } finally {
    loading.value = false
  }
})

// ─── Computed ─────────────────────────────────────────────────────────────────
const totalPassengers = computed(() => adults.value + children.value + infants.value)

const passengerLabel = computed(() => {
  const parts = []
  if (adults.value)   parts.push(`${adults.value} Dewasa`)
  if (children.value) parts.push(`${children.value} Anak`)
  if (infants.value)  parts.push(`${infants.value} Bayi`)
  return parts.join(', ')
})

const basePrice = computed(() =>
  (flight.value?.base_price ?? 0) * totalPassengers.value
)
const tax = computed(() =>
  (flight.value?.tax_surcharge ?? 0) * totalPassengers.value
)
const totalPrice = computed(() => basePrice.value + tax.value)

const departureDate = computed(() => {
  if (!flight.value?.departure_date) return '—'
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  }).format(new Date(flight.value.departure_date))
})

const duration = computed(() => {
  const mins = parseInt(flight.value?.estimated_duration_minutes ?? '0')
  if (!mins) return '—'
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return `${h}j ${m}m`
})

const originCode = computed(() =>
  flight.value?.origin?.code ?? '—'
)
const destCode = computed(() =>
  flight.value?.destination?.code ?? '—'
)
const originCity = computed(() =>
  flight.value?.origin?.city ?? '—'
)
const destCity = computed(() =>
  flight.value?.destination?.city ?? '—'
)
const airlineName = computed(() =>
  flight.value?.airline?.name ?? '—'
)

function formatTime(t?: string) {
  if (!t) return '—'
  return t.slice(0, 5)
}

function formatCurrency(n: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  }).format(n)
}

// ─── Actions ──────────────────────────────────────────────────────────────────
function lanjutKeDataPenumpang() {
  router.visit(route('booking.create', {
    flightId:       props.flightId,
    passengerCount: totalPassengers.value,
    adultCount:     adults.value,
    childCount:     children.value,
    infantCount:    infants.value,
  }))
}

function ubahPenerbangan() {
  router.visit(route('flight.detail', { flightId: props.flightId }))
}

function kembali() {
  router.visit(route('flight.detail', { flightId: props.flightId }))
}
</script>

<template>
  <Head title="Review Penerbangan — Jelajahin" />

  <div class="h-screen overflow-hidden bg-gray-50 flex flex-col">
    <Navbar />

    <main class="flex-1 overflow-hidden pt-24 pb-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Review Penerbangan</h1>
          <button
            class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors"
            @click="kembali"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail
          </button>
        </div>

        <!-- Stepper -->
        <div class="mb-8 overflow-x-auto">
          <div class="flex items-center min-w-max mx-auto">
            <template v-for="(step, idx) in steps" :key="step.number">
              <!-- Step -->
              <div class="flex flex-col items-center gap-1.5">
                <div
                  class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                  :class="step.number === currentStep
                    ? 'bg-teal border-teal text-white shadow-md shadow-teal/30'
                    : step.number < currentStep
                      ? 'bg-teal/20 border-teal text-teal'
                      : 'bg-white border-gray-200 text-gray-400'"
                >
                  <svg v-if="step.number < currentStep" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span v-else>{{ step.number }}</span>
                </div>
                <span
                  class="text-[11px] font-medium whitespace-nowrap"
                  :class="step.number === currentStep ? 'text-teal font-bold' : 'text-gray-400'"
                >
                  {{ step.label }}
                </span>
              </div>

              <!-- Connector -->
              <div
                v-if="idx < steps.length - 1"
                class="h-px w-10 sm:w-16 mx-1 mb-5 transition-colors"
                :class="step.number < currentStep ? 'bg-teal' : 'bg-gray-200'"
              />
            </template>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-24">
          <div class="w-10 h-10 border-4 border-teal border-t-transparent rounded-full animate-spin" />
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-24 text-red-500">{{ error }}</div>

        <!-- Content -->
        <div v-else class="flex flex-col lg:flex-row gap-6">

          <!-- Left: penerbangan + return -->
          <div class="flex-1 space-y-4">

            <!-- Card Penerbangan Pergi -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <!-- Header card -->
              <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                  </svg>
                  <div>
                    <p class="font-bold text-gray-900">Pergi</p>
                    <p class="text-xs text-gray-400">{{ departureDate }}</p>
                  </div>
                </div>
                <button
                  class="text-xs font-semibold text-teal border border-teal rounded-lg px-3 py-1.5 hover:bg-teal hover:text-white transition-all"
                  @click="ubahPenerbangan"
                >
                  UBAH PENERBANGAN
                </button>
              </div>

              <!-- Flight info -->
              <div class="px-6 py-5">
                <div class="flex items-center gap-4">
                  <!-- Airline logo placeholder -->
                  <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                    </svg>
                  </div>

                  <!-- Airline name & flight -->
                  <div class="w-36 shrink-0">
                    <p class="font-bold text-gray-900 text-sm">{{ airlineName }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ flight?.flight_number }} · Ekonomi</p>
                  </div>

                  <!-- Route -->
                  <div class="flex-1 flex items-center gap-3">
                    <!-- Departure -->
                    <div class="text-center">
                      <p class="text-2xl font-bold text-gray-900">{{ formatTime(flight?.departure_time) }}</p>
                      <p class="text-sm font-semibold text-gray-500">{{ originCode }}</p>
                      <p class="text-xs text-gray-400">({{ originCity }})</p>
                    </div>

                    <!-- Arrow + duration -->
                    <div class="flex-1 flex flex-col items-center gap-1">
                      <p class="text-xs text-gray-400">{{ duration }}</p>
                      <div class="relative w-full flex items-center">
                        <div class="flex-1 h-px bg-gray-200" />
                        <svg class="w-4 h-4 text-teal mx-1 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                        </svg>
                        <div class="flex-1 h-px bg-gray-200" />
                      </div>
                      <p class="text-xs text-gray-400">Langsung</p>
                    </div>

                    <!-- Arrival -->
                    <div class="text-center">
                      <p class="text-2xl font-bold text-gray-900">{{ formatTime(flight?.arrival_time) }}</p>
                      <p class="text-sm font-semibold text-gray-500">{{ destCode }}</p>
                      <p class="text-xs text-gray-400">({{ destCity }})</p>
                    </div>

                    <!-- Price -->
                    <div class="text-right ml-4">
                      <p class="text-lg font-bold text-teal">{{ formatCurrency(flight?.base_price ?? 0) }}</p>
                      <p class="text-xs text-gray-400">/ pax</p>
                      <button
                        class="text-xs text-teal underline mt-1 hover:text-teal/70"
                        @click="ubahPenerbangan"
                      >
                        Detail Penerbangan
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Tambah Penerbangan Pulang -->
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 hover:border-teal/50 transition-colors cursor-pointer group">
              <div class="flex flex-col items-center justify-center py-10 gap-3">
                <div class="w-10 h-10 rounded-full border-2 border-gray-300 group-hover:border-teal flex items-center justify-center transition-colors">
                  <svg class="w-5 h-5 text-gray-400 group-hover:text-teal transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                  </svg>
                </div>
                <div class="text-center">
                  <p class="font-bold text-gray-700 group-hover:text-teal transition-colors">Tambah Penerbangan Pulang</p>
                  <p class="text-sm text-gray-400 mt-0.5">Hemat lebih banyak dengan memesan tiket pulang-pergi.</p>
                </div>
              </div>
            </div>

          </div>

          <!-- Right: Ringkasan Pesanan -->
          <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm sticky top-24">

              <!-- Header -->
              <div class="flex items-center gap-2 px-5 pt-5 pb-4 border-b border-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h2 class="font-bold text-gray-900">Ringkasan Pesanan</h2>
              </div>

              <!-- Details -->
              <div class="px-5 py-4 space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Penumpang</span>
                  <span class="font-semibold text-gray-800">{{ passengerLabel }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Harga Dasar ({{ totalPassengers }}x)</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(basePrice) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Pajak & Biaya</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(tax) }}</span>
                </div>

                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                  <span class="font-bold text-gray-900">Total</span>
                  <span class="text-xl font-bold text-teal">{{ formatCurrency(totalPrice) }}</span>
                </div>
              </div>

              <!-- CTA -->
              <div class="px-5 pb-5">
                <button
                  class="w-full bg-teal text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-teal/90 active:scale-[0.98] transition-all shadow-lg shadow-teal/20"
                  @click="lanjutKeDataPenumpang"
                >
                  LANJUT KE DATA PENUMPANG
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
                <p class="text-center text-xs text-gray-400 mt-3">
                  Dengan menekan tombol lanjut, Anda menyetujui
                  <a href="/terms" class="text-teal underline hover:text-teal/70">Syarat &amp; Ketentuan</a>.
                </p>
              </div>

            </div>
          </div>

        </div>
      </div>
    </main>


  </div>
</template>
