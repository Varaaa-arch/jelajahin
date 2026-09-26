<template>
  <div>
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
    <div v-if="seatsLoading" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 animate-pulse">
      <div class="flex justify-center gap-6 mb-6">
        <div v-for="n in 4" :key="n" class="h-4 w-20 bg-gray-100 rounded" />
      </div>
      <div class="mx-auto max-w-md space-y-3">
        <div v-for="r in 8" :key="r" class="flex gap-3 items-center justify-center">
          <div class="w-6 h-8 bg-gray-100 rounded" />
          <div v-for="c in 3" :key="'l'+c" class="w-11 h-11 bg-gray-100 rounded-lg" />
          <div class="w-8" />
          <div v-for="c in 3" :key="'r'+c" class="w-11 h-11 bg-gray-100 rounded-lg" />
        </div>
      </div>
    </div>

    <template v-else-if="seatRows.length > 0">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <!-- Legend -->
        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 pb-4 border-b border-gray-100 text-sm text-gray-500">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-gray-200" />
            Tersedia
          </div>
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-gray-400 text-white text-xs font-bold flex items-center justify-center">×</span>
            Terisi
          </div>
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-white border-2 border-sky-400" />
            Premium
          </div>
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-navy" />
            Dipilih
          </div>
        </div>

        <!-- Fuselage -->
        <div class="mt-6 flex justify-center overflow-x-auto">
          <div class="relative px-8 py-6 min-w-[320px] rounded-[2.5rem] border border-gray-100 bg-slate-50/70">
            <div class="flex justify-center mb-4">
              <svg class="w-8 h-8 text-navy" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
              </svg>
            </div>
            <div class="border-t border-dashed border-gray-300 mb-5" />

            <!-- Column headers -->
            <div class="flex items-center justify-center gap-2 mb-3 text-[11px] font-bold tracking-widest text-gray-400 select-none">
              <div class="w-6" />
              <div class="flex gap-1.5">
                <div v-for="col in leftCols" :key="col" class="w-11 text-center">{{ col }}</div>
              </div>
              <div class="w-8" />
              <div class="flex gap-1.5">
                <div v-for="col in rightCols" :key="col" class="w-11 text-center">{{ col }}</div>
              </div>
              <div class="w-6" />
            </div>

            <template v-for="(section, sIdx) in seatSections" :key="section.key">
              <p
                v-if="section.label"
                class="text-center text-[10px] font-bold tracking-[0.2em] text-gray-400 mt-4 mb-3"
              >
                {{ section.label }}
              </p>

              <div
                v-for="row in section.rows"
                :key="row.rowNumber"
                class="relative mb-2"
                :class="row.isExit ? 'py-2 my-1 border-y border-dashed border-red-300' : ''"
              >
                <span
                  v-if="row.isExit"
                  class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-6 text-[9px] font-bold tracking-widest text-red-400 rotate-[-90deg]"
                >EXIT</span>
                <span
                  v-if="row.isExit"
                  class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-6 text-[9px] font-bold tracking-widest text-red-400 rotate-90"
                >EXIT</span>

                <div class="flex items-center justify-center gap-2">
                  <div class="w-6 text-center text-xs font-semibold text-gray-400">{{ row.rowNumber }}</div>

                  <div class="flex gap-1.5">
                    <template v-for="(seat, i) in row.leftSeats" :key="'l'+row.rowNumber+i">
                      <button
                        v-if="seat"
                        type="button"
                        @click="handleSeatClick(seat)"
                        :disabled="(!seat.is_available && !isSeatSelected(seat.seatNumber)) || isSeatLoading(seat.seatNumber)"
                        :class="getSeatClass(seat)"
                        class="w-11 h-11 rounded-lg text-[10px] font-semibold transition-all duration-150 flex items-center justify-center select-none"
                        :title="getSeatTooltip(seat)"
                      >
                        <span v-if="isSeatLoading(seat.seatNumber)" class="animate-spin text-sm">⟳</span>
                        <span v-else-if="!seat.is_available && !isSeatSelected(seat.seatNumber)">×</span>
                        <span v-else>{{ seat.seatNumber }}</span>
                      </button>
                      <div v-else class="w-11 h-11" />
                    </template>
                  </div>

                  <div class="w-8 text-center text-xs font-semibold text-gray-300">{{ row.rowNumber }}</div>

                  <div class="flex gap-1.5">
                    <template v-for="(seat, i) in row.rightSeats" :key="'r'+row.rowNumber+i">
                      <button
                        v-if="seat"
                        type="button"
                        @click="handleSeatClick(seat)"
                        :disabled="(!seat.is_available && !isSeatSelected(seat.seatNumber)) || isSeatLoading(seat.seatNumber)"
                        :class="getSeatClass(seat)"
                        class="w-11 h-11 rounded-lg text-[10px] font-semibold transition-all duration-150 flex items-center justify-center select-none"
                        :title="getSeatTooltip(seat)"
                      >
                        <span v-if="isSeatLoading(seat.seatNumber)" class="animate-spin text-sm">⟳</span>
                        <span v-else-if="!seat.is_available && !isSeatSelected(seat.seatNumber)">×</span>
                        <span v-else>{{ seat.seatNumber }}</span>
                      </button>
                      <div v-else class="w-11 h-11" />
                    </template>
                  </div>

                  <div class="w-6" />
                </div>
              </div>
              <div v-if="sIdx === 0 && seatSections.length > 1" class="border-t border-gray-200 my-3" />
            </template>
          </div>
        </div>
      </div>

      <!-- Selected seats bar -->
      <div class="mt-4 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-400">Kursi terpilih</p>
          <p v-if="selectedSeats.length === 0" class="font-bold text-gray-900">None</p>
          <div v-else class="flex flex-wrap gap-2 mt-1">
            <span
              v-for="seat in selectedSeats"
              :key="seat.seatId"
              class="inline-flex items-center gap-1 bg-navy text-white px-2.5 py-1 rounded-lg text-sm"
            >
              {{ seat.seatNumber }}
              <button
                type="button"
                @click="removeSeat(seat)"
                class="ml-0.5 font-bold leading-none hover:text-gray-300"
                aria-label="Hapus kursi"
              >×</button>
            </span>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </div>

      <!-- Lock countdown -->
      <div
        v-if="selectedSeats.length > 0"
        :class="['mt-3 p-3 rounded-xl border text-sm', isLockExpiringSoon ? 'bg-red-50 border-red-200 text-red-700' : 'bg-amber-50 border-amber-200 text-amber-800']"
      >
        <div class="flex items-center justify-between mb-1.5">
          <p class="font-medium">
            {{ isLockExpiringSoon ? 'Segera selesaikan pemilihan' : 'Kursi terkunci' }}
            <span class="font-bold ml-1">{{ lockTimeLabel }}</span>
          </p>
          <span class="text-xs opacity-70">{{ selectedSeats.length }}/{{ maxSeats }}</span>
        </div>
        <div :class="['w-full h-1.5 rounded-full', isLockExpiringSoon ? 'bg-red-200' : 'bg-amber-200']">
          <div
            :class="['h-1.5 rounded-full transition-all duration-1000', isLockExpiringSoon ? 'bg-red-500' : 'bg-amber-500']"
            :style="{ width: lockPercentage + '%' }"
          />
        </div>
      </div>
    </template>

    <div v-else-if="!seatsLoading" class="bg-white rounded-2xl border border-gray-100 shadow-sm text-center py-12 text-gray-400">
      <p class="text-3xl mb-3">✈</p>
      <p>Data kursi tidak tersedia untuk penerbangan ini.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useSeatLock, type LockedSeat } from '@/composables/useSeatLock'
import type { FlightSeat } from '@/types/flight'

const props = withDefaults(defineProps<{
  flightId: string
  maxSeats?: number
  replaceSeatId?: string | null
}>(), {
  maxSeats: 1,
  replaceSeatId: null,
})

const emit = defineEmits<{
  'seats-selected': [seats: Array<LockedSeat & { currentPrice?: number }>]
}>()

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
  lockPercentage,
  lockTimeLabel,
  isLockExpiringSoon,
  skipAutoUnlock,
} = useSeatLock(props.flightId)

interface SeatView extends FlightSeat {
  seatNumber: string
  columnLetter: string
  rowNumber: number
}

const leftCols = ['A', 'B', 'C']
const rightCols = ['D', 'E', 'F']

function parseSeatMeta(s: FlightSeat): SeatView {
  const seatNum = s.seat_number ?? s.aircraft_seat?.seat_number ?? ''
  const col = s.column_letter ?? s.aircraft_seat?.column_letter ?? seatNum.replace(/^[0-9]+/, '').slice(-1)
  const parsedRow = parseInt(seatNum, 10)
  const row = s.row_number ?? s.aircraft_seat?.row_number ?? (Number.isNaN(parsedRow) ? 0 : parsedRow)
  return { ...s, seatNumber: seatNum, columnLetter: col.toUpperCase(), rowNumber: row }
}

const flatSeats = computed<SeatView[]>(() => seats.value.map(parseSeatMeta))

function isPremiumSeat(seat: SeatView): boolean {
  const name = (
    seat.seat_class?.name
    ?? seat.seat_class?.display_name
    ?? seat.aircraft_seat?.seat_class?.name
    ?? seat.aircraft_seat?.seat_class?.display_name
    ?? ''
  ).toLowerCase()
  if (name.includes('premium') || name.includes('business') || name.includes('first')) return true
  return seat.rowNumber > 0 && seat.rowNumber <= 2
}

function isExitSeat(seat: SeatView): boolean {
  return !!(seat.is_exit_row ?? seat.aircraft_seat?.is_exit_row)
}

function padCols(rowSeats: SeatView[], cols: string[]): Array<SeatView | null> {
  return cols.map(letter => rowSeats.find(s => s.columnLetter === letter) ?? null)
}

const seatRows = computed(() => {
  const rowMap = new Map<number, SeatView[]>()
  for (const seat of flatSeats.value) {
    if (!seat.rowNumber) continue
    if (!rowMap.has(seat.rowNumber)) rowMap.set(seat.rowNumber, [])
    rowMap.get(seat.rowNumber)!.push(seat)
  }

  return Array.from(rowMap.entries())
    .map(([rowNumber, rowSeats]) => ({
      rowNumber,
      leftSeats: padCols(rowSeats, leftCols),
      rightSeats: padCols(rowSeats, rightCols),
      isPremium: rowSeats.some(isPremiumSeat) || (rowNumber > 0 && rowNumber <= 2),
      isExit: rowSeats.some(isExitSeat),
    }))
    .sort((a, b) => a.rowNumber - b.rowNumber)
})

const seatSections = computed(() => {
  const premium = seatRows.value.filter(r => r.isPremium)
  const economy = seatRows.value.filter(r => !r.isPremium)
  const sections: { key: string; label: string; rows: typeof seatRows.value }[] = []
  if (premium.length) sections.push({ key: 'premium', label: 'PREMIUM CLASS', rows: premium })
  if (economy.length) sections.push({ key: 'economy', label: 'ECONOMY CLASS', rows: economy })
  return sections
})

function enrich(list: LockedSeat[]) {
  return list.map(s => {
    const full = flatSeats.value.find(f => f.id === s.seatId)
    return { ...s, currentPrice: full?.current_price ?? 0 }
  })
}

function emitSeats() {
  emit('seats-selected', enrich(selectedSeats.value))
}

watch(selectedSeats, () => emitSeats(), { deep: true })

const handleSeatClick = async (seat: SeatView) => {
  if (!seat.is_available && !isSeatSelected(seat.seatNumber)) return

  if (isSeatSelected(seat.seatNumber)) {
    await toggleSeat(seat.id, seat.seatNumber)
    return
  }

  if (selectedSeats.value.length >= props.maxSeats) {
    if (props.replaceSeatId) {
      const old = selectedSeats.value.find(s => s.seatId === props.replaceSeatId)
      if (old) await toggleSeat(old.seatId, old.seatNumber)
      await toggleSeat(seat.id, seat.seatNumber)
      return
    }
    lockError.value = `Maksimal ${props.maxSeats} kursi sesuai jumlah penumpang`
    return
  }

  await toggleSeat(seat.id, seat.seatNumber)
}

const removeSeat = async (locked: LockedSeat) => {
  await toggleSeat(locked.seatId, locked.seatNumber)
}

const getSeatClass = (seat: SeatView): string => {
  if (isSeatSelected(seat.seatNumber)) {
    return 'bg-navy text-white hover:bg-navy/90'
  }
  if (!seat.is_available) {
    return 'bg-gray-400 text-white cursor-not-allowed'
  }
  if (isPremiumSeat(seat)) {
    return 'bg-white text-navy border-2 border-sky-400 hover:bg-sky-50'
  }
  return 'bg-gray-200 text-gray-600 hover:bg-gray-300'
}

const getSeatTooltip = (seat: SeatView): string => {
  const cls = seat.seat_class?.display_name ?? seat.aircraft_seat?.seat_class?.display_name ?? ''
  const price = seat.current_price
    ? ` · Rp ${new Intl.NumberFormat('id-ID').format(seat.current_price)}`
    : ''
  if (!seat.is_available) return `${seat.seatNumber} — Terisi`
  return `${seat.seatNumber}${cls ? ' · ' + cls : ''}${price}`
}

onMounted(() => {
  loadSeats()
})

defineExpose({ skipAutoUnlock })
</script>
