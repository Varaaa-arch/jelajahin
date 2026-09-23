<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'
import { httpClient } from '@/utils/http'
import type { Flight } from '@/types/flight'
import type { LockedSeat } from '@/composables/useSeatLock'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
}>()

// ─── State ────────────────────────────────────────────────────────────────────
const flight = ref<Flight | null>(null)
const flightLoading = ref(true)
const activeTab = ref<'detail' | 'fasilitas' | 'kebijakan' | 'ulasan'>('detail')
const selectedSeats = ref<LockedSeat[]>([])

// Simulasi data penumpang dari search params / session
const passengerCount = ref(3)  // 2 Dewasa, 1 Anak (sesuai desain)
const adultCount = ref(2)
const childCount = ref(1)

// Penerbangan lain (saran)
const otherFlights = ref([
  { airline: 'Batik Air', dep: '11:30', arr: '14:20', price: 3050000, logo: 'BA' },
  { airline: 'Citilink',  dep: '14:00', arr: '16:55', price: 2800000, logo: 'QG' },
  { airline: 'Garuda Indonesia', dep: '18:15', arr: '21:05', price: 3450000, logo: 'GA' },
])

// ─── Computed ─────────────────────────────────────────────────────────────────
const originCode = computed(() => flight.value?.origin?.code ?? 'CGK')
const destCode   = computed(() => flight.value?.destination?.code ?? 'DPS')
const originCity = computed(() => flight.value?.origin?.city ?? 'Jakarta')
const destCity   = computed(() => flight.value?.destination?.city ?? 'Bali')

const basePrice = computed(() => flight.value?.base_price ?? 1250000)

// Hitung total harga: harga dasar × jumlah penumpang + pajak - diskon
const totalPassengers = computed(() => adultCount.value + childCount.value)
const hargaDasar = computed(() => basePrice.value * totalPassengers.value)
const diskonPromo = computed(() => Math.round(hargaDasar.value * 0.15)) // 15% promo
const pajakBiaya  = computed(() => 50000)
const totalPembayaran = computed(() => hargaDasar.value - diskonPromo.value + pajakBiaya.value)

// Tanggal penerbangan (dari data atau fallback)
const tanggalPenerbangan = computed(() => {
  if (!flight.value?.departure_date) return '15 Nov 2024'
  const d = new Date(flight.value.departure_date)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
})

// ─── Helpers ─────────────────────────────────────────────────────────────────
const formatRupiah = (n: number) =>
  'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n))

const formatDuration = (minutes: number) => {
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}j ${m}m` : `${m}m`
}

// ─── Handlers ────────────────────────────────────────────────────────────────
const proceedToPayment = () => {
  if (!flight.value) return
  sessionStorage.setItem('selectedFlight', JSON.stringify(flight.value))
  router.visit(`/booking/create?flightId=${props.flightId}`)
}

const selectOtherFlight = (idx: number) => {
  // Placeholder — idealnya navigate ke flight lain
  console.log('select other flight', idx)
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const res = await httpClient.get(`/api/v1/flights/${props.flightId}`)
    flight.value = res.data.data ?? res.data
  } catch (err) {
    console.error('[Detail.vue] Failed to load flight:', err)
  } finally {
    flightLoading.value = false
  }
})
</script>

<template>
  <Head :title="flight ? `${originCode} → ${destCode} | Jelajahin` : 'Detail Penerbangan'" />

  <div class="min-h-screen bg-navy flex flex-col">
    <Navbar />

    <!-- Page body with top padding for fixed navbar -->
    <main class="flex-1 pt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

        <!-- ── Loading skeleton ─────────────────────────────────────── -->
        <div v-if="flightLoading" class="grid lg:grid-cols-[1fr_320px] gap-6">
          <div class="bg-navy-mid border border-white/8 rounded-2xl p-6 animate-pulse space-y-4">
            <div class="h-6 bg-white/10 rounded w-1/2"></div>
            <div class="h-4 bg-white/10 rounded w-1/3"></div>
            <div class="h-32 bg-white/10 rounded"></div>
          </div>
          <div class="bg-navy-mid border border-white/8 rounded-2xl p-6 animate-pulse space-y-4">
            <div class="h-5 bg-white/10 rounded w-2/3"></div>
            <div class="h-4 bg-white/10 rounded"></div>
            <div class="h-4 bg-white/10 rounded w-4/5"></div>
            <div class="h-12 bg-white/10 rounded mt-6"></div>
          </div>
        </div>

        <!-- ── Main content ─────────────────────────────────────────── -->
        <div v-else class="grid lg:grid-cols-[1fr_320px] gap-6">

          <!-- ── Left column ─────────────────────────────────────── -->
          <div class="space-y-6">

            <!-- Flight card -->
            <div class="bg-navy-mid border border-white/8 rounded-2xl p-5 sm:p-6">

              <!-- Airline header row -->
              <div class="flex items-start gap-4">
                <!-- Airline logo placeholder -->
                <div class="shrink-0 w-12 h-12 rounded-xl bg-white/8 border border-white/10 flex items-center justify-center text-white font-bold text-sm">
                  {{ flight?.airline?.code ?? '✈' }}
                </div>

                <!-- Title -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-white font-bold text-lg sm:text-xl">
                      {{ originCity }} ({{ originCode }})
                      <span class="text-teal mx-1">→</span>
                      {{ destCity }} ({{ destCode }})
                    </h1>
                  </div>
                  <div class="flex items-center gap-2 mt-1">
                    <!-- Airline icon + name -->
                    <svg class="w-3.5 h-3.5 text-white/40 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                    </svg>
                    <span class="text-white/50 text-sm">{{ flight?.airline?.name ?? 'Maskapai' }}</span>
                    <span class="text-white/25">•</span>
                    <span class="text-white/50 text-sm font-mono">{{ flight?.flight_number ?? 'GA-402' }}</span>
                    <span class="text-white/25">•</span>
                    <span class="px-2 py-0.5 bg-teal/15 border border-teal/25 text-teal text-xs font-semibold rounded-full">
                      {{ flight?.seat_class ?? 'Ekonomi' }}
                    </span>
                  </div>
                </div>

                <!-- Price top-right -->
                <div class="shrink-0 text-right">
                  <p class="text-white font-bold text-lg sm:text-xl">
                    {{ formatRupiah(basePrice) }}
                  </p>
                  <p class="text-white/40 text-xs">per pax</p>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-white/7 my-5"></div>

              <!-- Tabs -->
              <nav class="flex gap-1 mb-6" role="tablist" aria-label="Tab detail penerbangan">
                <button
                  v-for="tab in [
                    { key: 'detail',    label: 'Detail Penerbangan' },
                    { key: 'fasilitas', label: 'Fasilitas' },
                    { key: 'kebijakan', label: 'Kebijakan' },
                    { key: 'ulasan',    label: 'Ulasan' },
                  ]"
                  :key="tab.key"
                  :role="'tab'"
                  :aria-selected="activeTab === tab.key"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-md transition-all border-b-2',
                    activeTab === tab.key
                      ? 'text-white border-teal bg-teal/8'
                      : 'text-white/50 border-transparent hover:text-white/80 hover:bg-white/5',
                  ]"
                  @click="activeTab = tab.key as any"
                >
                  {{ tab.label }}
                </button>
              </nav>

              <!-- Tab panels -->
              <!-- Detail Penerbangan -->
              <div v-if="activeTab === 'detail'" role="tabpanel" aria-label="Detail Penerbangan">

                <!-- Route visual (dep → arr) -->
                <div v-if="flight" class="flex items-center gap-4 mb-6">
                  <!-- Origin -->
                  <div class="shrink-0">
                    <p class="text-white font-bold text-2xl">{{ flight.departure_time?.substring(0, 5) ?? '—' }}</p>
                    <p class="text-white/50 text-sm mt-0.5">{{ originCode }}</p>
                    <p class="text-white/35 text-xs">{{ flight.origin?.city }}</p>
                  </div>

                  <!-- Duration bar -->
                  <div class="flex-1 flex flex-col items-center">
                    <p class="text-white/35 text-xs mb-1">
                      {{ flight.route?.estimated_duration_minutes
                          ? formatDuration(flight.route.estimated_duration_minutes)
                          : flight.estimated_duration_minutes
                            ? formatDuration(Number(flight.estimated_duration_minutes))
                            : '2j 15m' }}
                    </p>
                    <div class="w-full flex items-center gap-2">
                      <div class="w-2 h-2 rounded-full bg-teal shrink-0"></div>
                      <div class="flex-1 border-t border-dashed border-white/20 relative">
                        <svg class="w-4 h-4 text-teal/70 absolute -top-2 left-1/2 -translate-x-1/2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                        </svg>
                      </div>
                      <div class="w-2 h-2 rounded-full bg-white/40 shrink-0"></div>
                    </div>
                    <p class="text-white/30 text-xs mt-1">{{ flight.departure_date ?? tanggalPenerbangan }}</p>
                  </div>

                  <!-- Destination -->
                  <div class="shrink-0 text-right">
                    <p class="text-white font-bold text-2xl">{{ flight.arrival_time?.substring(0, 5) ?? '—' }}</p>
                    <p class="text-white/50 text-sm mt-0.5">{{ destCode }}</p>
                    <p class="text-white/35 text-xs">{{ flight.destination?.city }}</p>
                  </div>
                </div>

                <!-- Placeholder when no flight loaded -->
                <div v-else class="text-white/30 text-center py-10">
                  <p>Data penerbangan tidak tersedia.</p>
                </div>

                <!-- Info grid -->
                <div v-if="flight" class="grid sm:grid-cols-2 gap-3">
                  <div class="bg-navy/60 border border-white/6 rounded-xl p-4">
                    <p class="text-white/40 text-xs mb-1">Nomor Penerbangan</p>
                    <p class="text-white font-semibold font-mono">{{ flight.flight_number ?? 'GA-402' }}</p>
                  </div>
                  <div class="bg-navy/60 border border-white/6 rounded-xl p-4">
                    <p class="text-white/40 text-xs mb-1">Kelas</p>
                    <p class="text-white font-semibold capitalize">{{ flight.seat_class ?? 'Ekonomi' }}</p>
                  </div>
                  <div class="bg-navy/60 border border-white/6 rounded-xl p-4">
                    <p class="text-white/40 text-xs mb-1">Maskapai</p>
                    <p class="text-white font-semibold">{{ flight.airline?.name ?? 'Garuda Indonesia' }}</p>
                  </div>
                  <div class="bg-navy/60 border border-white/6 rounded-xl p-4">
                    <p class="text-white/40 text-xs mb-1">Kursi Tersedia</p>
                    <p class="font-semibold" :class="(flight.seats_available ?? 10) < 10 ? 'text-orange-400' : 'text-white'">
                      {{ flight.seats_available ?? '—' }}
                      <span v-if="(flight.seats_available ?? 10) < 10" class="text-xs font-normal text-orange-400 ml-1">tersisa!</span>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Fasilitas tab -->
              <div v-else-if="activeTab === 'fasilitas'" role="tabpanel" aria-label="Fasilitas">
                <div class="grid sm:grid-cols-2 gap-3">
                  <div
                    v-for="item in [
                      { icon: '🧳', label: 'Bagasi 20 kg', desc: 'Bagasi kabin 7 kg termasuk' },
                      { icon: '🍽️', label: 'Makan Tersedia', desc: 'Snack & minuman gratis' },
                      { icon: '💺', label: 'Kursi Standar', desc: 'Pitch 30" • Lebar 18"' },
                      { icon: '🔌', label: 'USB Charging', desc: 'Di setiap kursi' },
                      { icon: '📶', label: 'Wi-Fi Berbayar', desc: 'Paket mulai Rp 50.000' },
                      { icon: '🎬', label: 'Hiburan Onboard', desc: 'Layar 10" personal IFE' },
                    ]"
                    :key="item.label"
                    class="flex items-start gap-3 bg-navy/60 border border-white/6 rounded-xl p-4"
                  >
                    <span class="text-xl leading-none mt-0.5">{{ item.icon }}</span>
                    <div>
                      <p class="text-white text-sm font-semibold">{{ item.label }}</p>
                      <p class="text-white/40 text-xs mt-0.5">{{ item.desc }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Kebijakan tab -->
              <div v-else-if="activeTab === 'kebijakan'" role="tabpanel" aria-label="Kebijakan">
                <div class="space-y-4">
                  <div
                    v-for="pol in [
                      { title: 'Perubahan Jadwal', body: 'Perubahan jadwal dikenakan biaya Rp 150.000 per orang. Pengajuan minimal 2 jam sebelum keberangkatan.' },
                      { title: 'Pembatalan Tiket', body: 'Pembatalan lebih dari 24 jam: pengembalian 75%. Pembatalan kurang dari 24 jam: pengembalian 50%.' },
                      { title: 'Bagasi Lebih', body: 'Biaya bagasi lebih Rp 30.000 per kg. Maksimal tambahan 20 kg.' },
                      { title: 'Check-in', body: 'Check-in online tersedia H-1 hingga 4 jam sebelum keberangkatan. Counter check-in ditutup 45 menit sebelum jadwal.' },
                    ]"
                    :key="pol.title"
                    class="bg-navy/60 border border-white/6 rounded-xl p-4"
                  >
                    <p class="text-white font-semibold text-sm mb-1">{{ pol.title }}</p>
                    <p class="text-white/50 text-sm leading-relaxed">{{ pol.body }}</p>
                  </div>
                </div>
              </div>

              <!-- Ulasan tab -->
              <div v-else-if="activeTab === 'ulasan'" role="tabpanel" aria-label="Ulasan">
                <!-- Rating summary -->
                <div class="flex items-center gap-4 bg-navy/60 border border-white/6 rounded-xl p-4 mb-4">
                  <div class="text-center">
                    <p class="text-white font-black text-4xl leading-none">4.3</p>
                    <div class="flex gap-0.5 mt-1 justify-center">
                      <span v-for="i in 5" :key="i" :class="i <= 4 ? 'text-yellow-400' : 'text-white/20'" class="text-base">★</span>
                    </div>
                    <p class="text-white/35 text-xs mt-1">128 ulasan</p>
                  </div>
                  <div class="flex-1 space-y-1.5">
                    <div v-for="n in [5,4,3,2,1]" :key="n" class="flex items-center gap-2">
                      <span class="text-white/40 text-xs w-3">{{ n }}</span>
                      <div class="flex-1 bg-white/8 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="h-full bg-yellow-400 rounded-full"
                          :style="`width: ${[40, 35, 15, 7, 3][5 - n]}%`"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Sample reviews -->
                <div class="space-y-3">
                  <div
                    v-for="rev in [
                      { name: 'Budi S.', rating: 5, text: 'Penerbangan tepat waktu, crew sangat ramah. Makanannya enak!', date: '10 Nov 2024' },
                      { name: 'Sari R.', rating: 4, text: 'Kursi nyaman, tapi boarding agak lambat. Secara keseluruhan oke.', date: '8 Nov 2024' },
                      { name: 'Doni K.', rating: 4, text: 'Hiburan onboard bagus, koneksi WiFi cukup kencang.', date: '5 Nov 2024' },
                    ]"
                    :key="rev.name"
                    class="bg-navy/60 border border-white/6 rounded-xl p-4"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-teal/20 border border-teal/25 flex items-center justify-center text-teal text-xs font-bold">
                          {{ rev.name[0] }}
                        </div>
                        <span class="text-white text-sm font-medium">{{ rev.name }}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <span v-for="i in 5" :key="i" :class="i <= rev.rating ? 'text-yellow-400' : 'text-white/15'" class="text-xs">★</span>
                        <span class="text-white/30 text-xs ml-1">{{ rev.date }}</span>
                      </div>
                    </div>
                    <p class="text-white/55 text-sm leading-relaxed">{{ rev.text }}</p>
                  </div>
                </div>
              </div>

            </div><!-- /Flight card -->

            <!-- ── Penerbangan Lainnya ─────────────────────────────── -->
            <div>
              <h2 class="text-white font-bold text-lg mb-4">Penerbangan Lainnya</h2>
              <div class="grid sm:grid-cols-3 gap-4">
                <button
                  v-for="(fl, idx) in otherFlights"
                  :key="idx"
                  class="bg-navy-mid border border-white/8 hover:border-teal/40 hover:bg-navy-light rounded-2xl p-4 text-left transition-all group focus:outline-none focus:ring-2 focus:ring-teal/40"
                  :aria-label="`Pilih penerbangan ${fl.airline} ${fl.dep} - ${fl.arr}`"
                  @click="selectOtherFlight(idx)"
                >
                  <!-- Airline name row -->
                  <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-white/8 border border-white/10 flex items-center justify-center text-white text-xs font-bold group-hover:bg-teal/15 group-hover:border-teal/20 transition-all">
                      {{ fl.logo }}
                    </div>
                    <span class="text-white/60 text-sm group-hover:text-white/80 transition-colors">{{ fl.airline }}</span>
                  </div>

                  <!-- Times -->
                  <div class="flex items-center gap-2 mb-2">
                    <span class="text-white font-bold text-lg">{{ fl.dep }}</span>
                    <svg class="w-4 h-4 text-teal/60 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                    </svg>
                    <span class="text-white font-bold text-lg">{{ fl.arr }}</span>
                  </div>

                  <!-- Price -->
                  <p class="text-teal text-sm font-semibold">{{ formatRupiah(fl.price) }}</p>
                </button>
              </div>
            </div>

          </div><!-- /Left column -->

          <!-- ── Right column — Order Summary ─────────────────────── -->
          <aside aria-label="Rincian Pemesanan" class="lg:sticky lg:top-20 self-start">
            <div class="bg-navy-mid border border-white/8 rounded-2xl p-5 space-y-5">

              <!-- Title -->
              <div>
                <h2 class="text-white font-bold text-base">Rincian Pemesanan</h2>
                <p class="text-white/40 text-sm mt-0.5">
                  {{ flight?.airline?.name ?? 'Garuda Indonesia' }}
                  ({{ flight?.flight_number ?? 'GA-402' }})
                </p>
              </div>

              <!-- Details list -->
              <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Rute</span>
                  <span class="text-white font-semibold">{{ originCode }} → {{ destCode }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Tanggal</span>
                  <span class="text-white font-semibold">{{ tanggalPenerbangan }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Penumpang</span>
                  <span class="text-white font-semibold">{{ adultCount }} Dewasa, {{ childCount }} Anak</span>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-white/7"></div>

              <!-- Price breakdown -->
              <div class="space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Harga Dasar (×{{ totalPassengers }})</span>
                  <span class="text-white">{{ formatRupiah(hargaDasar) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Diskon Promo</span>
                  <span class="text-teal font-medium">-{{ formatRupiah(diskonPromo) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/50">Pajak &amp; Biaya</span>
                  <span class="text-white">+{{ formatRupiah(pajakBiaya) }}</span>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-white/7"></div>

              <!-- Total -->
              <div class="flex items-end justify-between">
                <div>
                  <p class="text-white/50 text-xs">Total Pembayaran</p>
                </div>
                <div class="text-right">
                  <p class="text-white font-black text-2xl leading-tight">
                    {{ formatRupiah(totalPembayaran) }}
                  </p>
                </div>
              </div>

              <!-- CTA button -->
              <button
                class="w-full bg-teal hover:bg-teal-dark text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-teal/20 hover:-translate-y-px active:translate-y-0 flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-teal/50"
                @click="proceedToPayment"
              >
                LANJUT KE PEMBAYARAN
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
              </button>

              <!-- Fine print -->
              <p class="text-white/25 text-xs text-center leading-relaxed">
                Dengan melanjutkan, Anda menyetujui Syarat &amp; Ketentuan.
              </p>

            </div>
          </aside>

        </div><!-- /Main content grid -->
      </div><!-- /container -->
    </main>

    <AppFooter />
  </div>
</template>
