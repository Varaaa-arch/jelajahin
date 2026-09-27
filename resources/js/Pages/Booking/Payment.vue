<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Navbar />

    <!-- Stepper -->
    <div class="bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-6xl mx-auto px-6 pt-20 pb-4">
        <div class="flex items-center justify-center gap-0">
          <template v-for="(s, idx) in steps" :key="s.number">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 shrink-0"
                :class="s.number <= 4 ? 'bg-teal border-teal text-white' : s.number === 5 ? 'bg-teal border-teal text-white' : 'bg-white border-gray-200 text-gray-400'"
              >
                <svg v-if="s.number < 5" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span v-else>{{ s.number }}</span>
              </div>
              <span class="text-sm font-semibold hidden sm:block" :class="s.number <= 5 ? 'text-teal' : 'text-gray-400'">{{ s.label }}</span>
            </div>
            <div v-if="idx < steps.length - 1" class="w-12 sm:w-20 h-px mx-2" :class="s.number < 5 ? 'bg-teal' : 'bg-gray-200'" />
          </template>
        </div>
      </div>
    </div>

    <main class="flex-1 py-6 pb-10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <!-- Header -->
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-teal rounded-xl mb-3 shadow-md shadow-teal/20">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-gray-900">Pembayaran Aman</h1>
          <p class="text-sm text-gray-500 mt-1 flex items-center justify-center gap-1.5">
            <Lock class="w-3.5 h-3.5 text-gray-400" />
            Pembayaran dilindungi enkripsi SSL 256-bit
          </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 items-start">
          <!-- Left: Payment Methods -->
          <div class="flex-1 min-w-0">
            <!-- Order Summary mini (mobile) -->
            <div class="lg:hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
              <div class="flex justify-between items-center">
                <div>
                  <p class="text-xs text-gray-500">Order ID</p>
                  <p class="font-mono font-bold text-gray-900">{{ orderId }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-500">Total</p>
                  <p class="text-xl font-bold text-teal">Rp {{ formatPrice(totalAmount) }}</p>
                </div>
              </div>
              <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm text-gray-600">
                <span class="flex items-center gap-1.5"><Plane class="w-4 h-4 text-gray-400" /> {{ flightNumber || '—' }}</span>
                <span>{{ passengerCount }} Penumpang</span>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" v-if="currentStep === 'form'">
              <div class="flex border-b border-gray-100">
                <button
                  v-for="tab in paymentTabs"
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  :class="['flex-1 py-4 text-sm font-semibold transition-all flex items-center justify-center gap-1.5', activeTab === tab.id ? 'text-teal border-b-2 border-teal bg-teal-50' : 'text-gray-500 hover:text-gray-700']"
                >
                  <component :is="tab.icon" class="w-4 h-4" /> {{ tab.label }}
                </button>
              </div>

              <!-- Card -->
              <div v-if="activeTab === 'card'" class="p-6">
                <div v-if="savedCards.length" class="mb-4">
                  <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Kartu tersimpan</p>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="m in savedCards"
                      :id="`saved-${m.id}`"
                      :key="m.id"
                      type="button"
                      @click="applySavedMethod(m)"
                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 transition hover:border-teal hover:text-teal"
                    >
                      {{ m.provider.toUpperCase() }} • {{ m.masked_number }}
                      <Star v-if="m.is_default" class="w-3 h-3 text-teal-500" />
                    </button>
                  </div>
                  <p class="mt-1.5 text-[11px] text-gray-400">Nama & masa berlaku terisi otomatis, nomor kartu tetap diketik manual.</p>
                </div>
                <div class="relative h-44 rounded-2xl p-5 text-white mb-5 overflow-hidden shadow-md" :class="cardPreviewClass">
                  <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-white to-transparent pointer-events-none"></div>
                  <div class="flex justify-between items-start mb-6">
                    <span class="text-lg font-bold tracking-widest">JELAJAHIN</span>
                    <span class="text-[10px] font-bold tracking-widest px-2 py-1 rounded-md bg-white/20">{{ cardNetworkLabel }}</span>
                  </div>
                  <p class="font-mono text-xl tracking-widest mb-4">{{ maskedCardNumber }}</p>
                  <div class="flex justify-between text-sm">
                    <div>
                      <p class="opacity-70 text-xs mb-1 tracking-widest">CARDHOLDER</p>
                      <p class="uppercase font-medium">{{ cardForm.name || 'YOUR NAME' }}</p>
                    </div>
                    <div class="text-right">
                      <p class="opacity-70 text-xs mb-1 tracking-widest">EXPIRES</p>
                      <p>{{ cardForm.expiry || 'MM/YY' }}</p>
                    </div>
                  </div>
                </div>
                <div class="flex gap-2 mb-5">
                  <span v-for="card in ['VISA','MC','AMEX']" :key="card" class="px-2.5 py-1 border border-gray-200 rounded-lg text-xs text-gray-500 font-bold tracking-widest">{{ card }}</span>
                </div>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Kartu</label>
                    <input v-model="cardForm.number" @input="formatCardNumber" maxlength="19" placeholder="0000 0000 0000 0000" class="w-full border border-gray-200 rounded-xl px-4 py-3 font-mono text-lg tracking-widest focus:ring-2 focus:ring-teal/20 focus:border-teal outline-none transition" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Pemegang Kartu</label>
                    <input v-model="cardForm.name" placeholder="Nama sesuai kartu" class="w-full border border-gray-200 rounded-xl px-4 py-3 uppercase focus:ring-2 focus:ring-teal/20 focus:border-teal outline-none transition" />
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1.5">Masa Berlaku</label>
                      <input v-model="cardForm.expiry" @input="formatExpiry" maxlength="5" placeholder="MM/YY" class="w-full border border-gray-200 rounded-xl px-4 py-3 font-mono focus:ring-2 focus:ring-teal/20 focus:border-teal outline-none transition" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1.5">CVV</label>
                      <input v-model="cardForm.cvv" type="password" maxlength="4" placeholder="•••" class="w-full border border-gray-200 rounded-xl px-4 py-3 font-mono focus:ring-2 focus:ring-teal/20 focus:border-teal outline-none transition" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bank -->
              <div v-else-if="activeTab === 'bank'" class="p-6">
                <div v-if="savedBanks.length" class="mb-4">
                  <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Tersimpan</p>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="m in savedBanks"
                      :id="`saved-${m.id}`"
                      :key="m.id"
                      type="button"
                      @click="applySavedMethod(m)"
                      :class="['inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition',
                        selectedBank === m.provider ? 'border-teal bg-teal-50 text-teal-700' : 'border-gray-200 text-gray-600 hover:border-teal']"
                    >
                      {{ m.provider.toUpperCase() }} • {{ m.masked_number }}
                      <Star v-if="m.is_default" class="w-3 h-3 text-teal-500" />
                    </button>
                  </div>
                </div>
                <p class="text-sm text-gray-500 mb-4">Pilih bank untuk transfer:</p>
                <div class="space-y-3">
                  <label v-for="bank in bankOptions" :key="bank.id" :class="['flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all', selectedBank === bank.id ? 'border-teal bg-teal-50' : 'border-gray-200 hover:border-gray-300']">
                    <input type="radio" :value="bank.id" v-model="selectedBank" class="sr-only" />
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-white text-sm mr-3" :class="bank.color">{{ bank.code }}</div>
                    <div class="flex-1">
                      <p class="font-semibold text-gray-900">{{ bank.name }}</p>
                      <p class="text-sm text-gray-500">Transfer ke rekening virtual</p>
                    </div>
                    <div v-if="selectedBank === bank.id" class="text-teal">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                  </label>
                </div>
                <div v-if="selectedBank" class="mt-4 bg-gray-50 rounded-xl p-4 border border-gray-100">
                  <p class="text-xs text-gray-500 mb-1">Nomor Virtual Account</p>
                  <div class="flex items-center justify-between gap-3">
                    <p class="font-mono text-xl font-bold text-gray-900 tracking-wider truncate">{{ virtualAccountNumber }}</p>
                    <button @click="copyVA" class="text-teal text-sm font-semibold hover:text-teal-700 shrink-0 flex items-center gap-1">
                      <Copy v-if="!vaCopied" class="w-4 h-4" />
                      <Check v-else class="w-4 h-4" />
                      {{ vaCopied ? 'Copied!' : 'Copy' }}
                    </button>
                  </div>
                  <p class="text-xs text-amber-600 mt-2 flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 shrink-0" />
                    Bayar dalam 24 jam sebelum kedaluwarsa
                  </p>
                </div>
              </div>

              <!-- E-Wallet -->
              <div v-else-if="activeTab === 'ewallet'" class="p-6">
                <div v-if="savedWallets.length" class="mb-4">
                  <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Tersimpan</p>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="m in savedWallets"
                      :id="`saved-${m.id}`"
                      :key="m.id"
                      type="button"
                      @click="applySavedMethod(m)"
                      :class="['inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition',
                        selectedWallet === m.provider ? 'border-teal bg-teal-50 text-teal-700' : 'border-gray-200 text-gray-600 hover:border-teal']"
                    >
                      {{ m.account_name }} • {{ m.masked_number }}
                      <Star v-if="m.is_default" class="w-3 h-3 text-teal-500" />
                    </button>
                  </div>
                </div>
                <p class="text-sm text-gray-500 mb-4">Pilih e-wallet kamu:</p>
                <div class="grid grid-cols-2 gap-3">
                  <label v-for="wallet in ewalletOptions" :key="wallet.id" :class="['flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all', selectedWallet === wallet.id ? 'border-teal bg-teal-50' : 'border-gray-200 hover:border-gray-300']">
                    <input type="radio" :value="wallet.id" v-model="selectedWallet" class="sr-only" />
                    <span class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-white text-sm mb-2" :class="wallet.color">{{ wallet.initials }}</span>
                    <span class="font-semibold text-sm text-gray-700">{{ wallet.name }}</span>
                    <span v-if="selectedWallet === wallet.id" class="mt-1 text-teal text-xs font-semibold flex items-center gap-1">
                      <Check class="w-3.5 h-3.5" /> Dipilih
                    </span>
                  </label>
                </div>
                <div v-if="selectedWallet" class="mt-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor HP / Akun {{ selectedWalletName }}</label>
                  <div class="flex">
                    <span class="px-3 py-3 bg-gray-100 border border-r-0 border-gray-200 rounded-l-xl text-gray-500 text-sm">+62</span>
                    <input v-model="walletPhone" type="tel" placeholder="8xx xxxx xxxx" class="flex-1 border border-gray-200 rounded-r-xl px-4 py-3 focus:ring-2 focus:ring-teal/20 focus:border-teal outline-none transition" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Processing -->
            <div v-if="currentStep === 'processing'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
              <div class="w-20 h-20 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-teal animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900 mb-2">Memproses Pembayaran...</h2>
              <p class="text-gray-500 text-sm mb-6">Mohon tunggu, jangan tutup halaman ini</p>
              <div class="text-left space-y-3 max-w-xs mx-auto">
                <div v-for="(step, idx) in processingSteps" :key="idx" class="flex items-center gap-3">
                  <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-xs shrink-0', processingIndex > idx ? 'bg-green-500 text-white' : processingIndex === idx ? 'bg-teal text-white animate-pulse' : 'bg-gray-200 text-gray-400']">
                    <Check v-if="processingIndex > idx" class="w-3.5 h-3.5" />
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <span :class="['text-sm', processingIndex >= idx ? 'text-gray-900 font-medium' : 'text-gray-400']">{{ step }}</span>
                </div>
              </div>
            </div>

            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 mt-4 flex items-start gap-3">
              <TriangleAlert class="text-red-500 w-5 h-5 shrink-0" />
              <p class="text-sm text-red-700">{{ errorMessage }}</p>
            </div>

            <div v-if="currentStep === 'form'" class="flex gap-3 mt-4">
              <button @click="goBack" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">← Kembali</button>
              <button @click="processPayment" :disabled="!isFormValid" :class="['flex-1 py-3 rounded-xl font-bold text-white transition-all flex items-center justify-center gap-2', isFormValid ? 'bg-teal hover:bg-teal-600 shadow-lg shadow-teal/20' : 'bg-gray-300 cursor-not-allowed']">
                <span>Bayar</span><span class="bg-white/20 px-2 py-0.5 rounded-lg text-sm">Rp {{ formatPrice(totalAmount) }}</span>
              </button>
            </div>
            <p v-if="currentStep === 'form'" class="text-center text-xs text-gray-400 mt-3 flex items-center justify-center gap-1.5">
              <Lock class="w-3.5 h-3.5" /> Enkripsi 256-bit • Aman & Terpercaya
            </p>
          </div>

          <!-- Right: Ringkasan -->
          <div class="w-full lg:w-80 shrink-0 lg:sticky lg:top-24">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">Ringkasan Pesanan</h2>
              </div>
              <div class="px-5 py-4 space-y-3">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-xs text-gray-500">Order ID</p>
                    <p class="font-mono font-bold text-gray-900 text-sm">{{ orderId }}</p>
                  </div>
                  <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Menunggu Bayar</span>
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-between text-sm">
                  <span class="text-gray-500">Penerbangan</span>
                  <span class="font-semibold text-gray-900 flex items-center gap-1.5"><Plane class="w-4 h-4 text-gray-400" /> {{ flightNumber || '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Penumpang</span>
                  <span class="font-semibold text-gray-900">{{ passengerCount }} Orang</span>
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                  <span class="font-bold text-gray-900">Total</span>
                  <span class="text-xl font-bold text-teal">Rp {{ formatPrice(totalAmount) }}</span>
                </div>
                <p class="text-xs text-gray-400">Termasuk pajak & biaya</p>
              </div>
              <div class="px-5 pb-5">
                <div class="bg-teal-50 border border-teal-100 rounded-xl p-3 flex gap-2">
                  <ShieldCheck class="text-teal w-4 h-4 shrink-0 mt-0.5" />
                  <p class="text-xs text-teal-800 leading-relaxed">Jaminan harga tetap & refund fleksibel. E-tiket dikirim ke email setelah pembayaran.</p>
                </div>
              </div>
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-400">
              <span class="flex items-center gap-1"><Lock class="w-3.5 h-3.5" /> SSL</span><span>•</span><span>Visa</span><span>•</span><span>Mastercard</span><span>•</span><span>BCA</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import { httpClient } from '@/utils/http'
import {
  Lock,
  Plane,
  CreditCard,
  Landmark,
  Wallet,
  Check,
  Clock,
  TriangleAlert,
  ShieldCheck,
  Copy,
  Star,
} from 'lucide-vue-next'

interface SavedMethod {
  id: string
  type: 'bank_account' | 'e_wallet' | 'card'
  provider: string
  label: string | null
  account_name: string
  masked_number: string
  expiry: string | null
  is_default: boolean
}

const props = defineProps<{
  bookingId?: string
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

const steps = [
  { number: 1, label: 'Penerbangan' },
  { number: 2, label: 'Penumpang' },
  { number: 3, label: 'Kursi' },
  { number: 4, label: 'Layanan' },
  { number: 5, label: 'Pembayaran' },
]

const currentStep = ref<'form' | 'processing'>('form')
const errorMessage = ref('')
const orderId = ref(props.pnrCode || 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase())
const totalAmount = ref(props.totalAmount || 0)

type PaymentTabId = 'card' | 'bank' | 'ewallet'

const resolveInitialTab = (): PaymentTabId => {
  if (props.paymentMethod === 'bank_transfer') return 'bank'
  if (props.paymentMethod === 'ewallet') return 'ewallet'
  return 'card'
}

const activeTab = ref<PaymentTabId>(resolveInitialTab())

const cardForm = ref({ number: '', name: '', expiry: '', cvv: '' })
const selectedBank = ref('')
const vaCopied = ref(false)
const selectedWallet = ref('')
const walletPhone = ref('')
const processingIndex = ref(0)

// ─── Metode tersimpan (prefill dari dashboard) ──────────────────────────────
const savedMethods = ref<SavedMethod[]>([])
const savedBanks = computed(() => savedMethods.value.filter(m => m.type === 'bank_account'))
const savedWallets = computed(() => savedMethods.value.filter(m => m.type === 'e_wallet'))
const savedCards = computed(() => savedMethods.value.filter(m => m.type === 'card'))

const loadSavedMethods = async () => {
  try {
    const authUser = (usePage().props as any)?.auth?.user
    if (!authUser) return
    const res = await httpClient.get('/payment-methods')
    savedMethods.value = res.data?.data ?? []
    // Prefill otomatis HANYA bila metode utama cocok dengan tab aktif awal,
    // agar tidak memindahkan tab secara mengejutkan.
    const primary = savedMethods.value.find(m => m.is_default)
    const primaryTab = primary?.type === 'bank_account' ? 'bank' : primary?.type === 'e_wallet' ? 'ewallet' : primary ? 'card' : ''
    if (primary && primaryTab === activeTab.value) applySavedMethod(primary, true)
  } catch { /* abaikan — checkout tetap jalan manual */ }
}

const applySavedMethod = (m: SavedMethod, silent = false) => {
  if (m.type === 'bank_account') {
    activeTab.value = 'bank'
    selectedBank.value = m.provider
  } else if (m.type === 'e_wallet') {
    // Provider terpilih otomatis; nomor HP tetap diisi manual
    // karena server hanya mengirim versi masking demi keamanan.
    activeTab.value = 'ewallet'
    selectedWallet.value = m.provider
  } else {
    activeTab.value = 'card'
    cardForm.value.name = m.account_name
    if (m.expiry) cardForm.value.expiry = m.expiry
  }
  if (!silent) {
    const el = document.getElementById(`saved-${m.id}`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  }
}
const processingSteps = [
  'Memverifikasi data pembayaran',
  'Menghubungi payment gateway',
  'Memproses transaksi',
  'Mengkonfirmasi pembayaran',
]

const paymentTabs: Array<{ id: PaymentTabId; label: string; icon: typeof CreditCard }> = [
  { id: 'card', label: 'Kartu', icon: CreditCard },
  { id: 'bank', label: 'Transfer Bank', icon: Landmark },
  { id: 'ewallet', label: 'E-Wallet', icon: Wallet },
]

const bankOptions = [
  { id: 'bca', code: 'BCA', name: 'Bank Central Asia', color: 'bg-blue-500' },
  { id: 'bni', code: 'BNI', name: 'Bank Negara Indonesia', color: 'bg-orange-500' },
  { id: 'bri', code: 'BRI', name: 'Bank Rakyat Indonesia', color: 'bg-blue-700' },
  { id: 'mandiri', code: 'MDR', name: 'Bank Mandiri', color: 'bg-yellow-500' },
]

const ewalletOptions = [
  { id: 'gopay', name: 'GoPay', initials: 'GP', color: 'bg-emerald-500' },
  { id: 'ovo', name: 'OVO', initials: 'OV', color: 'bg-purple-500' },
  { id: 'dana', name: 'DANA', initials: 'DA', color: 'bg-sky-500' },
  { id: 'shopeepay', name: 'ShopeePay', initials: 'SP', color: 'bg-orange-500' },
]

const maskedCardNumber = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (!num) return '•••• •••• •••• ••••'
  const parts = []
  for (let i = 0; i < 16; i += 4) {
    const chunk = num.substring(i, i + 4) || '••••'
    if (i === 4 || i === 8) parts.push('••••')
    else parts.push(chunk.padEnd(4, '•'))
  }
  return parts.join(' ')
})

const cardNetworkLabel = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (num.startsWith('4')) return 'VISA'
  if (num.startsWith('5')) return 'MC'
  if (num.startsWith('3')) return 'AMEX'
  return 'CARD'
})

const cardPreviewClass = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (num.startsWith('4')) return 'bg-gradient-to-br from-blue-500 to-blue-700'
  if (num.startsWith('5')) return 'bg-gradient-to-br from-red-500 to-orange-600'
  if (num.startsWith('3')) return 'bg-gradient-to-br from-green-500 to-teal-600'
  return 'bg-gradient-to-br from-gray-700 to-navy'
})

const virtualAccountNumber = computed(() => {
  if (!selectedBank.value) return ''
  const prefix: Record<string, string> = { bca: '70017', bni: '88908', bri: '26215', mandiri: '89090' }
  return (prefix[selectedBank.value] || '00000') + Math.floor(Math.random() * 9000000000 + 1000000000)
})

const selectedWalletName = computed(() => ewalletOptions.find(w => w.id === selectedWallet.value)?.name || '')

const isFormValid = computed(() => {
  if (activeTab.value === 'card') {
    const num = cardForm.value.number.replace(/\s/g, '')
    return num.length === 16 && cardForm.value.name.length > 2 && cardForm.value.expiry.length === 5 && cardForm.value.cvv.length >= 3
  }
  if (activeTab.value === 'bank') return !!selectedBank.value
  if (activeTab.value === 'ewallet') return !!selectedWallet.value && walletPhone.value.length >= 9
  return false
})

const formatPrice = (price: number) => new Intl.NumberFormat('id-ID').format(Math.round(price))
const formatCardNumber = () => {
  let val = cardForm.value.number.replace(/\D/g, '').substring(0, 16)
  cardForm.value.number = val.replace(/(.{4})/g, '$1 ').trim()
}
const formatExpiry = () => {
  let val = cardForm.value.expiry.replace(/\D/g, '').substring(0, 4)
  if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2)
  cardForm.value.expiry = val
}
const copyVA = () => {
  navigator.clipboard.writeText(virtualAccountNumber.value)
  vaCopied.value = true
  setTimeout(() => { vaCopied.value = false }, 2000)
}

const processPayment = async () => {
  if (!isFormValid.value) return
  errorMessage.value = ''
  currentStep.value = 'processing'
  processingIndex.value = 0
  for (let i = 0; i < processingSteps.length; i++) {
    await delay(700 + Math.random() * 600)
    processingIndex.value = i + 1
  }
  try {
    let bookingId = props.bookingId
    if (!bookingId) {
      const session = sessionStorage.getItem('pendingBooking')
      if (session) {
        const pending = JSON.parse(session)
        bookingId = pending.bookingId
      }
    }
    if (bookingId) {
      const initiateRes = await httpClient.post('/api/payments/initiate', { booking_id: bookingId })
      const token = initiateRes.data?.data?.token
      await httpClient.post('/api/payments/process', { token })
    }
    router.visit('/booking/success', {
      method: 'get',
      data: {
        pnr: props.pnrCode || orderId.value,
        method: activeTab.value,
        total: totalAmount.value,
        flight: props.flightNumber || '',
        passengers: props.passengerCount || 1,
        origin: props.originCode || 'CGK',
        originCity: props.originCity || 'Jakarta',
        destination: props.destinationCode || 'DPS',
        destinationCity: props.destinationCity || 'Denpasar',
      },
    })
  } catch (err: any) {
    currentStep.value = 'form'
    const serverMsg: string = err?.response?.data?.error || err?.response?.data?.message || ''
    errorMessage.value = serverMsg
      ? `Pembayaran gagal: ${serverMsg}`
      : 'Pembayaran gagal diproses. Silakan coba lagi atau hubungi support.'
    console.error('[processPayment] gagal', err?.response?.data || err?.message)
  }
}

const goBack = () => window.history.back()
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms))

onMounted(() => {
  if (!props.totalAmount) {
    const session = sessionStorage.getItem('pendingBooking')
    if (session) {
      const data = JSON.parse(session)
      totalAmount.value = data.totalAmount || 0
    }
  }
  if (props.paymentMethod === 'bank_transfer') activeTab.value = 'bank'
  else if (props.paymentMethod === 'ewallet') activeTab.value = 'ewallet'
  else activeTab.value = 'card'
  loadSavedMethods()
})
</script>
