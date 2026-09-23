<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PassengerForm from '@/Components/Booking/PassengerForm.vue'
import BookingSummary from '@/Components/Booking/BookingSummary.vue'
import PaymentMethod from '@/Components/Booking/PaymentMethod.vue'
import { httpClient } from '@/utils/http'
import { useSeatLock, type LockedSeat } from '@/composables/useSeatLock'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
}>()

// ─── State ────────────────────────────────────────────────────────────────────
const steps = [
  { label: 'Penumpang', icon: '👤' },
  { label: 'Ringkasan',  icon: '📋' },
  { label: 'Pembayaran', icon: '💳' },
]
const currentStep = ref(0)

const flight    = ref<any>(null)
const passengers = ref<any[]>([])
const selectedSeats = ref<LockedSeat[]>([])

const { unlockAll } = useSeatLock(props.flightId)

// ─── Computed ─────────────────────────────────────────────────────────────────
const totalAmount = computed(() => {
  if (!flight.value) return 0
  const base = (flight.value.base_price ?? 0) * (passengers.value.length || selectedSeats.value.length || 1)
  return Math.round(base * 1.1)
})

const flightOrigin = computed(() =>
  flight.value?.origin?.code ?? flight.value?.route?.origin_airport?.iata_code ?? '—'
)
const flightDest = computed(() =>
  flight.value?.destination?.code ?? flight.value?.route?.destination_airport?.iata_code ?? '—'
)
const flightOriginCity = computed(() =>
  flight.value?.origin?.city ?? flight.value?.route?.origin_airport?.city ?? 'Asal'
)
const flightDestCity = computed(() =>
  flight.value?.destination?.city ?? flight.value?.route?.destination_airport?.city ?? 'Tujuan'
)

function formatTime(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}
function formatDuration(dep: string, arr: string) {
  if (!dep || !arr) return '—'
  const diff = new Date(arr).getTime() - new Date(dep).getTime()
  const h = Math.floor(diff / 3600000)
  const m = Math.floor((diff % 3600000) / 60000)
  return `${h}j ${m}m`
}
function formatRupiah(val: number) {
  return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val))
}

// ─── Handlers ────────────────────────────────────────────────────────────────
const handlePassengersSubmitted = (data: any[]) => {
  passengers.value = data
  currentStep.value = 1
}

const handleProceedToPayment = () => {
  currentStep.value = 2
}

const handlePaymentMethodSelected = async (method: string) => {
  try {
    const res = await httpClient.post('/api/bookings', {
      flight_id: flight.value?.id,
      seat_ids: selectedSeats.value.map(s => s.seatId),
      passengers: passengers.value,
    })

    const bookingId = res.data.booking?.id
    const pnr = res.data.booking?.pnr_code

    sessionStorage.setItem('pendingBooking', JSON.stringify({ bookingId, totalAmount: totalAmount.value }))
    selectedSeats.value = []

    router.visit('/booking/payment', {
      method: 'get',
      data: {
        bookingId,
        pnr,
        total: totalAmount.value,
        flight: flight.value?.flight_number ?? '',
        passengers: passengers.value.length,
        method,
        origin: flightOrigin.value,
        originCity: flightOriginCity.value,
        destination: flightDest.value,
        destinationCity: flightDestCity.value,
      },
    })
  } catch {
    const pnr = 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase()
    sessionStorage.setItem('pendingBooking', JSON.stringify({ bookingId: null, totalAmount: totalAmount.value }))
    selectedSeats.value = []

    router.visit('/booking/payment', {
      method: 'get',
      data: {
        pnr,
        total: totalAmount.value,
        flight: flight.value?.flight_number ?? '',
        passengers: passengers.value.length,
        method,
        origin: flightOrigin.value,
        originCity: flightOriginCity.value,
        destination: flightDest.value,
        destinationCity: flightDestCity.value,
      },
    })
  }
}

const handleCancel = async () => {
  await unlockAll()
  sessionStorage.removeItem('selectedSeats')
  window.history.back()
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  const seatsRaw = sessionStorage.getItem('selectedSeats')
  if (seatsRaw) selectedSeats.value = JSON.parse(seatsRaw)

  const flightRaw = sessionStorage.getItem('selectedFlight')
  if (flightRaw) {
    flight.value = JSON.parse(flightRaw)
    return
  }

  if (props.flightId) {
    try {
      const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
      flight.value = res.data.data ?? res.data
    } catch (err) {
      console.error('[Create.vue] Failed to fetch flight:', err)
    }
  }
})

onBeforeUnmount(async () => {
  if (selectedSeats.value.length > 0) {
    await unlockAll()
    sessionStorage.removeItem('selectedSeats')
  }
})
</script>

<template>
  <Head title="Buat Pemesanan – Jelajahin" />

  <div class="min-h-screen bg-gray-50 font-sans">
    <!-- Navbar -->
    <Navbar :transparent="false" />

    <!-- ─── Page Header ──────────────────────────────────────────────────── -->
    <div class="bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-5xl mx-auto px-6 py-5 flex items-center gap-4">
        <!-- Back button -->
        <button
          type="button"
          class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 transition-all shrink-0"
          aria-label="Kembali"
          @click="handleCancel"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
        </button>

        <div class="flex-1">
          <h1 class="text-lg font-black text-gray-900 leading-tight">Lengkapi Pemesanan</h1>
          <p class="text-xs text-gray-400 mt-0.5">
            Isi detail penumpang, lalu lanjutkan ke pembayaran.
          </p>
        </div>

        <!-- Flight badge -->
        <div
          v-if="flight"
          class="hidden sm:flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-3 py-2"
        >
          <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
          </svg>
          <span class="text-xs font-bold text-blue-700">
            {{ flightOrigin }} → {{ flightDest }}
          </span>
          <span class="text-xs text-blue-400">{{ flight.flight_number }}</span>
        </div>
      </div>
    </div>

    <!-- ─── Main Content ──────────────────────────────────────────────────── -->
    <div class="max-w-5xl mx-auto px-6 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- LEFT: Stepper + Form -->
        <div class="lg:col-span-2 space-y-6">

          <!-- ─── Stepper ────────────────────────────────────────────────── -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-0">
              <template v-for="(step, idx) in steps" :key="idx">
                <!-- Step node -->
                <div class="flex flex-col items-center shrink-0">
                  <div
                    :class="[
                      'w-10 h-10 rounded-full flex items-center justify-center text-sm font-black transition-all shadow-sm',
                      currentStep > idx
                        ? 'bg-green-500 text-white shadow-green-200'
                        : currentStep === idx
                          ? 'bg-blue-500 text-white shadow-blue-200'
                          : 'bg-gray-100 text-gray-400'
                    ]"
                  >
                    <svg v-if="currentStep > idx" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path d="M5 13l4 4L19 7"/>
                    </svg>
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <p
                    :class="[
                      'text-xs mt-1.5 font-semibold whitespace-nowrap',
                      currentStep === idx ? 'text-blue-500' : currentStep > idx ? 'text-green-500' : 'text-gray-400'
                    ]"
                  >{{ step.label }}</p>
                </div>

                <!-- Connector -->
                <div v-if="idx < steps.length - 1" class="flex-1 mx-2 mb-5 relative h-0.5">
                  <div class="absolute inset-0 bg-gray-100 rounded-full"/>
                  <div
                    class="absolute inset-y-0 left-0 rounded-full transition-all duration-500"
                    :class="currentStep > idx ? 'bg-green-400' : 'bg-transparent'"
                    :style="{ width: currentStep > idx ? '100%' : '0%' }"
                  />
                </div>
              </template>
            </div>
          </div>

          <!-- ─── Step Forms ─────────────────────────────────────────────── -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Step 0: Passenger Form -->
            <div v-if="currentStep === 0">
              <div class="px-6 pt-5 pb-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-base">👤</div>
                <div>
                  <h2 class="text-base font-black text-gray-900">Data Penumpang</h2>
                  <p class="text-xs text-gray-400">Isi data sesuai KTP / Paspor yang berlaku</p>
                </div>
              </div>
              <div class="p-6">
                <PassengerForm
                  :seatCount="selectedSeats.length || 1"
                  @passengers-submitted="handlePassengersSubmitted"
                  @back="handleCancel"
                />
              </div>
            </div>

            <!-- Step 1: Booking Summary -->
            <div v-else-if="currentStep === 1">
              <div class="px-6 pt-5 pb-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-base">📋</div>
                <div>
                  <h2 class="text-base font-black text-gray-900">Ringkasan Pemesanan</h2>
                  <p class="text-xs text-gray-400">Periksa detail sebelum melanjutkan ke pembayaran</p>
                </div>
              </div>
              <div class="p-6">
                <BookingSummary
                  :flight="flight"
                  :passengers="passengers"
                  @proceed-to-payment="handleProceedToPayment"
                  @back="currentStep = 0"
                />
              </div>
            </div>

            <!-- Step 2: Payment Method -->
            <div v-else-if="currentStep === 2">
              <div class="px-6 pt-5 pb-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-base">💳</div>
                <div>
                  <h2 class="text-base font-black text-gray-900">Metode Pembayaran</h2>
                  <p class="text-xs text-gray-400">Pilih cara pembayaran yang paling nyaman</p>
                </div>
              </div>
              <div class="p-6">
                <PaymentMethod
                  :totalAmount="totalAmount"
                  @payment-method-selected="handlePaymentMethodSelected"
                  @back="currentStep = 1"
                />
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT: Flight Summary Card (sticky) -->
        <div class="space-y-4 lg:sticky lg:top-6">

          <!-- Flight Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 bg-blue-500">
              <p class="text-xs text-blue-100 font-medium">Detail Penerbangan</p>
            </div>

            <div class="p-5 space-y-4">
              <!-- Loading skeleton -->
              <template v-if="!flight">
                <div class="animate-pulse space-y-3">
                  <div class="h-4 w-24 bg-gray-100 rounded"/>
                  <div class="h-3 w-40 bg-gray-100 rounded"/>
                  <div class="h-3 w-32 bg-gray-100 rounded"/>
                </div>
              </template>

              <template v-else>
                <!-- Airline -->
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-black text-gray-900">{{ flight.flight_number }}</p>
                    <p class="text-xs text-gray-400">{{ flight.airline?.name ?? 'Maskapai' }}</p>
                  </div>
                </div>

                <!-- Route -->
                <div class="flex items-center gap-2">
                  <div class="text-center flex-1">
                    <p class="text-xl font-black text-gray-900">{{ formatTime(flight.departure_time) }}</p>
                    <p class="text-sm font-bold text-gray-700">{{ flightOrigin }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ flightOriginCity }}</p>
                  </div>

                  <div class="flex flex-col items-center gap-1 px-1">
                    <p class="text-xs text-gray-400 font-medium">
                      {{ formatDuration(flight.departure_time, flight.arrival_time) }}
                    </p>
                    <div class="relative flex items-center w-16">
                      <div class="flex-1 h-px bg-gray-200"/>
                      <svg class="w-3 h-3 text-blue-400 mx-0.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                      </svg>
                      <div class="flex-1 h-px bg-gray-200"/>
                    </div>
                    <span class="text-xs text-green-600 font-semibold">Langsung</span>
                  </div>

                  <div class="text-center flex-1">
                    <p class="text-xl font-black text-gray-900">{{ formatTime(flight.arrival_time) }}</p>
                    <p class="text-sm font-bold text-gray-700">{{ flightDest }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ flightDestCity }}</p>
                  </div>
                </div>

                <!-- Date -->
                <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                  <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                  </svg>
                  <p class="text-xs text-gray-600 font-medium">{{ formatDate(flight.departure_time) }}</p>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-100"/>

                <!-- Price breakdown -->
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">Harga dasar/orang</p>
                    <p class="text-xs font-bold text-gray-700">{{ formatRupiah(flight.base_price ?? 0) }}</p>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">Penumpang</p>
                    <p class="text-xs font-bold text-gray-700">× {{ passengers.length || selectedSeats.length || 1 }}</p>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">Pajak & biaya</p>
                    <p class="text-xs font-bold text-gray-700">~10%</p>
                  </div>
                  <div class="border-t border-gray-100 pt-2 flex items-center justify-between">
                    <p class="text-sm font-black text-gray-900">Total</p>
                    <p class="text-base font-black text-blue-500">{{ formatRupiah(totalAmount) }}</p>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Info card -->
          <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 space-y-2">
            <p class="text-xs font-bold text-blue-700 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
              </svg>
              Informasi Penting
            </p>
            <ul class="text-xs text-blue-600 space-y-1">
              <li class="flex items-start gap-1.5">
                <span class="mt-0.5 w-1 h-1 bg-blue-400 rounded-full shrink-0"/>
                Pastikan nama sesuai KTP/Paspor
              </li>
              <li class="flex items-start gap-1.5">
                <span class="mt-0.5 w-1 h-1 bg-blue-400 rounded-full shrink-0"/>
                Tiket tidak dapat di-refund
              </li>
              <li class="flex items-start gap-1.5">
                <span class="mt-0.5 w-1 h-1 bg-blue-400 rounded-full shrink-0"/>
                Check-in online tersedia 24 jam sebelum terbang
              </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>
