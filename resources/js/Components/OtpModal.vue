<script setup lang="ts">
import { ref, computed, watch, nextTick, onUnmounted } from 'vue'
import axios from 'axios'

// ─── Props & Emits ────────────────────────────────────────────────────────────
const props = defineProps<{
  show: boolean
  email: string
  sendRoute?: () => string
  verifyRoute?: () => string
  /** Local/dev only — OTP code returned by the server */
  initialDebugCode?: string | null
}>()

const emit = defineEmits<{
  verified: [payload: { token: string | null; email: string }]
  close: []
}>()

// ─── State ────────────────────────────────────────────────────────────────────
const digits     = ref<string[]>(['', '', '', '', '', ''])
const inputRefs  = ref<HTMLInputElement[]>([])
const loading    = ref(false)
const resending  = ref(false)
const error      = ref('')
const success    = ref(false)
const debugCode  = ref<string | null>(null)

// Countdown resend (60 detik)
const countdown     = ref(60)
const canResend     = computed(() => countdown.value === 0)
let countdownTimer: ReturnType<typeof setInterval> | null = null

const code = computed(() => digits.value.join(''))
const isFull = computed(() => digits.value.every(d => d !== ''))

// Masked email display: bu***@gmail.com
const maskedEmail = computed(() => {
  const [local, domain] = props.email.split('@')
  if (!local || !domain) return props.email
  const visible = local.slice(0, 2)
  return `${visible}${'*'.repeat(Math.max(local.length - 2, 3))}@${domain}`
})

// ─── Watchers ─────────────────────────────────────────────────────────────────
watch(() => props.show, (v) => {
  if (v) {
    document.body.style.overflow = 'hidden'
    resetState()
    debugCode.value = props.initialDebugCode ?? null
    startCountdown()
    nextTick(() => inputRefs.value[0]?.focus())
  } else {
    document.body.style.overflow = ''
    clearCountdown()
  }
}, { immediate: true })

watch(() => props.initialDebugCode, (v) => {
  if (props.show && v) debugCode.value = v
})

// Auto-submit when all 6 digits filled
watch(isFull, (v) => {
  if (v && !loading.value && !success.value) {
    verifyOtp()
  }
})

// ─── Countdown ────────────────────────────────────────────────────────────────
function startCountdown() {
  countdown.value = 60
  clearCountdown()
  countdownTimer = setInterval(() => {
    if (countdown.value > 0) countdown.value--
    else clearCountdown()
  }, 1000)
}

function clearCountdown() {
  if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null }
}

onUnmounted(clearCountdown)

// ─── Input handlers ───────────────────────────────────────────────────────────
function onInput(index: number, e: Event) {
  const val = (e.target as HTMLInputElement).value.replace(/\D/g, '').slice(-1)
  digits.value[index] = val
  error.value = ''

  if (val && index < 5) {
    nextTick(() => inputRefs.value[index + 1]?.focus())
  }
}

function onKeydown(index: number, e: KeyboardEvent) {
  if (e.key === 'Backspace') {
    if (digits.value[index]) {
      digits.value[index] = ''
    } else if (index > 0) {
      digits.value[index - 1] = ''
      nextTick(() => inputRefs.value[index - 1]?.focus())
    }
  }
  if (e.key === 'ArrowLeft' && index > 0) {
    nextTick(() => inputRefs.value[index - 1]?.focus())
  }
  if (e.key === 'ArrowRight' && index < 5) {
    nextTick(() => inputRefs.value[index + 1]?.focus())
  }
}

function onPaste(e: ClipboardEvent) {
  e.preventDefault()
  const text = e.clipboardData?.getData('text') ?? ''
  const nums  = text.replace(/\D/g, '').slice(0, 6).split('')
  nums.forEach((n, i) => { digits.value[i] = n })
  // Focus last filled or last box
  const focusIdx = Math.min(nums.length, 5)
  nextTick(() => inputRefs.value[focusIdx]?.focus())
}

// ─── API calls ────────────────────────────────────────────────────────────────
const sendRoute = props.sendRoute ?? (() => route('otp.send'))
  const verifyRoute = props.verifyRoute ?? (() => route('otp.verify'))

  async function verifyOtp() {
    if (!isFull.value || loading.value) return
    loading.value = true
    error.value   = ''

    try {
      const response = await axios.post(verifyRoute(), {
        email: props.email,
        code:  code.value,
      })
      success.value = true
      setTimeout(() => emit('verified', { token: response.data.token ?? null, email: props.email }), 1200)
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Kode tidak valid.'
      digits.value = ['', '', '', '', '', '']
      nextTick(() => inputRefs.value[0]?.focus())
    } finally {
      loading.value = false
    }
  }

  async function resendOtp() {
    if (!canResend.value || resending.value) return
    resending.value = true
    error.value     = ''

    try {
      const response = await axios.post(sendRoute(), { email: props.email })
      digits.value = ['', '', '', '', '', '']
      debugCode.value = response.data?.debug_code ?? null
      startCountdown()
      nextTick(() => inputRefs.value[0]?.focus())
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim ulang kode.'
    } finally {
      resending.value = false
    }
  }

function resetState() {
  digits.value  = ['', '', '', '', '', '']
  error.value   = ''
  success.value = false
  loading.value = false
}

function close() {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-[110] flex items-center justify-center px-4"
        role="dialog"
        aria-modal="true"
        aria-label="Verifikasi Email"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/65 backdrop-blur-sm" />

        <!-- Card -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
        >
          <div
            v-if="show"
            class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden"
            @click.stop
          >
            <!-- Close button -->
            <button
              type="button"
              class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-all z-10"
              aria-label="Tutup"
              @click="close"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>

            <!-- ── SUCCESS STATE ──────────────────────────────────────── -->
            <Transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 scale-90"
              enter-to-class="opacity-100 scale-100"
            >
              <div v-if="success" class="flex flex-col items-center justify-center py-14 px-8 text-center">
                <!-- Animated checkmark -->
                <div class="w-20 h-20 rounded-full bg-teal/10 flex items-center justify-center mb-5">
                  <div class="w-14 h-14 rounded-full bg-teal flex items-center justify-center shadow-lg shadow-teal/30 animate-bounce">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                </div>
                <h2 class="text-xl font-black text-gray-900 mb-2">Email Terverifikasi!</h2>
                <p class="text-sm text-gray-500">Akun Anda sudah aktif. Mengalihkan…</p>
              </div>
            </Transition>

            <!-- ── INPUT STATE ───────────────────────────────────────── -->
            <div v-if="!success" class="px-7 py-8">

              <!-- Icon + title -->
              <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal to-teal-dark flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal/25">
                  <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <h2 class="text-xl font-black text-gray-900">Cek email kamu</h2>
                <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">
                  Kami kirim kode 6 digit ke<br/>
                  <span class="font-semibold text-gray-700">{{ maskedEmail }}</span>
                </p>
              </div>

              <!-- 6-digit OTP input boxes -->
              <div
                class="flex gap-2 justify-center mb-5"
                role="group"
                aria-label="Masukkan kode OTP 6 digit"
                @paste="onPaste"
              >
                <input
                  v-for="(_, i) in digits"
                  :key="i"
                  :ref="el => { if (el) inputRefs[i] = el as HTMLInputElement }"
                  v-model="digits[i]"
                  type="text"
                  inputmode="numeric"
                  maxlength="1"
                  :aria-label="`Digit ${i + 1}`"
                  :class="[
                    'w-11 h-13 text-center text-xl font-black rounded-xl border-2 transition-all duration-150 outline-none select-none',
                    // success state
                    success
                      ? 'border-teal bg-teal/5 text-teal'
                      : error
                        // error state
                        ? 'border-red-400 bg-red-50 text-red-600 animate-shake'
                        : digits[i]
                          // filled state
                          ? 'border-teal bg-teal/5 text-teal shadow-sm shadow-teal/20'
                          // empty state
                          : 'border-gray-200 bg-gray-50 text-gray-900 focus:border-teal focus:bg-white focus:shadow-sm focus:shadow-teal/20',
                  ]"
                  style="height: 52px;"
                  @input="onInput(i, $event)"
                  @keydown="onKeydown(i, $event)"
                />
              </div>

              <!-- Error message -->
              <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
              >
                <div v-if="error" class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-3.5 py-2.5 mb-4">
                  <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  <p class="text-xs text-red-600 font-medium">{{ error }}</p>
                </div>
              </Transition>

              <!-- Verify button (manual, jika tidak auto-submit) -->
              <button
                type="button"
                :disabled="!isFull || loading"
                :class="[
                  'w-full flex items-center justify-center gap-2 font-bold text-sm py-3 rounded-xl transition-all',
                  isFull && !loading
                    ? 'bg-teal hover:bg-teal-dark text-white shadow-lg shadow-teal/25 hover:-translate-y-px'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                ]"
                @click="verifyOtp"
              >
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ loading ? 'Memverifikasi...' : 'Verifikasi Sekarang' }}
              </button>

              <!-- Resend section -->
              <div class="mt-5 text-center">
                <p class="text-xs text-gray-400">
                  Tidak menerima kode?
                </p>
                <div class="mt-1.5">
                  <!-- Countdown -->
                  <span v-if="!canResend" class="text-xs text-gray-500">
                    Kirim ulang dalam
                    <span class="font-bold text-teal tabular-nums">{{ countdown }}s</span>
                  </span>
                  <!-- Resend button -->
                  <button
                    v-else
                    type="button"
                    :disabled="resending"
                    class="text-xs font-bold text-teal hover:underline disabled:opacity-50 transition-all"
                    @click="resendOtp"
                  >
                    <span v-if="resending" class="flex items-center gap-1 justify-center">
                      <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                      </svg>
                      Mengirim...
                    </span>
                    <span v-else>Kirim ulang kode →</span>
                  </button>
                </div>
              </div>

              <!-- Local/dev OTP hint -->
              <p
                v-if="debugCode"
                class="mt-4 text-center text-xs font-mono text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2"
              >
                Kode (dev): <span class="font-bold tracking-widest">{{ debugCode }}</span>
              </p>

              <!-- Info -->
              <p class="text-center text-[11px] text-gray-300 mt-4">
                Kode berlaku selama 10 menit
              </p>

            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%       { transform: translateX(-6px); }
  40%       { transform: translateX(6px); }
  60%       { transform: translateX(-4px); }
  80%       { transform: translateX(4px); }
}
.animate-shake { animation: shake 0.4s ease-in-out; }
</style>
