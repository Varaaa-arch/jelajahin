<template>
  <div class="bg-white rounded-2xl shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-2">Pilih Kursi</h2>

    <!-- Error pemuatan kursi -->
    <div v-if="seatsError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm flex items-center gap-2">
      <span>⚠</span> {{ seatsError }}
      <button @click="loadSeats" class="ml-auto text-red-700 font-semibold hover:underline">Coba lagi</button>
    </div>

    <!-- Error lock -->
    <div v-if="lockError" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 text-sm flex items-center gap-2">
      <span>🔒</span> {{ lockError }}
    </div>

    <!-- Loading skeleton -->
    <div v-if="seatsLoading" class="bg-gray-50 p-6 rounded-lg animate-pulse">
      <div v-for="r in 6" :key="r" class="flex gap-4 mb-4">
        <div class="w-8 h-10 bg-gray-200 rounded"></div>
        <div class="flex gap-2">
          <div v-for="c in 6" :key="c" class="w-10 h-10 bg-gray-200 rounded"></div>
        </div>
        <div class="w-4"></div>
        <div class="flex gap-2">
          <div v-for="c in 6" :key="c" class="w-10 h-10 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Seat grid dari data API -->
    <template v-else-if="seatRows.length > 0">
      <div class="bg-gray-50 p-4 rounded-xl overflow-x-auto">
        <!-- Column header -->
        <div class="flex gap-4 mb-2 ml-8 text-xs font-bold text-gray-400 select-none">
          <div class="flex gap-2">
            <div v-for="col in leftCols" :key="col" class="w-10 text-center">{{ col }}</div>
          </div>
          <div class="w-4"></div>
          <div class="flex gap-2">
            <div v-for="col in rightCols" :key="col" class="w-10 text-center">{{ col }}</div>
          </div>
        </div>

        <div
          v-for="row in seatRows"
          :key="row.rowNumber"
          class="flex gap-4 mb-2 items-center"
        >
          <!-- Row number -->
          <div class="w-8 text-center text-sm font-semibold text-gray-500 shrink-0">
            {{ row.rowNumber }}
          </div>

          <!-- Left seats -->
          <div class="flex gap-2">
            <button
              v-for="seat in row.leftSeats"
              :key="seat.id"
              @click="handleSeatClick(seat)"
              :disabled="!seat.is_available || isSeatLoading(seat.seatNumber)"
              :class="getSeatClass(seat)"
              class="w-10 h-10 rounded-lg font-semibold text-xs transition-all duration-150 flex items-center justify-center select-none"
              :title="getSeatTooltip(seat)"
            >
              <span v-if="isSeatLoading(seat.seatNumber)" class="animate-spin text-base">⟳</span>
              <span v-else>{{ seat.columnLetter }}</span>
            </button>
          </div>

          <!-- Aisle -->
          <div class="w-4 shrink-0"></div>

          <!-- Right seats -->
          <div class="flex gap-2">
            <button
              v-for="seat in row.rightSeats"
              :key="seat.id"
              @click="handleSeatClick(seat)"
              :disabled="!seat.is_available || isSeatLoading(seat.seatNumber)"
              :class="getSeatClass(seat)"
              class="w-10 h-10 rounded-lg font-semibold text-xs transition-all duration-150 flex items-center justify-center select-none"
              :title="getSeatTooltip(seat)"
            >
              <span v-if="isSeatLoading(seat.seatNumber)" class="animate-spin text-base">⟳</span>
              <span v-else>{{ seat.columnLetter }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="mt-4 flex flex-wrap gap-6 text-sm">
        <div class="flex items-center gap-2"><div class="w-5 h-5 bg-emerald-500 rounded"></div> Tersedia</div>
        <div class="flex items-center gap-2"><div class="w-5 h-5 bg-blue-500 rounded"></div> Terpilih</div>
        <div class="flex items-center gap-2"><div class="w-5 h-5 bg-red-400 rounded"></div> Tidak tersedia</div>
        <div class="flex items-center gap-2"><div class="w-5 h-5 bg-amber-400 rounded"></div> Exit row</div>
      </div>

      <!-- Selected seats summary -->
      <div v-if="selectedSeats.length > 0" class="mt-5 p-4 bg-blue-50 border border-blue-100 rounded-xl">
        <p class="font-semibold text-blue-800 mb-2">Kursi Terpilih:</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="seat in selectedSeats"
            :key="seat.seatId"
            class="inline-flex items-center gap-1 bg-blue-500 text-white px-3 py-1 rounded-lg text-sm"
          >
            {{ seat.seatNumber }}
            <button
              @click="removeSeat(seat)"
              class="ml-1 font-bold leading-none hover:text-blue-200"
              aria-label="Hapus kursi"
            >×</button>
          </span>
        </div>
      </div>

      <!-- Lock countdown -->
      <div
        v-if="selectedSeats.length > 0"
        :class="['mt-4 p-4 rounded-xl border transition-colors', isLockExpiringSoon ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200']"
      >
        <div class="flex items-center justify-between mb-2">
          <p :class="['text-sm font-medium', isLockExpiringSoon ? 'text-red-700' : 'text-yellow-800']">
            {{ isLockExpiringSoon ? '⚠ Segera selesaikan pemilihan!' : '⏱ Kursi terkunci untuk:' }}
            <span class="font-bold ml-1">{{ lockTimeLabel }}</span>
          </p>
          <span class="text-xs text-gray-400">{{ selectedSeats.length }} kursi terkunci</span>
        </div>
        <div :class="['w-full h-2 rounded-full', isLockExpiringSoon ? 'bg-red-200' : 'bg-yellow-200']">
          <div
            :class="['h-2 rounded-full transition-all duration-1000', isLockExpiringSoon ? 'bg-red-500' : 'bg-yellow-500']"
            :style="{ width: lockPercentage + '%' }"
          ></div>
        </div>
      </div>
    </template>

    <!-- Fallback: tidak ada data kursi -->
    <div v-else-if="!seatsLoading" class="text-center py-10 text-gray-400">
      <p class="text-4xl mb-3">✈️</p>
      <p>Data kursi tidak tersedia untuk penerbangan ini.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSeatLock, type LockedSeat } from '@/composables/useSeatLock'
import type { FlightSeat } from '@/types/flight'

// ─── Props & Emits ────────────────────────────────────────────────────────────
const props = defineProps<{
  flightId: string
}>()

const emit = defineEmits<{
  'seats-selected': [seats: LockedSeat[]]
}>()

// ─── Composable ───────────────────────────────────────────────────────────────
const {
  seats,
  seatsLoading,
  seatsError,
  loadSeats,
  selectedSeats,
  lockError,
  toggleSeat,
  isSeatSelected,
  isSeatLoading,
  lockTimeRemaining,
  lockPercentage,
  lockTimeLabel,
  isLockExpiringSoon,
} = useSeatLock(props.flightId)

// ─── Seat structure helpers ───────────────────────────────────────────────────

/** Flatten seat dengan computed field seatNumber & columnLetter dari API */
interface SeatView extends FlightSeat {
  seatNumber: string    // e.g. "1A"
  columnLetter: string  // e.g. "A"
}

const flatSeats = computed<SeatView[]>(() =>
  seats.value.map(s => {
    const seatNum = s.seat_number ?? s.aircraft_seat?.seat_number ?? ''
    const col = s.column_letter ?? s.aircraft_seat?.column_letter ?? seatNum.slice(-1)
    return { ...s, seatNumber: seatNum, columnLetter: col }
  })
)

/** Kolom kiri (A-C) dan kanan (D-F) untuk layout 3-3 */
const leftCols = ['A', 'B', 'C']
const rightCols = ['D', 'E', 'F']

/** Group by row */
const seatRows = computed(() => {
  const rowMap = new Map<number, { leftSeats: SeatView[]; rightSeats: SeatView[] }>()

  for (const seat of flatSeats.value) {
    const rowNum = seat.row_number ?? seat.aircraft_seat?.row_number ?? 0
    if (!rowMap.has(rowNum)) {
      rowMap.set(rowNum, { leftSeats: [], rightSeats: [] })
    }
    const col = seat.columnLetter
    if (leftCols.includes(col)) {
      rowMap.get(rowNum)!.leftSeats.push(seat)
    } else {
      rowMap.get(rowNum)!.rightSeats.push(seat)
    }
  }

  return Array.from(rowMap.entries())
    .map(([rowNumber, cols]) => ({
      rowNumber,
      leftSeats: cols.leftSeats.sort((a, b) => a.columnLetter.localeCompare(b.columnLetter)),
      rightSeats: cols.rightSeats.sort((a, b) => a.columnLetter.localeCompare(b.columnLetter)),
    }))
    .sort((a, b) => a.rowNumber - b.rowNumber)
})

// ─── Event handlers ───────────────────────────────────────────────────────────

const handleSeatClick = async (seat: SeatView) => {
  if (!seat.is_available && !isSeatSelected(seat.seatNumber)) return
  await toggleSeat(seat.id, seat.seatNumber)
  emit('seats-selected', selectedSeats.value)
}

const removeSeat = async (locked: LockedSeat) => {
  await toggleSeat(locked.seatId, locked.seatNumber)
  emit('seats-selected', selectedSeats.value)
}

// ─── CSS helpers ──────────────────────────────────────────────────────────────

const getSeatClass = (seat: SeatView): string => {
  if (isSeatSelected(seat.seatNumber)) {
    return 'bg-blue-500 hover:bg-blue-600 text-white ring-2 ring-blue-300 scale-105'
  }
  if (!seat.is_available) {
    return 'bg-red-100 text-red-400 cursor-not-allowed opacity-60'
  }
  if (seat.is_exit_row ?? seat.aircraft_seat?.is_exit_row) {
    return 'bg-amber-400 hover:bg-amber-500 text-white'
  }
  return 'bg-emerald-400 hover:bg-emerald-500 text-white'
}

const getSeatTooltip = (seat: SeatView): string => {
  const cls = seat.seat_class?.display_name ?? seat.aircraft_seat?.seat_class?.display_name ?? ''
  const price = seat.current_price
    ? ` · Rp ${new Intl.NumberFormat('id-ID').format(seat.current_price)}`
    : ''
  if (!seat.is_available) return `${seat.seatNumber} — Tidak tersedia`
  return `${seat.seatNumber}${cls ? ' · ' + cls : ''}${price}`
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(() => {
  loadSeats()
})
</script>
