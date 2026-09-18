<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 py-8 px-4">
    <div class="max-w-2xl mx-auto">

      <!-- Success Animation -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-green-500 rounded-full mb-6 shadow-lg animate-bounce-once">
          <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-gray-500">Tiket kamu sudah dikonfirmasi. Selamat terbang! ✈️</p>
      </div>

      <!-- Booking Card / E-Ticket Preview -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
        <!-- Header Tiket -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-widest opacity-80 mb-1">Kode Pemesanan</p>
              <p class="text-2xl font-bold font-mono tracking-widest">{{ pnrCode }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs uppercase tracking-widest opacity-80 mb-1">Status</p>
              <span class="inline-flex items-center gap-1 bg-green-400 text-white text-sm font-semibold px-3 py-1 rounded-full">
                ✓ Confirmed
              </span>
            </div>
          </div>
        </div>

        <!-- Ticket Body -->
        <div class="px-6 py-5">
          <!-- Flight Info -->
          <div class="flex items-center justify-between mb-6">
            <div class="text-center">
              <p class="text-3xl font-bold text-gray-800">{{ originCode }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ originCity }}</p>
            </div>
            <div class="flex-1 text-center px-4">
              <div class="flex items-center">
                <div class="flex-1 border-t-2 border-dashed border-gray-300"></div>
                <div class="mx-3">
                  <svg class="w-7 h-7 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                  </svg>
                </div>
                <div class="flex-1 border-t-2 border-dashed border-gray-300"></div>
              </div>
              <p class="text-xs text-gray-400 mt-1">{{ flightNumber }}</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-gray-800">{{ destinationCode }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ destinationCity }}</p>
            </div>
          </div>

          <!-- Divider with dots -->
          <div class="relative my-4">
            <div class="border-t border-dashed border-gray-200"></div>
            <div class="absolute -left-6 -top-3 w-6 h-6 bg-emerald-50 rounded-r-full"></div>
            <div class="absolute -right-6 -top-3 w-6 h-6 bg-emerald-50 rounded-l-full"></div>
          </div>

          <!-- Detail Grid -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Penumpang</p>
              <p class="font-semibold text-gray-800">{{ passengerCount }} Orang</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Metode Bayar</p>
              <p class="font-semibold text-gray-800 capitalize">{{ paymentMethodLabel }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Tanggal Bayar</p>
              <p class="font-semibold text-gray-800">{{ paymentDate }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Bayar</p>
              <p class="font-semibold text-blue-600 text-lg">Rp {{ formattedTotal }}</p>
            </div>
          </div>
        </div>

        <!-- Barcode Footer -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-100">
          <div class="flex gap-1">
            <div v-for="i in 28" :key="i"
              class="w-1 rounded-sm bg-gray-800"
              :style="{ height: (Math.random() > 0.5 ? 28 : 20) + 'px' }"
            ></div>
          </div>
          <p class="text-xs font-mono text-gray-400 ml-4">{{ pnrCode }}-{{ transactionId }}</p>
        </div>
      </div>

      <!-- Info Box -->
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex items-start gap-3">
        <span class="text-blue-500 text-xl flex-shrink-0">ℹ️</span>
        <div class="text-sm text-blue-700">
          <p class="font-semibold mb-1">Apa selanjutnya?</p>
          <ul class="space-y-1 list-disc list-inside">
            <li>E-Tiket akan dikirim ke email kamu</li>
            <li>Tunjukkan kode PNR di check-in counter</li>
            <li>Tiba di bandara minimal 2 jam sebelum keberangkatan</li>
          </ul>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-3">
        <button
          @click="downloadTicket"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-md hover:shadow-lg"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Download E-Tiket
        </button>

        <div class="grid grid-cols-2 gap-3">
          <button
            @click="goToMyBookings"
            class="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-xl border border-gray-200 transition-colors"
          >
            📋 Pesanan Saya
          </button>
          <button
            @click="goToHome"
            class="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-xl border border-gray-200 transition-colors"
          >
            🏠 Halaman Utama
          </button>
        </div>
      </div>

      <!-- Share -->
      <div class="text-center mt-6">
        <p class="text-sm text-gray-400">Bagikan perjalananmu</p>
        <div class="flex justify-center gap-4 mt-2">
          <button @click="share('whatsapp')" class="text-green-500 hover:text-green-600 text-2xl transition-transform hover:scale-110">💬</button>
          <button @click="share('instagram')" class="text-pink-500 hover:text-pink-600 text-2xl transition-transform hover:scale-110">📸</button>
          <button @click="share('twitter')" class="text-blue-400 hover:text-blue-500 text-2xl transition-transform hover:scale-110">🐦</button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  pnrCode?: string
  totalAmount?: number
  flightNumber?: string
  passengerCount?: number
  paymentMethod?: string
  originCode?: string
  originCity?: string
  destinationCode?: string
  destinationCity?: string
}>()

// ─── State ───────────────────────────────────────────────────────────────────
const pnrCode = ref(props.pnrCode || 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase())
const totalAmount = ref(props.totalAmount || 0)
const flightNumber = ref(props.flightNumber || '-')
const passengerCount = ref(props.passengerCount || 1)
const paymentMethod = ref(props.paymentMethod || 'credit_card')
const originCode = ref(props.originCode || 'CGK')
const originCity = ref(props.originCity || 'Jakarta')
const destinationCode = ref(props.destinationCode || 'DPS')
const destinationCity = ref(props.destinationCity || 'Bali')
const transactionId = ref(Math.random().toString(36).substring(2, 10).toUpperCase())

// ─── Computed ────────────────────────────────────────────────────────────────
const formattedTotal = computed(() =>
  new Intl.NumberFormat('id-ID').format(Math.round(totalAmount.value))
)

const paymentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
})

const paymentMethodLabel = computed(() => {
  const labels: Record<string, string> = {
    credit_card: 'Kartu Kredit',
    debit_card: 'Kartu Debit',
    bank_transfer: 'Transfer Bank',
    ewallet: 'E-Wallet',
    card: 'Kartu Kredit/Debit',
    bank: 'Transfer Bank',
  }
  return labels[paymentMethod.value] || paymentMethod.value
})

// ─── Actions ─────────────────────────────────────────────────────────────────
const downloadTicket = () => {
  // Trigger download e-ticket dari backend
  const url = `/booking/eticket/${pnrCode.value}`
  window.open(url, '_blank')
}

const goToMyBookings = () => {
  router.visit('/dashboard')
}

const goToHome = () => {
  router.visit('/')
}

const share = (platform: string) => {
  const text = `✈️ Aku baru beli tiket ke ${destinationCity.value} dengan Jelajahin! Kode booking: ${pnrCode.value}`
  const encodedText = encodeURIComponent(text)
  const urls: Record<string, string> = {
    whatsapp: `https://wa.me/?text=${encodedText}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodedText}`,
    instagram: `https://instagram.com/`,
  }
  window.open(urls[platform] || '#', '_blank')
}

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(() => {
  // Jika ada data dari sessionStorage (fallback)
  const session = sessionStorage.getItem('pendingBooking')
  if (session && !props.totalAmount) {
    const data = JSON.parse(session)
    totalAmount.value = data.totalAmount || 0
  }

  // Bersihkan session setelah berhasil
  sessionStorage.removeItem('pendingBooking')
  sessionStorage.removeItem('selectedSeats')
})
</script>

<style scoped>
@keyframes bounce-once {
  0%, 100% { transform: translateY(0); }
  30% { transform: translateY(-20px); }
  60% { transform: translateY(-10px); }
}
.animate-bounce-once {
  animation: bounce-once 0.8s ease-out forwards;
}
</style>
