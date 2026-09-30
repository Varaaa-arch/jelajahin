<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import SeatMap from '@/Pages/Flight/SeatMap.vue'
import AddonSelector from '@/Components/Booking/AddonSelector.vue'
import { httpClient } from '@/utils/http'
import type { LockedSeat } from '@/composables/useSeatLock'

const BOOKING_TIMEOUT_MS = 10000

const FORM_STORAGE_KEY = 'bookingPassengerForm'
const ADDON_STORAGE_KEY = 'bookingAddons'

const props = defineProps<{
  flightId: string
  passengerCount?: number
  adultCount?: number
  childCount?: number
  infantCount?: number
}>()

const steps = [
  { number: 1, label: 'Penerbangan' },
  { number: 2, label: 'Penumpang' },
  { number: 3, label: 'Kursi' },
  { number: 4, label: 'Layanan' },
  { number: 5, label: 'Pembayaran' },
]
const currentStep = ref(2)

const flight = ref<any>(null)
const selectedSeats = ref<Array<LockedSeat & { currentPrice?: number }>>([])
// SeatMap expose `skipAutoUnlock` sebagai ref -> di template ref terbaca unwrapped (boolean).
// Dukung kedua bentuk agar tidak throw "can't assign to property value on false".
const seatMapRef = ref<{ skipAutoUnlock?: boolean | { value: boolean } } | null>(null)

function setSkipAutoUnlock(v: boolean) {
  const exposed: any = (seatMapRef.value as any)?.skipAutoUnlock
  if (exposed !== undefined && exposed !== null && typeof exposed === 'object' && 'value' in exposed) {
    exposed.value = v
  } else if (seatMapRef.value) {
    ;(seatMapRef.value as any).skipAutoUnlock = v
  }
}
const activePassengerIndex = ref(0)
const submitting = ref(false)
const seatError = ref('')
const bookingError = ref('')

function getAuthUserId(): string {
  try {
    const authUser = (usePage().props as any)?.auth?.user
    if (authUser?.id) return String(authUser.id)
  } catch { /* ignore */ }
  return localStorage.getItem('user_id') || ''
}

const adultCount = computed(() => props.adultCount ?? props.passengerCount ?? 1)
const childCount = computed(() => props.childCount ?? 0)
const totalPax = computed(() => {
  const n = adultCount.value + childCount.value
  return n > 0 ? n : (props.passengerCount ?? 1)
})

type PassengerSlot = { key: string; label: string; subtitle?: string }
const passengerSlots = computed<PassengerSlot[]>(() => {
  const slots: PassengerSlot[] = []
  for (let i = 0; i < adultCount.value; i++) {
    slots.push({ key: `adult-${i}`, label: `Dewasa ${i + 1}`, subtitle: 'Dewasa' })
  }
  for (let i = 0; i < childCount.value; i++) {
    slots.push({ key: `child-${i}`, label: `Anak ${i + 1}`, subtitle: 'Anak' })
  }
  if (!slots.length) slots.push({ key: 'adult-0', label: 'Dewasa 1', subtitle: 'Dewasa' })
  return slots
})

const passengerSeats = ref<Array<(LockedSeat & { currentPrice?: number }) | null>>([])

function syncPassengerSeats(seats: Array<LockedSeat & { currentPrice?: number }>) {
  const remaining = [...seats]
  const next = passengerSlots.value.map((_, idx) => {
    const prev = passengerSeats.value[idx]
    if (prev && remaining.some(s => s.seatId === prev.seatId)) {
      const found = remaining.find(s => s.seatId === prev.seatId)!
      remaining.splice(remaining.indexOf(found), 1)
      return found
    }
    return null
  })
  for (const seat of remaining) {
    const empty = next.findIndex(s => s === null)
    if (empty !== -1) next[empty] = seat
  }
  passengerSeats.value = next
  const firstEmpty = next.findIndex(s => s === null)
  if (firstEmpty !== -1) activePassengerIndex.value = firstEmpty
}

watch(passengerSlots, (slots) => {
  if (passengerSeats.value.length !== slots.length) {
    passengerSeats.value = slots.map((_, i) => passengerSeats.value[i] ?? null)
  }
}, { immediate: true })

const form = ref({
  title: 'Tuan (Mr)',
  full_name: '',
  date_of_birth: '',
  nationality: 'Indonesia',
  identity_number: '',
  email: '',
  phone: '',
})

const errors = ref<Record<string, string>>({})

// Addons state - default sesuai gambar: 5kg + Premium Shield
const selectedBaggage = ref<string>('5kg')
const selectedInsurance = ref<string>('premium')
const selectedMeals = ref<Record<number, string>>({})

const baggagePrice = computed(() => {
  const map: Record<string, number> = { none: 0, '5kg': 150000, '10kg': 280000 }
  return map[selectedBaggage.value] ?? 0
})
const insuranceUnitPrice = computed(() => {
  const map: Record<string, number> = { none: 0, basic: 45000, premium: 85000 }
  return map[selectedInsurance.value] ?? 0
})
const insurancePrice = computed(() => insuranceUnitPrice.value * totalPax.value)
const addonsTotal = computed(() => baggagePrice.value + insurancePrice.value)

const basePrice = computed(() => (flight.value?.base_price ?? 0) * totalPax.value)
const tax = computed(() => (flight.value?.tax_surcharge ?? 0) * totalPax.value)
const seatSelectionFee = computed(() => {
  const base = flight.value?.base_price ?? 0
  return selectedSeats.value.reduce((sum, s) => {
    const p = s.currentPrice ?? 0
    if (p <= 0) return sum
    if (p < base) return sum + p
    return sum + Math.max(0, p - base)
  }, 0)
})
const total = computed(() => basePrice.value + tax.value + seatSelectionFee.value + addonsTotal.value)

const originCode = computed(() => flight.value?.origin?.code ?? flight.value?.route?.originAirport?.code ?? '—')
const destCode = computed(() => flight.value?.destination?.code ?? flight.value?.route?.destinationAirport?.code ?? '—')
const originCity = computed(() => flight.value?.origin?.city ?? flight.value?.route?.originAirport?.city ?? '—')
const destCity = computed(() => flight.value?.destination?.city ?? flight.value?.route?.destinationAirport?.city ?? '—')
const airlineName = computed(() => flight.value?.airline?.name ?? flight.value?.route?.airline?.name ?? '—')

const replaceSeatId = computed(() => passengerSeats.value[activePassengerIndex.value]?.seatId ?? null)

function formatTime(t?: string) {
  if (!t) return '—'
  return t.slice(0, 5)
}
function formatDuration(mins?: string | number) {
  const m = parseInt(String(mins ?? 0))
  if (!m) return '—'
  return `${Math.floor(m / 60)}j ${m % 60}m`
}
function formatDate(d?: string) {
  if (!d) return '—'
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  }).format(new Date(d))
}
function formatCurrency(n: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  }).format(n)
}

function persistForm() {
  sessionStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(form.value))
}
function persistAddons() {
  sessionStorage.setItem(ADDON_STORAGE_KEY, JSON.stringify({
    baggage: selectedBaggage.value,
    insurance: selectedInsurance.value,
    meals: selectedMeals.value,
  }))
}

function validate() {
  errors.value = {}
  if (!form.value.full_name.trim()) errors.value.full_name = 'Nama wajib diisi'
  if (!form.value.date_of_birth) errors.value.date_of_birth = 'Tanggal lahir wajib diisi'
  if (!form.value.identity_number.trim()) errors.value.identity_number = 'Nomor identitas wajib diisi'
  if (!form.value.email.trim()) errors.value.email = 'Email wajib diisi'
  if (!form.value.phone.trim()) errors.value.phone = 'Nomor telepon wajib diisi'
  return Object.keys(errors.value).length === 0
}

function buildPassengers() {
  const [firstName, ...rest] = form.value.full_name.trim().split(' ')
  const lastName = rest.join(' ') || firstName
  return Array.from({ length: totalPax.value }, (_, i) => ({
    title: form.value.title === 'Tuan (Mr)' ? 'Mr' : form.value.title === 'Nyonya (Mrs)' ? 'Mrs' : 'Ms',
    first_name: i === 0 ? firstName : `Penumpang ${i + 1}`,
    last_name: i === 0 ? lastName : `Penumpang ${i + 1}`,
    date_of_birth: form.value.date_of_birth,
    gender: form.value.title === 'Tuan (Mr)' ? 'M' : 'F',
    identity_type: 'id_card',
    identity_number: form.value.identity_number,
    nationality: form.value.nationality,
  }))
}

function buildAddonsPayload() {
  const mealsArr = passengerSlots.value.map((_, idx) => selectedMeals.value[idx] ?? 'Halal Meal (Included)')
  return {
    baggage: selectedBaggage.value,
    insurance: selectedInsurance.value,
    meals: mealsArr,
  }
}

function lanjutKeKursi() {
  if (!validate()) return
  persistForm()
  currentStep.value = 3
}

function lanjutKeLayanan() {
  if (selectedSeats.value.length !== totalPax.value) {
    seatError.value = `Pilih ${totalPax.value} kursi (satu per penumpang)`
    return
  }
  seatError.value = ''
  persistAddons()
  currentStep.value = 4
}

function onSeatsSelected(seats: Array<LockedSeat & { currentPrice?: number }>) {
  selectedSeats.value = seats
  seatError.value = ''
  syncPassengerSeats(seats)
}

function selectPassenger(index: number) {
  activePassengerIndex.value = index
}

function onUpdateBaggage(v: string) {
  selectedBaggage.value = v
  persistAddons()
}
function onUpdateInsurance(v: string) {
  selectedInsurance.value = v
  persistAddons()
}
function onUpdateMeal(payload: { idx: number; value: string }) {
  selectedMeals.value = { ...selectedMeals.value, [payload.idx]: payload.value }
  persistAddons()
}

async function lanjutKePembayaran() {
  // Cegah double-click: kalau masih submitting, abaikan
  if (submitting.value) return
  if (selectedSeats.value.length !== totalPax.value) {
    seatError.value = `Pilih ${totalPax.value} kursi (satu per penumpang)`
    currentStep.value = 3
    return
  }

  submitting.value = true
  bookingError.value = ''
  seatError.value = ''
  persistForm()
  persistAddons()
  setSkipAutoUnlock(true)

  const payload = {
    flight_id: flight.value?.id,
    seats: selectedSeats.value.map(s => s.seatNumber),
    seat_ids: selectedSeats.value.map(s => s.seatId),
    passengers: buildPassengers(),
    addons: buildAddonsPayload(),
    user_id: getAuthUserId(),
  }

  try {
    // Timeout khusus 10 detik — jangan kunci UI sampai 30 detik
    const res = await httpClient.post('/api/bookings', payload, { timeout: BOOKING_TIMEOUT_MS })
    const bookingId = res.data.booking?.id ?? ''
    const pnr = res.data.booking?.pnr_code ?? ''

    const params = new URLSearchParams({
      bookingId: String(bookingId),
      pnr: String(pnr),
      total: String(total.value),
      flight: String(flight.value?.flight_number ?? ''),
      passengers: String(totalPax.value),
      method: 'credit_card',
      origin: String(originCode.value),
      originCity: String(originCity.value),
      destination: String(destCode.value),
      destinationCity: String(destCity.value),
    })
    // Navigasi Inertia (tanpa reload penuh) biar terasa cepat
    router.visit(`/booking/payment?${params.toString()}`)
  } catch (e: any) {
    // Gagal → kembalikan flag supaya kursi bisa di-unlock lagi saat user navigasi
    setSkipAutoUnlock(false)
    const status = e?.response?.status
    const serverMsg = e?.response?.data?.message || ''
    const errCode = e?.response?.data?.error || ''
    const isTimeout = e?.code === 'ECONNABORTED' || /timeout/i.test(e?.message ?? '')

    if (status === 401) {
      bookingError.value = 'Sesi habis. Silakan masuk lagi lewat menu — halaman & kursi pilihanmu tetap di sini.'
    } else if (status === 422 && (errCode === 'seat_lock_invalid' || /lock|expired/i.test(serverMsg))) {
      seatError.value = 'Kunci kursi kedaluwarsa / dipakai orang lain. Pilih kursi lagi.'
      bookingError.value = seatError.value
      currentStep.value = 3
    } else if (isTimeout || !status) {
      bookingError.value = 'Server lama merespons (>10 detik). Cek koneksi / coba lagi.'
    } else {
      bookingError.value = serverMsg || 'Gagal membuat booking. Coba lagi.'
    }
    console.error('[lanjutKePembayaran] gagal', { status, serverMsg, err: e?.message })
  } finally {
    submitting.value = false
  }
}

function kembali() {
  if (currentStep.value === 4) {
    currentStep.value = 3
    return
  }
  if (currentStep.value === 3) {
    currentStep.value = 2
    return
  }
  if (currentStep.value === 5) {
    currentStep.value = 4
    return
  }
  persistForm()
  router.visit(route('booking.review', {
    flightId: props.flightId,
    passengerCount: totalPax.value,
    adultCount: adultCount.value,
    childCount: childCount.value,
    infantCount: props.infantCount ?? 0,
  }))
}

onMounted(async () => {
  const saved = sessionStorage.getItem(FORM_STORAGE_KEY)
  if (saved) {
    try { form.value = { ...form.value, ...JSON.parse(saved) } } catch { /* ignore */ }
  }
  const savedAddons = sessionStorage.getItem(ADDON_STORAGE_KEY)
  if (savedAddons) {
    try {
      const a = JSON.parse(savedAddons)
      if (a.baggage) selectedBaggage.value = a.baggage
      if (a.insurance) selectedInsurance.value = a.insurance
      if (a.meals) selectedMeals.value = a.meals
    } catch { /* ignore */ }
  }

  if (!props.flightId) return
  try {
    const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
    flight.value = res.data?.data ?? res.data
  } catch { /* flight sidebar tetap placeholder */ }
})
</script>

<template>
  <Head :title="currentStep === 3 ? 'Pilih Kursi — Jelajahin' : currentStep === 4 ? 'Layanan Tambahan — Jelajahin' : 'Data Penumpang — Jelajahin'" />

  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Navbar />

    <div class="bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-6xl mx-auto px-6 pt-20 pb-4">
        <div class="flex items-center justify-center gap-0">
          <template v-for="(step, idx) in steps" :key="step.number">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all shrink-0"
                :class="step.number === currentStep
                  ? 'bg-teal border-teal text-white'
                  : step.number < currentStep
                    ? 'bg-teal border-teal text-white'
                    : 'bg-white border-gray-200 text-gray-400'"
              >
                <svg v-if="step.number < currentStep" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span v-else>{{ step.number }}</span>
              </div>
              <span
                class="text-sm font-semibold hidden sm:block"
                :class="step.number === currentStep ? 'text-navy font-bold' : step.number < currentStep ? 'text-teal' : 'text-gray-400'"
              >{{ step.label }}</span>
            </div>
            <div
              v-if="idx < steps.length - 1"
              class="w-12 sm:w-20 h-px mx-2 transition-colors"
              :class="step.number < currentStep ? 'bg-teal' : 'bg-gray-200'"
            />
          </template>
        </div>
      </div>
    </div>

    <main class="flex-1 py-6 pb-10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col lg:flex-row gap-6 items-start">

          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">
              {{ currentStep === 3 ? 'Pilih Kursi' : currentStep === 4 ? 'Layanan Tambahan' : 'Detail Penumpang' }}
            </h1>
            <p v-if="currentStep === 4" class="text-sm text-gray-500 mb-5">Tingkatkan kenyamanan penerbangan Anda dengan pilihan layanan tambahan kami.</p>
            <p v-else class="mb-5"></p>

            <div v-show="currentStep === 2" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
              <div class="flex items-center gap-2 pb-4 border-b border-gray-100">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <h2 class="font-bold text-gray-900">Data Penumpang 1 (Dewasa)</h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">
                <div class="flex flex-col gap-1.5">
                  <label class="text-sm font-medium text-gray-700">Titel</label>
                  <select
                    v-model="form.title"
                    class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white"
                  >
                    <option>Tuan (Mr)</option>
                    <option>Nyonya (Mrs)</option>
                    <option>Nona (Ms)</option>
                  </select>
                </div>
                <div class="flex flex-col gap-1.5">
                  <label class="text-sm font-medium text-gray-700">Nama Lengkap <span class="text-sm text-gray-400">(Sesuai KTP/Paspor)</span></label>
                  <input
                    v-model="form.full_name"
                    type="text"
                    placeholder="Nama lengkap"
                    class="border rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal/10 transition-colors"
                    :class="errors.full_name ? 'border-red-400' : 'border-gray-200 focus:border-teal'"
                  />
                  <p v-if="errors.full_name" class="text-xs text-red-500">{{ errors.full_name }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                  <label class="text-sm font-medium text-gray-700">Tanggal Lahir</label>
                  <input
                    v-model="form.date_of_birth"
                    type="date"
                    class="border rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal/10 transition-colors"
                    :class="errors.date_of_birth ? 'border-red-400' : 'border-gray-200 focus:border-teal'"
                  />
                  <p v-if="errors.date_of_birth" class="text-xs text-red-500">{{ errors.date_of_birth }}</p>
                </div>
                <div class="flex flex-col gap-1.5">
                  <label class="text-sm font-medium text-gray-700">Kewarganegaraan</label>
                  <select
                    v-model="form.nationality"
                    class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 bg-white"
                  >
                    <option>Indonesia</option>
                    <option>Malaysia</option>
                    <option>Singapore</option>
                    <option>Australia</option>
                    <option>United States</option>
                    <option>Other</option>
                  </select>
                </div>
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700">Nomor KTP / Paspor</label>
                <input
                  v-model="form.identity_number"
                  type="text"
                  placeholder="Nomor Identitas"
                  class="border rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal/10 transition-colors max-w-sm"
                  :class="errors.identity_number ? 'border-red-400' : 'border-gray-200 focus:border-teal'"
                />
                <p v-if="errors.identity_number" class="text-xs text-red-500">{{ errors.identity_number }}</p>
              </div>

              <div class="border-t border-gray-100 pt-4">
                <h3 class="font-bold text-gray-900 mb-4">Informasi Kontak</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input
                      v-model="form.email"
                      type="email"
                      placeholder="email@example.com"
                      class="border rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal/10 transition-colors"
                      :class="errors.email ? 'border-red-400' : 'border-gray-200 focus:border-teal'"
                    />
                    <p v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</p>
                  </div>
                  <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700">Nomor Telepon</label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      placeholder="+62 812 3456 7890"
                      class="border rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal/10 transition-colors"
                      :class="errors.phone ? 'border-red-400' : 'border-gray-200 focus:border-teal'"
                    />
                    <p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-show="currentStep === 3">
              <p v-if="seatError" class="mb-3 text-sm text-red-600">{{ seatError }}</p>
              <SeatMap
                v-if="flightId"
                ref="seatMapRef"
                :flight-id="flightId"
                :max-seats="totalPax"
                :replace-seat-id="replaceSeatId"
                @seats-selected="onSeatsSelected"
              />
            </div>

            <div v-show="currentStep === 4">
              <AddonSelector
                :model-baggage="selectedBaggage"
                :model-insurance="selectedInsurance"
                :model-meals="selectedMeals"
                :passenger-slots="passengerSlots"
                @update:baggage="onUpdateBaggage"
                @update:insurance="onUpdateInsurance"
                @update:meal="onUpdateMeal"
              />
            </div>
          </div>

          <div class="w-full lg:w-80 shrink-0 lg:sticky lg:top-24">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">Ringkasan Pesanan</h2>
              </div>

              <div class="px-5 py-4 space-y-3">
                <div v-if="currentStep >= 3" class="space-y-3 pb-3 border-b border-gray-100">
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <p class="font-bold text-gray-900 text-sm">{{ originCity }} ({{ originCode }})</p>
                      <p class="text-xs text-gray-400 mt-0.5">{{ formatTime(flight?.departure_time) }} - {{ formatDate(flight?.departure_date) }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                    </svg>
                  </div>
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <p class="font-bold text-gray-900 text-sm">{{ destCity }} ({{ destCode }})</p>
                      <p class="text-xs text-gray-400 mt-0.5">{{ formatTime(flight?.arrival_time) }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 shrink-0 rotate-90" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                    </svg>
                  </div>
                </div>

                <div v-else class="flex items-start justify-between gap-2">
                  <div>
                    <p class="font-bold text-gray-900 text-sm">{{ originCity }} ({{ originCode }}) ke {{ destCity }} ({{ destCode }})</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(flight?.departure_date) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ formatTime(flight?.departure_time) }} - {{ formatTime(flight?.arrival_time) }}
                      ({{ formatDuration(flight?.estimated_duration_minutes) }})
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ airlineName }} · Ekonomi</p>
                  </div>
                  <svg class="w-5 h-5 text-gray-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                  </svg>
                </div>

                <div v-if="currentStep >= 3" class="space-y-2">
                  <p class="text-sm font-bold text-gray-900">Penumpang</p>
                  <div
                    v-for="(slot, idx) in passengerSlots"
                    :key="slot.key"
                    class="flex items-center justify-between gap-2"
                  >
                    <div class="flex items-center gap-2 min-w-0">
                      <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                      </svg>
                      <span class="text-sm text-gray-700 truncate">{{ slot.label }}</span>
                    </div>
                    <button
                      type="button"
                      class="text-xs font-semibold px-2.5 py-1 rounded-lg border transition-colors shrink-0"
                      :class="activePassengerIndex === idx
                        ? 'border-navy bg-navy text-white'
                        : passengerSeats[idx]
                          ? 'border-teal text-teal'
                          : 'border-gray-200 text-gray-500 hover:border-teal hover:text-teal'"
                      @click="() => { if (currentStep === 3) selectPassenger(idx); }"
                    >
                      {{ passengerSeats[idx]?.seatNumber ?? 'Pilih Kursi' }}
                    </button>
                  </div>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tiket Dewasa ({{ totalPax }}x)</span>
                    <span class="font-semibold text-gray-800">{{ formatCurrency(basePrice) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak & Biaya</span>
                    <span class="font-semibold text-gray-800">{{ formatCurrency(tax) }}</span>
                  </div>
                  <div v-if="currentStep >= 3" class="flex justify-between text-sm">
                    <span class="text-gray-500">Pilih kursi</span>
                    <span class="font-semibold text-gray-800">{{ formatCurrency(seatSelectionFee) }}</span>
                  </div>
                  <template v-if="currentStep >= 4">
                    <p class="text-xs font-bold tracking-widest text-gray-400 pt-2">LAYANAN TAMBAHAN</p>
                    <div v-if="selectedBaggage !== 'none'" class="flex justify-between text-sm">
                      <span class="text-gray-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 7h12M6 7a2 2 0 01-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 01-2-2M9 7V5a3 3 0 013-3h0a3 3 0 013 3v2"/></svg>
                        Ekstra {{ selectedBaggage }}
                      </span>
                      <span class="font-semibold text-gray-800">{{ formatCurrency(baggagePrice) }}</span>
                    </div>
                    <div v-if="selectedInsurance !== 'none'" class="flex justify-between text-sm">
                      <span class="text-gray-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z"/></svg>
                        {{ selectedInsurance === 'premium' ? 'Premium Shield' : 'Basic Protect' }} (x{{ totalPax }})
                      </span>
                      <span class="font-semibold text-gray-800">{{ formatCurrency(insurancePrice) }}</span>
                    </div>
                  </template>
                </div>

                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                  <span class="font-bold text-gray-900">Total</span>
                  <span class="text-lg font-bold text-teal">{{ formatCurrency(total) }}</span>
                </div>
                <p v-if="currentStep >= 4" class="text-xs text-gray-400">Termasuk pajak</p>
              </div>

              <div class="px-5 pb-5 space-y-2">
                <button
                  v-if="currentStep === 2"
                  class="w-full bg-teal text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-teal-600 active:scale-[0.98] transition-all"
                  @click="lanjutKeKursi"
                >
                  <span>LANJUT KE KURSI</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
                <button
                  v-else-if="currentStep === 3"
                  class="w-full bg-teal text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-teal-600 active:scale-[0.98] transition-all"
                  @click="lanjutKeLayanan"
                >
                  <span>LANJUT KE LAYANAN</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
                <button
                  v-else-if="currentStep === 4"
                  class="w-full bg-teal text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-teal-600 active:scale-[0.98] transition-all disabled:opacity-60"
                  :disabled="submitting"
                  @click="lanjutKePembayaran"
                >
                  <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <span>LANJUT KE PEMBAYARAN</span>
                  <svg v-if="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
                <p v-if="currentStep === 4 && bookingError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">
                  {{ bookingError }}
                  <button type="button" class="ml-2 font-bold underline" @click="lanjutKePembayaran">Coba lagi</button>
                </p>
                <p v-if="currentStep === 4 && !bookingError && seatError" class="text-sm text-red-600">{{ seatError }}</p>
                <button
                  class="w-full text-sm text-gray-400 hover:text-gray-700 transition-colors py-2 text-center"
                  @click="kembali"
                >
                  ← Kembali
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { scrollbar-width: none; }
</style>
