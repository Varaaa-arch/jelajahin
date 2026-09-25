import { ref, computed, watch, nextTick, onUnmounted } from 'vue'
import { otpAPI } from '../api/otp'

export function useOtp(email: string, onVerified?: () => void) {
  const digits    = ref<string[]>(['', '', '', '', '', ''])
  const inputRefs = ref<HTMLInputElement[]>([])
  const loading   = ref(false)
  const resending = ref(false)
  const error     = ref('')
  const success   = ref(false)

  const countdown     = ref(60)
  const canResend     = computed(() => countdown.value === 0)
  let countdownTimer: ReturnType<typeof setInterval> | null = null

  const code = computed(() => digits.value.join(''))
  const isFull = computed(() => digits.value.every(d => d !== ''))

  const maskedEmail = computed(() => {
    const [local, domain] = email.split('@')
    if (!local || !domain) return email
    const visible = local.slice(0, 2)
    return `${visible}${'*'.repeat(Math.max(local.length - 2, 3))}@${domain}`
  })

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
    const nums = text.replace(/\D/g, '').slice(0, 6).split('')
    nums.forEach((n, i) => { digits.value[i] = n })
    const focusIdx = Math.min(nums.length, 5)
    nextTick(() => inputRefs.value[focusIdx]?.focus())
  }

  async function verifyOtp() {
    if (!isFull.value || loading.value) return
    loading.value = true
    error.value = ''

    try {
      await otpAPI.verify(email, code.value)
      success.value = true
      if (onVerified) setTimeout(() => onVerified(), 1200)
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
    error.value = ''

    try {
      await otpAPI.resend(email)
      digits.value = ['', '', '', '', '', '']
      startCountdown()
      nextTick(() => inputRefs.value[0]?.focus())
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim ulang kode.'
    } finally {
      resending.value = false
    }
  }

  function resetState() {
    digits.value = ['', '', '', '', '', '']
    error.value = ''
    success.value = false
    loading.value = false
  }

  return {
    digits,
    inputRefs,
    loading,
    resending,
    error,
    success,
    countdown,
    canResend,
    code,
    isFull,
    maskedEmail,
    onInput,
    onKeydown,
    onPaste,
    verifyOtp,
    resendOtp,
    resetState,
    startCountdown,
    clearCountdown,
    onUnmounted,
  }
}
