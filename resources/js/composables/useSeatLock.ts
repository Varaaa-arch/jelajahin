import { ref, computed, onUnmounted } from 'vue'
import { httpClient } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import type { FlightSeat } from '@/types/flight'

const LOCK_TTL_SECONDS = 900 // 15 menit, sesuai Go service DefaultLockTTL

export interface LockedSeat {
  seatNumber: string   // e.g. "1A"
  seatId: string       // UUID dari FlightSeat
  lockToken?: string
}

export function useSeatLock(flightId: string) {
  // ─── State ────────────────────────────────────────────────────────────────
  const seats = ref<FlightSeat[]>([])
  const seatsLoading = ref(false)
  const seatsError = ref<string | null>(null)

  const selectedSeats = ref<LockedSeat[]>([])
  const loadingSeats = ref<Set<string>>(new Set())    // seatNumber yang sedang diproses
  const lockError = ref<string | null>(null)

  const lockTimeRemaining = ref(LOCK_TTL_SECONDS)
  const lockStartTime = ref<number | null>(null)
  const skipAutoUnlock = ref(false)
  let countdownInterval: ReturnType<typeof setInterval> | null = null

  // ─── Helpers ──────────────────────────────────────────────────────────────

  /**
   * Ambil user_id dari Inertia shared props (Laravel auth) atau fallback ke
   * localStorage (jika sudah disimpan saat login).
   */
  const getUserId = (): string => {
    try {
      const page = usePage()
      const authUser = (page.props as any)?.auth?.user
      if (authUser?.id) return String(authUser.id)
    } catch {
      // usePage() bisa gagal di luar komponen Inertia — gunakan fallback
    }
    return localStorage.getItem('user_id') || 'guest-' + Math.random().toString(36).substring(2, 9)
  }

  // ─── Load seats dari Go API ────────────────────────────────────────────────

  const loadSeats = async () => {
    if (!flightId) return
    seatsLoading.value = true
    seatsError.value = null
    try {
      const res = await httpClient.get(`/api/v1/flights/${flightId}/seats`)
      seats.value = res.data.data ?? res.data ?? []
    } catch (err: any) {
      seatsError.value = err?.response?.data?.message || 'Gagal memuat data kursi'
    } finally {
      seatsLoading.value = false
    }
  }

  // ─── Lock / unlock single seat ────────────────────────────────────────────

  const lockSeat = async (seatId: string, seatNumber: string): Promise<boolean> => {
    loadingSeats.value = new Set([...loadingSeats.value, seatNumber])
    lockError.value = null

    try {
      const res = await httpClient.post('/api/v1/seats/lock', {
        flight_id: flightId,
        seat_id: seatId,
        user_id: getUserId(),
      })

      if (res.data.success) {
        selectedSeats.value.push({
          seatNumber,
          seatId,
          lockToken: res.data.lock_token,
        })

        // Mulai countdown saat kursi pertama berhasil dikunci
        if (lockStartTime.value === null) {
          lockStartTime.value = Date.now()
          lockTimeRemaining.value = LOCK_TTL_SECONDS
          startCountdown()
        }
        return true
      }
      return false
    } catch (err: any) {
      const status = err?.response?.status
      const data = err?.response?.data

      if (status === 409) {
        lockError.value = `Kursi ${seatNumber} sudah dikunci penumpang lain`
      } else {
        lockError.value = data?.message || `Gagal mengunci kursi ${seatNumber}`
      }
      return false
    } finally {
      const next = new Set(loadingSeats.value)
      next.delete(seatNumber)
      loadingSeats.value = next
    }
  }

  const unlockSeat = async (seatId: string, seatNumber: string): Promise<void> => {
    try {
      await httpClient.post('/api/v1/seats/unlock', {
        flight_id: flightId,
        seat_id: seatId,
        user_id: getUserId(),
      })
    } catch (err: any) {
      // 404 berarti lock sudah expired, tidak perlu error
      if (err?.response?.status !== 404) {
        console.error(`[useSeatLock] unlock error for ${seatNumber}:`, err?.message)
      }
    }
  }

  // ─── Toggle seat (select → lock, deselect → unlock) ───────────────────────

  const toggleSeat = async (seatId: string, seatNumber: string): Promise<void> => {
    const idx = selectedSeats.value.findIndex(s => s.seatNumber === seatNumber)

    if (idx !== -1) {
      // Kursi sudah dipilih → deselect dan unlock
      selectedSeats.value.splice(idx, 1)
      await unlockSeat(seatId, seatNumber)

      if (selectedSeats.value.length === 0) {
        stopCountdown()
      }
    } else {
      // Kursi belum dipilih → lock
      await lockSeat(seatId, seatNumber)
    }
  }

  // ─── Lock multiple seats sekaligus ────────────────────────────────────────

  const lockMultipleSeats = async (
    seatList: Array<{ seatId: string; seatNumber: string }>
  ): Promise<boolean> => {
    lockError.value = null
    try {
      const res = await httpClient.post('/api/v1/seats/lock-multiple', {
        flight_id: flightId,
        seat_ids: seatList.map(s => s.seatId),
        user_id: getUserId(),
      })

      if (res.data.success) {
        selectedSeats.value = seatList.map(s => ({
          seatNumber: s.seatNumber,
          seatId: s.seatId,
        }))

        lockStartTime.value = Date.now()
        lockTimeRemaining.value = LOCK_TTL_SECONDS
        startCountdown()
        return true
      }

      const failed: string[] = res.data.failed_seats ?? []
      lockError.value = `Beberapa kursi sudah dikunci: ${failed.join(', ')}`
      return false
    } catch (err: any) {
      const failed: string[] = err?.response?.data?.failed_seats ?? []
      lockError.value = failed.length
        ? `Kursi sudah dikunci oleh penumpang lain: ${failed.join(', ')}`
        : err?.response?.data?.message || 'Gagal mengunci kursi'
      return false
    }
  }

  // ─── Unlock semua seat yang dipilih ──────────────────────────────────────

  const unlockAll = async (): Promise<void> => {
    const toUnlock = [...selectedSeats.value]
    // Bersihkan state lebih dulu agar UI langsung update
    selectedSeats.value = []
    stopCountdown()

    await Promise.allSettled(
      toUnlock.map(s => unlockSeat(s.seatId, s.seatNumber))
    )
  }

  // ─── Check lock status single seat dari server ────────────────────────────

  const checkSeatLock = async (
    seatId: string
  ): Promise<{ isLocked: boolean; lockedBy?: string; ttlSeconds?: number }> => {
    try {
      const res = await httpClient.get(
        `/api/v1/seats/${flightId}/${seatId}/lock-status`
      )
      return {
        isLocked: res.data.is_locked,
        lockedBy: res.data.locked_by,
        ttlSeconds: res.data.ttl_seconds,
      }
    } catch {
      return { isLocked: false }
    }
  }

  // ─── Countdown timer ─────────────────────────────────────────────────────

  const startCountdown = () => {
    if (countdownInterval) return
    countdownInterval = setInterval(() => {
      lockTimeRemaining.value--
      if (lockTimeRemaining.value <= 0) {
        stopCountdown()
        // Lock sudah expired di server — reset state lokal
        selectedSeats.value = []
        lockStartTime.value = null
        lockError.value = 'Waktu penguncian kursi habis. Silakan pilih kursi kembali.'
      }
    }, 1000)
  }

  const stopCountdown = () => {
    if (countdownInterval) {
      clearInterval(countdownInterval)
      countdownInterval = null
    }
    lockTimeRemaining.value = LOCK_TTL_SECONDS
    lockStartTime.value = null
  }

  // ─── Computed helpers ─────────────────────────────────────────────────────

  const lockPercentage = computed(() =>
    (lockTimeRemaining.value / LOCK_TTL_SECONDS) * 100
  )

  const lockTimeLabel = computed(() => {
    const m = Math.floor(lockTimeRemaining.value / 60)
    const s = lockTimeRemaining.value % 60
    return `${m}:${s.toString().padStart(2, '0')}`
  })

  const isLockExpiringSoon = computed(() => lockTimeRemaining.value < 120)

  const isSeatSelected = (seatNumber: string) =>
    selectedSeats.value.some(s => s.seatNumber === seatNumber)

  const isSeatLoading = (seatNumber: string) =>
    loadingSeats.value.has(seatNumber)

  // ─── Cleanup otomatis saat component di-unmount ──────────────────────────

  onUnmounted(() => {
    stopCountdown()
    if (skipAutoUnlock.value) return
    const toUnlock = [...selectedSeats.value]
    toUnlock.forEach(s => {
      httpClient
        .post('/api/v1/seats/unlock', { flight_id: flightId, seat_id: s.seatId, user_id: getUserId() })
        .catch(() => {})
    })
  })

  return {
    // Data kursi dari API
    seats,
    seatsLoading,
    seatsError,
    loadSeats,

    // Seat selection
    selectedSeats,
    loadingSeats,
    lockError,
    toggleSeat,
    lockMultipleSeats,
    unlockAll,
    checkSeatLock,

    // Status helpers
    isSeatSelected,
    isSeatLoading,

    // Timer
    lockTimeRemaining,
    lockPercentage,
    lockTimeLabel,
    isLockExpiringSoon,
    skipAutoUnlock,
  }
}
