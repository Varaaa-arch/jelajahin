<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-2xl mx-auto">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Secure Payment</h1>
        <p class="text-gray-500 mt-1">🔒 Your payment is protected by 256-bit SSL encryption</p>
      </div>

      <!-- Order Summary Card -->
      <div class="bg-white rounded-2xl shadow-md p-5 mb-6">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-500">Order ID</p>
            <p class="font-mono font-bold text-gray-800">{{ orderId }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Total Pembayaran</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ formatPrice(totalAmount) }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm text-gray-600">
          <span>✈ Penerbangan {{ flightNumber }}</span>
          <span>{{ passengerCount }} Penumpang</span>
        </div>
      </div>

      <!-- Payment Method Tabs -->
      <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-6" v-if="currentStep === 'form'">
        <!-- Tab Headers -->
        <div class="flex border-b border-gray-100">
          <button
            v-for="tab in paymentTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex-1 py-4 text-sm font-medium transition-all',
              activeTab === tab.id
                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50'
                : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            <span class="mr-1">{{ tab.icon }}</span> {{ tab.label }}
          </button>
        </div>

        <!-- Credit / Debit Card Form -->
        <div v-if="activeTab === 'card'" class="p-6">
          <div class="mb-6">
            <!-- Card Preview -->
            <div class="relative h-44 rounded-xl p-5 text-white mb-6 overflow-hidden" :class="cardPreviewClass">
              <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-white to-transparent pointer-events-none"></div>
              <div class="flex justify-between items-start mb-6">
                <span class="text-lg font-bold tracking-widest">JELAJAHIN</span>
                <span class="text-2xl">{{ cardNetworkIcon }}</span>
              </div>
              <p class="font-mono text-xl tracking-widest mb-4">
                {{ maskedCardNumber }}
              </p>
              <div class="flex justify-between text-sm">
                <div>
                  <p class="opacity-70 text-xs mb-1">CARDHOLDER</p>
                  <p class="uppercase font-medium">{{ cardForm.name || 'YOUR NAME' }}</p>
                </div>
                <div class="text-right">
                  <p class="opacity-70 text-xs mb-1">EXPIRES</p>
                  <p>{{ cardForm.expiry || 'MM/YY' }}</p>
                </div>
              </div>
            </div>

            <!-- Accepted Cards -->
            <div class="flex gap-2 mb-4">
              <span v-for="card in ['VISA', 'MC', 'AMEX']" :key="card"
                class="px-2 py-1 border border-gray-200 rounded text-xs text-gray-500 font-medium">
                {{ card }}
              </span>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kartu</label>
              <input
                v-model="cardForm.number"
                @input="formatCardNumber"
                maxlength="19"
                placeholder="0000 0000 0000 0000"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-lg tracking-widest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemegang Kartu</label>
              <input
                v-model="cardForm.name"
                placeholder="Nama sesuai kartu"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Masa Berlaku</label>
                <input
                  v-model="cardForm.expiry"
                  @input="formatExpiry"
                  maxlength="5"
                  placeholder="MM/YY"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                <input
                  v-model="cardForm.cvv"
                  type="password"
                  maxlength="4"
                  placeholder="•••"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Bank Transfer -->
        <div v-else-if="activeTab === 'bank'" class="p-6">
          <p class="text-sm text-gray-500 mb-4">Pilih bank untuk transfer:</p>
          <div class="space-y-3">
            <label
              v-for="bank in bankOptions"
              :key="bank.id"
              :class="[
                'flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
                selectedBank === bank.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <input type="radio" :value="bank.id" v-model="selectedBank" class="sr-only" />
              <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-white text-sm mr-3" :class="bank.color">
                {{ bank.code }}
              </div>
              <div class="flex-1">
                <p class="font-semibold text-gray-800">{{ bank.name }}</p>
                <p class="text-sm text-gray-500">Transfer ke rekening virtual</p>
              </div>
              <div v-if="selectedBank === bank.id" class="text-blue-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
            </label>
          </div>

          <!-- Virtual Account Preview -->
          <div v-if="selectedBank" class="mt-4 bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Nomor Virtual Account</p>
            <div class="flex items-center justify-between">
              <p class="font-mono text-xl font-bold text-gray-800 tracking-wider">{{ virtualAccountNumber }}</p>
              <button @click="copyVA" class="text-blue-600 text-sm font-medium hover:text-blue-700">
                {{ vaCopied ? '✓ Copied!' : 'Copy' }}
              </button>
            </div>
            <p class="text-xs text-orange-500 mt-2">⏱ Bayar dalam 24 jam sebelum kedaluwarsa</p>
          </div>
        </div>

        <!-- E-Wallet -->
        <div v-else-if="activeTab === 'ewallet'" class="p-6">
          <p class="text-sm text-gray-500 mb-4">Pilih e-wallet kamu:</p>
          <div class="grid grid-cols-2 gap-3">
            <label
              v-for="wallet in ewalletOptions"
              :key="wallet.id"
              :class="[
                'flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
                selectedWallet === wallet.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <input type="radio" :value="wallet.id" v-model="selectedWallet" class="sr-only" />
              <span class="text-3xl mb-2">{{ wallet.icon }}</span>
              <span class="font-semibold text-sm text-gray-700">{{ wallet.name }}</span>
              <span v-if="selectedWallet === wallet.id" class="mt-1 text-blue-500 text-xs">✓ Dipilih</span>
            </label>
          </div>

          <!-- Phone Number Input -->
          <div v-if="selectedWallet" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / Akun {{ selectedWalletName }}</label>
            <div class="flex">
              <span class="px-3 py-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">+62</span>
              <input
                v-model="walletPhone"
                type="tel"
                placeholder="8xx xxxx xxxx"
                class="flex-1 border border-gray-300 rounded-r-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Processing Screen -->
      <div v-if="currentStep === 'processing'" class="bg-white rounded-2xl shadow-md p-10 text-center">
        <div class="relative inline-flex mb-6">
          <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
          </div>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Memproses Pembayaran...</h2>
        <p class="text-gray-500 text-sm mb-6">Mohon tunggu, jangan tutup halaman ini</p>
        
        <!-- Progress Steps -->
        <div class="text-left space-y-3 max-w-xs mx-auto">
          <div v-for="(step, idx) in processingSteps" :key="idx" class="flex items-center gap-3">
            <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0', 
              processingIndex > idx ? 'bg-green-500 text-white' : 
              processingIndex === idx ? 'bg-blue-500 text-white animate-pulse' : 
              'bg-gray-200 text-gray-400']">
              {{ processingIndex > idx ? '✓' : idx + 1 }}
            </div>
            <span :class="['text-sm', processingIndex >= idx ? 'text-gray-800 font-medium' : 'text-gray-400']">
              {{ step }}
            </span>
          </div>
        </div>
      </div>

      <!-- Validation Error -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 flex items-start gap-3">
        <span class="text-red-500 text-xl flex-shrink-0">⚠</span>
        <p class="text-sm text-red-700">{{ errorMessage }}</p>
      </div>

      <!-- Pay Button -->
      <div v-if="currentStep === 'form'" class="flex gap-4">
        <button
          @click="goBack"
          class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
        >
          ← Kembali
        </button>
        <button
          @click="processPayment"
          :disabled="!isFormValid"
          :class="[
            'flex-1 py-3 rounded-xl font-semibold text-white transition-all text-center',
            isFormValid
              ? 'bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl'
              : 'bg-gray-300 cursor-not-allowed'
          ]"
        >
          💳 Bayar Rp {{ formatPrice(totalAmount) }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { httpClient } from '@/utils/http'

const props = defineProps<{
  bookingId?: string
  pnrCode?: string
  totalAmount?: number
  flightNumber?: string
  passengerCount?: number
  paymentMethod?: string  // pre-selected method dari Create.vue
}>()

// ─── State ─────────────────────────────────────────────────────────────────
const currentStep = ref<'form' | 'processing'>('form')
const errorMessage = ref('')
const orderId = ref(props.pnrCode || 'JLJ-' + Math.random().toString(36).substring(2, 8).toUpperCase())
const totalAmount = ref(props.totalAmount || 0)

// Tab aktif sesuai method yang dipilih sebelumnya
const activeTab = ref<'card' | 'bank' | 'ewallet'>(() => {
  if (props.paymentMethod === 'bank_transfer') return 'bank'
  if (props.paymentMethod === 'ewallet') return 'ewallet'
  return 'card'
})

// Credit/Debit Card
const cardForm = ref({ number: '', name: '', expiry: '', cvv: '' })

// Bank Transfer
const selectedBank = ref('')
const vaCopied = ref(false)

// E-Wallet
const selectedWallet = ref('')
const walletPhone = ref('')

// Processing
const processingIndex = ref(0)
const processingSteps = [
  'Memverifikasi data pembayaran',
  'Menghubungi payment gateway',
  'Memproses transaksi',
  'Mengkonfirmasi pembayaran',
]

// ─── Options ────────────────────────────────────────────────────────────────
const paymentTabs = [
  { id: 'card', label: 'Kartu', icon: '💳' },
  { id: 'bank', label: 'Transfer Bank', icon: '🏦' },
  { id: 'ewallet', label: 'E-Wallet', icon: '📱' },
]

const bankOptions = [
  { id: 'bca', code: 'BCA', name: 'Bank Central Asia', color: 'bg-blue-500' },
  { id: 'bni', code: 'BNI', name: 'Bank Negara Indonesia', color: 'bg-orange-500' },
  { id: 'bri', code: 'BRI', name: 'Bank Rakyat Indonesia', color: 'bg-blue-700' },
  { id: 'mandiri', code: 'MDR', name: 'Bank Mandiri', color: 'bg-yellow-500' },
]

const ewalletOptions = [
  { id: 'gopay', name: 'GoPay', icon: '🟢' },
  { id: 'ovo', name: 'OVO', icon: '🟣' },
  { id: 'dana', name: 'DANA', icon: '🔵' },
  { id: 'shopeepay', name: 'ShopeePay', icon: '🔴' },
]

// ─── Computed ────────────────────────────────────────────────────────────────
const maskedCardNumber = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (!num) return '•••• •••• •••• ••••'
  const parts = []
  for (let i = 0; i < 16; i += 4) {
    const chunk = num.substring(i, i + 4) || '••••'
    // mask middle digits
    if (i === 4 || i === 8) {
      parts.push('••••')
    } else {
      parts.push(chunk.padEnd(4, '•'))
    }
  }
  return parts.join(' ')
})

const cardNetworkIcon = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (num.startsWith('4')) return '💳' // Visa
  if (num.startsWith('5')) return '🔴' // Mastercard
  if (num.startsWith('3')) return '🟦' // Amex
  return '💳'
})

const cardPreviewClass = computed(() => {
  const num = cardForm.value.number.replace(/\s/g, '')
  if (num.startsWith('4')) return 'bg-gradient-to-br from-blue-500 to-blue-700'
  if (num.startsWith('5')) return 'bg-gradient-to-br from-red-500 to-orange-600'
  if (num.startsWith('3')) return 'bg-gradient-to-br from-green-500 to-teal-600'
  return 'bg-gradient-to-br from-gray-600 to-gray-800'
})

const virtualAccountNumber = computed(() => {
  if (!selectedBank.value) return ''
  const prefix: Record<string, string> = { bca: '70017', bni: '88908', bri: '26215', mandiri: '89090' }
  return (prefix[selectedBank.value] || '00000') + Math.floor(Math.random() * 9000000000 + 1000000000)
})

const selectedWalletName = computed(() => {
  return ewalletOptions.find(w => w.id === selectedWallet.value)?.name || ''
})

const isFormValid = computed(() => {
  if (activeTab.value === 'card') {
    const num = cardForm.value.number.replace(/\s/g, '')
    return num.length === 16 && cardForm.value.name.length > 2 &&
           cardForm.value.expiry.length === 5 && cardForm.value.cvv.length >= 3
  }
  if (activeTab.value === 'bank') return !!selectedBank.value
  if (activeTab.value === 'ewallet') return !!selectedWallet.value && walletPhone.value.length >= 9
  return false
})

// ─── Formatters ─────────────────────────────────────────────────────────────
const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(Math.round(price))
}

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

// ─── Actions ─────────────────────────────────────────────────────────────────
const processPayment = async () => {
  if (!isFormValid.value) return
  errorMessage.value = ''
  currentStep.value = 'processing'
  processingIndex.value = 0

  // Simulate step-by-step processing (fake)
  for (let i = 0; i < processingSteps.length; i++) {
    await delay(700 + Math.random() * 600)
    processingIndex.value = i + 1
  }

  // Simulate API call
  try {
    let bookingId = props.bookingId

    // Jika belum ada bookingId (dipanggil langsung), buat dari sessionStorage
    if (!bookingId) {
      const session = sessionStorage.getItem('pendingBooking')
      if (session) {
        const pending = JSON.parse(session)
        bookingId = pending.bookingId
      }
    }

    if (bookingId) {
      // Initiate payment
      const initiateRes = await httpClient.post('/api/payments/initiate', {
        booking_id: bookingId,
      })
      const token = initiateRes.data?.data?.token

      // Process payment
      await httpClient.post('/api/payments/process', { token })
    }

    // Redirect ke Success page
    router.visit('/booking/success', {
      method: 'get',
      data: {
        pnr: props.pnrCode || orderId.value,
        method: activeTab.value,
      },
    })

  } catch (err: any) {
    currentStep.value = 'form'
    errorMessage.value = 'Pembayaran gagal diproses. Silakan coba lagi atau hubungi support.'
  }
}

const goBack = () => {
  window.history.back()
}

const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms))

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(() => {
  // Load data dari sessionStorage jika tidak ada props
  if (!props.totalAmount) {
    const session = sessionStorage.getItem('pendingBooking')
    if (session) {
      const data = JSON.parse(session)
      totalAmount.value = data.totalAmount || 0
    }
  }

  // Pre-select tab dari method yang dipilih di Create.vue
  if (props.paymentMethod === 'bank_transfer') activeTab.value = 'bank'
  else if (props.paymentMethod === 'ewallet') activeTab.value = 'ewallet'
  else activeTab.value = 'card'
})
</script>
