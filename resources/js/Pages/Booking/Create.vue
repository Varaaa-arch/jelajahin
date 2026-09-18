<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex items-center justify-between relative">
          <!-- progress line -->
          <div class="absolute left-0 right-0 top-5 h-0.5 bg-gray-200 -z-10"></div>
          <div
            class="absolute left-0 top-5 h-0.5 bg-blue-500 -z-10 transition-all duration-300"
            :style="{ width: `${(currentStep / (steps.length - 1)) * 100}%` }"
          ></div>

          <div v-for="(step, idx) in steps" :key="idx" class="flex flex-col items-center">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors',
              currentStep > idx ? 'bg-green-500 text-white' :
              currentStep === idx ? 'bg-blue-600 text-white' :
              'bg-gray-200 text-gray-600'
            ]">
              {{ currentStep > idx ? '✓' : idx + 1 }}
            </div>
            <p class="text-xs mt-2 text-gray-500">{{ step }}</p>
          </div>
        </div>
      </div>

      <!-- Step 0: Passenger Form -->
      <div v-if="currentStep === 0">
        <PassengerForm
          :seatCount="selectedSeats.length || 1"
          @passengers-submitted="handlePassengersSubmitted"
          @back="handleCancel"
        />
      </div>

      <!-- Step 1: Booking Summary -->
      <div v-else-if="currentStep === 1">
        <BookingSummary
          :flight="flight"
          :passengers="passengers"
          @proceed-to-payment="handleProceedToPayment"
          @back="currentStep = 0"
        />
      </div>

      <!-- Step 2: Payment Method -->
      <div v-else-if="currentStep === 2">
        <PaymentMethod
          :totalAmount="totalAmount"
          @payment-method-selected="handlePaymentMethodSelected"
          @back="currentStep = 1"
        />
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
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
const steps = ['Penumpang', 'Ringkasan', 'Pembayaran']
const currentStep = ref(0)

const flight = ref<any>(null)
const passengers = ref<any[]>([])
const selectedSeats = ref<LockedSeat[]>([])

// ─── useSeatLock untuk unlock saat cancel/navigasi ────────────────────────────
// Kita tidak render SeatMap di sini, tapi butuh unlock seats yang sudah dikunci
// di SeatMap (Detail.vue). Buat instance dengan flightId yang sama.
const { unlockAll } = useSeatLock(props.flightId)

// ─── Computed ────────────────────────────────────────────────────────────────
const totalAmount = computed(() => {
  if (!flight.value) return 0
  const base = (flight.value.base_price ?? 0) * (passengers.value.length || selectedSeats.value.length || 1)
  return Math.round(base * 1.1) // + 10% tax
})

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
    // Buat booking di Laravel API
    const res = await httpClient.post('/api/bookings', {
      flight_id: flight.value?.id,
      seat_ids: selectedSeats.value.map(s => s.seatId),
      passengers: passengers.value,
    })

    const bookingId = res.data.booking?.id
    const pnr = res.data.booking?.pnr_code

    sessionStorage.setItem('pendingBooking', JSON.stringify({
      bookingId,
      totalAmount: totalAmount.value,
    }))

    // Setelah booking terkonfirmasi, seats tidak perlu di-unlock manual —
    // backend akan update status. Clear local state saja.
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
        origin: flight.value?.origin?.code,
        originCity: flight.value?.origin?.city,
        destination: flight.value?.destination?.code,
        destinationCity: flight.value?.destination?.city,
      },
    })
  } catch (err: any) {
    // Dev mode / backend tidak tersedia: tetap lanjut ke payment
    const pnr = 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase()
    sessionStorage.setItem('pendingBooking', JSON.stringify({
      bookingId: null,
      totalAmount: totalAmount.value,
    }))
    selectedSeats.value = []

    router.visit('/booking/payment', {
      method: 'get',
      data: {
        pnr,
        total: totalAmount.value,
        flight: flight.value?.flight_number ?? '',
        passengers: passengers.value.length,
        method,
        origin: flight.value?.origin?.code,
        originCity: flight.value?.origin?.city,
        destination: flight.value?.destination?.code,
        destinationCity: flight.value?.destination?.city,
      },
    })
  }
}

/**
 * Cancel: unlock semua seat yang sudah dikunci, lalu kembali.
 */
const handleCancel = async () => {
  await unlockAll()
  // Bersihkan sessionStorage seats agar tidak ada sisa
  sessionStorage.removeItem('selectedSeats')
  window.history.back()
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  // Ambil seat data dari sessionStorage (di-set oleh Detail.vue)
  const seatsRaw = sessionStorage.getItem('selectedSeats')
  if (seatsRaw) {
    selectedSeats.value = JSON.parse(seatsRaw)
  }

  // Ambil flight data dari sessionStorage agar tidak fetch ulang
  const flightRaw = sessionStorage.getItem('selectedFlight')
  if (flightRaw) {
    flight.value = JSON.parse(flightRaw)
    return
  }

  // Fallback: fetch dari API
  if (props.flightId) {
    try {
      const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
      flight.value = res.data.data ?? res.data
    } catch (err) {
      console.error('[Create.vue] Failed to fetch flight:', err)
    }
  }
})

/**
 * Saat komponen di-unmount karena navigasi (bukan karena redirect ke /payment),
 * unlock semua seat yang masih aktif.
 * Jika seats sudah dikosongkan (handlePaymentMethodSelected), unlockAll no-op.
 */
onBeforeUnmount(async () => {
  if (selectedSeats.value.length > 0) {
    await unlockAll()
    sessionStorage.removeItem('selectedSeats')
  }
})
</script>
