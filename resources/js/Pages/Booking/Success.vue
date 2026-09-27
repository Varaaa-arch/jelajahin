<template>
  <Head title="Pembayaran Berhasil — Jelajahin" />

  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Navbar />

    <main class="flex-1 py-8 pb-12">
      <div class="max-w-2xl mx-auto px-4 sm:px-6">

        <!-- Hero sukses -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-teal rounded-full mb-5 shadow-lg shadow-teal/30 animate-pop-in relative">
            <Check class="w-10 h-10 text-white" :stroke-width="3" />
            <span class="absolute inset-0 rounded-full bg-teal/30 animate-ping-once pointer-events-none" />
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h1>
          <p class="text-gray-500">
            Tiket kamu sudah dikonfirmasi dan e-tiket telah dikirim ke email.
          </p>

          <!-- Chip PNR + salin -->
          <div class="mt-5 inline-flex items-center gap-3 bg-white border border-gray-200 rounded-2xl pl-5 pr-3 py-3 shadow-sm">
            <div class="text-left">
              <p class="text-[11px] uppercase tracking-widest text-gray-400 font-semibold">Kode Pemesanan</p>
              <p class="font-mono font-bold text-xl text-gray-900 tracking-[0.2em]">{{ pnrCode }}</p>
            </div>
            <button
              type="button"
              @click="copyPnr"
              class="flex items-center gap-1.5 text-sm font-semibold px-3 py-2 rounded-xl transition-colors"
              :class="pnrCopied ? 'text-teal bg-teal-50' : 'text-gray-500 hover:text-teal hover:bg-teal-50'"
            >
              <Check v-if="pnrCopied" class="w-4 h-4" />
              <Copy v-else class="w-4 h-4" />
              {{ pnrCopied ? 'Tersalin!' : 'Salin' }}
            </button>
          </div>
        </div>

        <!-- Boarding-pass card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/60 overflow-hidden mb-6 border border-gray-100">
          <!-- Header navy -->
          <div class="bg-navy px-6 py-5 text-white">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-teal flex items-center justify-center shrink-0">
                  <Plane class="w-5 h-5 text-white" />
                </div>
                <div class="min-w-0">
                  <p class="font-bold truncate">{{ airlineName }}</p>
                  <p class="text-xs text-white/60">{{ flightNumber }}</p>
                </div>
              </div>
              <span class="inline-flex items-center gap-1.5 bg-teal text-white text-xs font-bold px-3 py-1.5 rounded-full shrink-0">
                <BadgeCheck class="w-4 h-4" /> Confirmed
              </span>
            </div>
          </div>

          <!-- Rute -->
          <div class="px-6 pt-6 pb-2">
            <div class="flex items-center justify-between gap-2">
              <div class="text-center w-24">
                <p class="text-3xl font-black text-gray-900">{{ originCode }}</p>
                <p class="text-xs text-gray-500 mt-1 truncate">{{ originCity }}</p>
              </div>
              <div class="flex-1 flex flex-col items-center px-1">
                <div class="w-full flex items-center gap-2">
                  <div class="flex-1 h-px bg-gray-200" />
                  <span class="w-9 h-9 rounded-full bg-teal-50 flex items-center justify-center shrink-0">
                    <Plane class="w-4 h-4 text-teal" />
                  </span>
                  <div class="flex-1 h-px bg-gray-200" />
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5">Penerbangan langsung</p>
              </div>
              <div class="text-center w-24">
                <p class="text-3xl font-black text-gray-900">{{ destinationCode }}</p>
                <p class="text-xs text-gray-500 mt-1 truncate">{{ destinationCity }}</p>
              </div>
            </div>
          </div>

          <!-- Perforasi -->
          <div class="relative mx-6 my-2">
            <div class="border-t-2 border-dashed border-gray-200" />
            <div class="absolute -left-9 -top-3 w-6 h-6 bg-gray-50 rounded-full border-r border-gray-100" />
            <div class="absolute -right-9 -top-3 w-6 h-6 bg-gray-50 rounded-full border-l border-gray-100" />
          </div>

          <!-- Detail -->
          <div class="px-6 py-5">
            <div class="grid grid-cols-2 gap-x-4 gap-y-5">
              <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                  <Users class="w-4 h-4 text-teal" />
                </span>
                <div>
                  <p class="text-[11px] uppercase tracking-widest text-gray-400 font-semibold">Penumpang</p>
                  <p class="font-bold text-gray-900 mt-0.5">{{ passengerCount }} Orang</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                  <component :is="methodIcon" class="w-4 h-4 text-teal" />
                </span>
                <div>
                  <p class="text-[11px] uppercase tracking-widest text-gray-400 font-semibold">Metode Bayar</p>
                  <p class="font-bold text-gray-900 mt-0.5 capitalize">{{ paymentMethodLabel }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                  <CalendarCheck class="w-4 h-4 text-teal" />
                </span>
                <div>
                  <p class="text-[11px] uppercase tracking-widest text-gray-400 font-semibold">Tanggal Bayar</p>
                  <p class="font-bold text-gray-900 mt-0.5">{{ paymentDate }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                  <Receipt class="w-4 h-4 text-teal" />
                </span>
                <div>
                  <p class="text-[11px] uppercase tracking-widest text-gray-400 font-semibold">Total Bayar</p>
                  <p class="font-bold text-teal text-lg leading-tight mt-0.5">Rp {{ formattedTotal }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Barcode -->
          <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            <div class="flex gap-[3px] justify-center" aria-hidden="true">
              <div
                v-for="(bar, i) in barcodeBars"
                :key="i"
                class="bg-gray-800 rounded-[1px]"
                :style="{ width: bar.w + 'px', height: bar.h + 'px' }"
              />
            </div>
            <p class="text-center text-[11px] font-mono text-gray-400 mt-2 tracking-[0.3em]">{{ pnrCode }}</p>
          </div>
        </div>

        <!-- Langkah selanjutnya -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
          <h2 class="font-bold text-gray-900 mb-4">Langkah selanjutnya</h2>
          <ol class="space-y-4">
            <li v-for="(step, idx) in nextSteps" :key="step.title" class="flex items-start gap-3">
              <div class="flex flex-col items-center shrink-0">
                <span class="w-9 h-9 rounded-xl bg-navy flex items-center justify-center">
                  <component :is="step.icon" class="w-4 h-4 text-white" />
                </span>
                <span v-if="idx < nextSteps.length - 1" class="w-px h-5 bg-gray-200 mt-1" />
              </div>
              <div class="pt-1">
                <p class="font-semibold text-gray-900 text-sm">{{ step.title }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ step.desc }}</p>
              </div>
            </li>
          </ol>
        </div>

        <!-- Aksi -->
        <div class="space-y-3">
          <button
            type="button"
            @click="downloadDoc('eticket')"
            :disabled="downloading !== ''"
            class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-600 disabled:opacity-70 text-white font-bold py-3.5 px-6 rounded-2xl transition-all shadow-lg shadow-teal/25 active:scale-[0.99]"
          >
            <LoaderCircle v-if="downloading === 'eticket'" class="w-5 h-5 animate-spin" />
            <Download v-else class="w-5 h-5" />
            {{ downloading === 'eticket' ? 'Menyiapkan PDF...' : 'Unduh E-Tiket (PDF)' }}
          </button>

          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              @click="goToMyBookings"
              class="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-2xl border border-gray-200 transition-colors"
            >
              <ClipboardList class="w-5 h-5 text-gray-500" />
              Pesanan Saya
            </button>
            <button
              type="button"
              @click="goToHome"
              class="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-2xl border border-gray-200 transition-colors"
            >
              <House class="w-5 h-5 text-gray-500" />
              Halaman Utama
            </button>
          </div>

          <button
            type="button"
            @click="downloadDoc('invoice')"
            :disabled="downloading !== ''"
            class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-teal hover:text-teal-600 disabled:opacity-60 transition-colors py-1"
          >
            <LoaderCircle v-if="downloading === 'invoice'" class="w-4 h-4 animate-spin" />
            <Receipt v-else class="w-4 h-4" />
            {{ downloading === 'invoice' ? 'Menyiapkan invoice...' : 'Unduh invoice pembayaran' }}
          </button>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import {
  Plane,
  Check,
  Copy,
  Download,
  LoaderCircle,
  BadgeCheck,
  Users,
  CalendarCheck,
  Receipt,
  CreditCard,
  Landmark,
  Wallet,
  MailCheck,
  QrCode,
  Clock,
  ClipboardList,
  House,
} from 'lucide-vue-next'

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
const pnrCode = ref(props.pnrCode || '')
const totalAmount = ref(props.totalAmount || 0)
const flightNumber = ref(props.flightNumber || '-')
const passengerCount = ref(props.passengerCount || 1)
const paymentMethod = ref(props.paymentMethod || 'credit_card')
const originCode = ref(props.originCode || 'CGK')
const originCity = ref(props.originCity || 'Jakarta')
const destinationCode = ref(props.destinationCode || 'DPS')
const destinationCity = ref(props.destinationCity || 'Denpasar')
const pnrCopied = ref(false)
const downloading = ref<'' | 'eticket' | 'invoice'>('')

// ─── Computed ────────────────────────────────────────────────────────────────
const formattedTotal = computed(() =>
  new Intl.NumberFormat('id-ID').format(Math.round(totalAmount.value))
)

const paymentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
})

const airlineName = computed(() => 'Jelajahin Airlines')

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

const methodIcon = computed(() => {
  if (paymentMethod.value === 'bank_transfer' || paymentMethod.value === 'bank') return Landmark
  if (paymentMethod.value === 'ewallet') return Wallet
  return CreditCard
})

const nextSteps = [
  { icon: MailCheck, title: 'Cek email kamu', desc: 'E-tiket dan invoice telah dikirim ke email terdaftar.' },
  { icon: QrCode, title: 'Siapkan kode PNR', desc: 'Tunjukkan kode pemesanan beserta identitas saat check-in.' },
  { icon: Clock, title: 'Datang lebih awal', desc: 'Tiba di bandara minimal 2 jam sebelum keberangkatan.' },
]

// Barcode deterministik dari PNR (stabil antar render)
const barcodeBars = computed(() => {
  const src = pnrCode.value || 'JELAJAHIN'
  let hash = 0
  for (const ch of src) hash = (hash * 31 + ch.charCodeAt(0)) >>> 0
  const bars: Array<{ w: number; h: number }> = []
  let seed = hash || 7
  for (let i = 0; i < 42; i++) {
    seed = (seed * 1103515245 + 12345) & 0x7fffffff
    bars.push({ w: 1 + (seed % 4), h: seed % 3 === 0 ? 22 : 30 })
  }
  return bars
})

// ─── Actions ─────────────────────────────────────────────────────────────────
const copyPnr = async () => {
  try {
    await navigator.clipboard.writeText(pnrCode.value)
    pnrCopied.value = true
    setTimeout(() => { pnrCopied.value = false }, 2000)
  } catch { /* clipboard tidak tersedia */ }
}

const downloadDoc = (doc: 'eticket' | 'invoice') => {
  if (downloading.value !== '' || !pnrCode.value) return
  downloading.value = doc
  window.open(route('booking.documents', { pnr: pnrCode.value, doc }), '_blank')
  setTimeout(() => { downloading.value = '' }, 3000)
}

const goToMyBookings = () => {
  router.visit('/dashboard')
}

const goToHome = () => {
  router.visit('/')
}

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(() => {
  const session = sessionStorage.getItem('pendingBooking')
  if (session && !props.totalAmount) {
    try {
      const data = JSON.parse(session)
      totalAmount.value = data.totalAmount || 0
    } catch { /* abaikan */ }
  }
  sessionStorage.removeItem('pendingBooking')
  sessionStorage.removeItem('selectedSeats')
})
</script>

<style scoped>
@keyframes pop-in {
  0% { transform: scale(0.4); opacity: 0; }
  60% { transform: scale(1.08); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}
.animate-pop-in {
  animation: pop-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes ping-once {
  0% { transform: scale(1); opacity: 0.6; }
  100% { transform: scale(1.8); opacity: 0; }
}
.animate-ping-once {
  animation: ping-once 0.8s ease-out 0.2s both;
}
</style>
