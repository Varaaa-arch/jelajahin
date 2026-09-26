<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import { httpClient } from '@/utils/http'
import { useSeatLock, type LockedSeat } from '@/composables/useSeatLock'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
  passengerCount?: number
  adultCount?: number
  childCount?: number
}>()

// ─── Stepper ─────────────────────────────────────────────────────────────────
const steps = [
  { number: 1, label: 'Penerbangan' },
  { number: 2, label: 'Penumpang' },
  { number: 3, label: 'Kursi' },
  { number: 4, label: 'Pembayaran' },
]
const currentStep = 2

// ─── State ───────────────────────────────────────────────────────────────────
const flight    = ref<any>(null)
const selectedSeats = ref<LockedSeat[]>([])
const { unlockAll } = useSeatLock(props.flightId)

const totalPax = computed(() => props.adultCount ?? props.passengerCount ?? 1)

// Form state — 1 penumpang form (bisa di-expand nanti)
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
const submitting = ref(false)

// ─── Computed ─────────────────────────────────────────────────────────────────
const basePrice = computed(() => (flight.value?.base_price ?? 0) * totalPax.value)
const tax       = computed(() => (flight.value?.tax_surcharge ?? 0) * totalPax.value)
const total     = computed(() => basePrice.value + tax.value)

const originCode = computed(() => flight.value?.origin?.code ?? '—')
const destCode   = computed(() => flight.value?.destination?.code ?? '—')
const originCity = computed(() => flight.value?.origin?.city ?? '—')
const destCity   = computed(() => flight.value?.destination?.city ?? '—')
const airlineName = computed(() => flight.value?.airline?.name ?? '—')

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

// ─── Validation ───────────────────────────────────────────────────────────────
function validate() {
  errors.value = {}
  if (!form.value.full_name.trim())       errors.value.full_name       = 'Nama wajib diisi'
  if (!form.value.date_of_birth)          errors.value.date_of_birth   = 'Tanggal lahir wajib diisi'
  if (!form.value.identity_number.trim()) errors.value.identity_number = 'Nomor identitas wajib diisi'
  if (!form.value.email.trim())           errors.value.email           = 'Email wajib diisi'
  if (!form.value.phone.trim())           errors.value.phone           = 'Nomor telepon wajib diisi'
  return Object.keys(errors.value).length === 0
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function lanjut() {
  if (!validate()) return
  submitting.value = true

  const [firstName, ...rest] = form.value.full_name.trim().split(' ')
  const lastName = rest.join(' ') || firstName

  const passengers = Array.from({ length: totalPax.value }, (_, i) => ({
    title:           form.value.title === 'Tuan (Mr)' ? 'Mr' : form.value.title === 'Nyonya (Mrs)' ? 'Mrs' : 'Ms',
    first_name:      i === 0 ? firstName : `Penumpang ${i + 1}`,
    last_name:       i === 0 ? lastName  : `Penumpang ${i + 1}`,
    date_of_birth:   form.value.date_of_birth,
    gender:          form.value.title === 'Tuan (Mr)' ? 'M' : 'F',
    identity_type:   'id_card',
    identity_number: form.value.identity_number,
    nationality:     form.value.nationality,
  }))

  try {
    const res = await httpClient.post('/api/bookings', {
      flight_id:   flight.value?.id,
      seat_ids:    selectedSeats.value.map(s => s.seatId),
      passengers,
      user_id:     '',
    })

    const bookingId = res.data.booking?.id
    const pnr       = res.data.booking?.pnr_code
    selectedSeats.value = []

    router.visit('/booking/payment', {
      method: 'get',
      data: {
        bookingId, pnr,
        total:      total.value,
        flight:     flight.value?.flight_number ?? '',
        passengers: totalPax.value,
        method:     'credit_card',
        origin:     originCode.value,
        originCity: originCity.value,
        destination: destCode.value,
        destinationCity: destCity.value,
      },
    })
  } catch {
    const pnr = 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase()
    selectedSeats.value = []
    router.visit('/booking/payment', {
      method: 'get',
      data: {
        pnr,
        total:      total.value,
        flight:     flight.value?.flight_number ?? '',
        passengers: totalPax.value,
        method:     'credit_card',
        origin:     originCode.value,
        originCity: originCity.value,
        destination: destCode.value,
        destinationCity: destCity.value,
      },
    })
  } finally {
    submitting.value = false
  }
}

function kembali() {
  router.visit(route('booking.review', { flightId: props.flightId }))
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  const seatsRaw = sessionStorage.getItem('selectedSeats')
  if (seatsRaw) selectedSeats.value = JSON.parse(seatsRaw)

  try {
    const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
    flight.value = res.data?.data ?? res.data
  } catch {}
})

onBeforeUnmount(async () => {
  if (selectedSeats.value.length > 0) {
    await unlockAll()
    sessionStorage.removeItem('selectedSeats')
  }
})
</script>

<template>
  <Head title="Data Penumpang — Jelajahin" />

  <div class="h-screen overflow-hidden bg-gray-50 flex flex-col">
    <Navbar />

    <!-- Stepper bar -->
    <div class="bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-5xl mx-auto px-6 pt-20 pb-4">
        <div class="flex items-center justify-center gap-0">
          <template v-for="(step, idx) in steps" :key="step.number">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all shrink-0"
                :class="step.number === currentStep
                  ? 'bg-navy border-navy text-white'
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

    <!-- Main -->
    <main class="flex-1 overflow-hidden py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-full flex flex-col">
        <div class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0">

          <!-- LEFT: Form -->
          <div class="flex-1 min-h-0 overflow-y-auto pr-1">
            <h1 class="text-2xl font-bold text-gray-900 mb-5">Detail Penumpang</h1>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">

              <!-- Header penumpang -->
              <div class="flex items-center gap-2 pb-4 border-b border-gray-100">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <h2 class="font-bold text-gray-900">Data Penumpang 1 (Dewasa)</h2>
              </div>

              <!-- Titel + Nama -->
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

              <!-- Tanggal lahir + Kewarganegaraan -->
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

              <!-- Nomor KTP/Paspor -->
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

              <!-- Divider -->
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

          </div>
          <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden sticky top-24">

              <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">Ringkasan Pesanan</h2>
              </div>

              <div class="px-5 py-4 space-y-3">
                <!-- Flight info -->
                <div class="flex items-start justify-between gap-2">
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

                <div class="border-t border-gray-100 pt-3 space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tiket Dewasa ({{ totalPax }}x)</span>
                    <span class="font-semibold text-gray-800">{{ formatCurrency(basePrice) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak & Biaya</span>
                    <span class="font-semibold text-gray-800">{{ formatCurrency(tax) }}</span>
                  </div>
                </div>

                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                  <span class="font-bold text-gray-900">Total</span>
                  <span class="text-lg font-bold text-gray-900">{{ formatCurrency(total) }}</span>
                </div>
              </div>

              <!-- Tombol di dalam sidebar -->
              <div class="px-5 pb-5 space-y-2">
                <button
                  class="w-full bg-navy text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-navy/90 active:scale-[0.98] transition-all disabled:opacity-60"
                  :disabled="submitting"
                  @click="lanjut"
                >
                  <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <span>LANJUT KE KURSI</span>
                  <svg v-if="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
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
