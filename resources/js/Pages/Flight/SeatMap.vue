<template>
  <div class="bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6">Pilih Kursi</h2>

    <!-- Seat Grid -->
    <div class="bg-gray-100 p-6 rounded-lg overflow-x-auto">
      <div class="inline-block">
        <!-- Rows -->
        <div v-for="row in 10" :key="row" class="flex gap-4 mb-4">
          <!-- Row number -->
          <div class="w-8 flex items-center justify-center text-sm font-semibold text-gray-600">
            {{ row }}
          </div>

          <!-- Seats A-F -->
          <div class="flex gap-2">
            <button 
              v-for="col in ['A', 'B', 'C', 'D', 'E', 'F']"
              :key="`${row}${col}`"
              @click="handleSeatClick(row, col)"
              :disabled="isSeatLocked(row, col) || isSeatLoading(row, col)"
              :class="getSeatClass(row, col)"
              class="w-10 h-10 rounded font-semibold text-sm transition"
            >
              <!-- Show spinner jika sedang lock -->
              <span v-if="isSeatLoading(row, col)" class="animate-spin">⟳</span>
              <span v-else>{{ row }}{{ col }}</span>
            </button>
          </div>

          <!-- Aisle -->
          <div class="w-4"></div>

          <!-- Seats G-L -->
          <div class="flex gap-2">
            <button 
              v-for="col in ['G', 'H', 'I', 'J', 'K', 'L']"
              :key="`${row}${col}`"
              @click="handleSeatClick(row, col)"
              :disabled="isSeatLocked(row, col) || isSeatLoading(row, col)"
              :class="getSeatClass(row, col)"
              class="w-10 h-10 rounded font-semibold text-sm transition"
            >
              <span v-if="isSeatLoading(row, col)" class="animate-spin">⟳</span>
              <span v-else>{{ row }}{{ col }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex gap-8">
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-green-500 rounded"></div>
        <span class="text-sm text-gray-600">Tersedia</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-blue-500 rounded"></div>
        <span class="text-sm text-gray-600">Terpilih</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-red-500 rounded"></div>
        <span class="text-sm text-gray-600">Terkunci</span>
      </div>
    </div>

    <!-- Selected Seats Summary -->
    <div v-if="selectedSeats.length > 0" class="mt-6 p-4 bg-blue-50 rounded-lg">
      <p class="font-semibold mb-2">Kursi Terpilih:</p>
      <div class="flex flex-wrap gap-2">
        <span 
          v-for="(seat, idx) in selectedSeats"
          :key="idx"
          class="bg-blue-500 text-white px-3 py-1 rounded text-sm flex items-center gap-2"
        >
          {{ seat.seatNumber }}
          <button @click="deselectSeat(idx)" class="font-bold">×</button>
        </span>
      </div>
    </div>

    <!-- Lock Countdown -->
    <div v-if="selectedSeats.length > 0" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-sm text-yellow-800">
        Kursi terkunci selama: <span class="font-bold">{{ lockTimeRemaining }}s</span>
      </p>
      <div class="mt-2 w-full bg-yellow-200 rounded-full h-2">
        <div 
          class="bg-yellow-600 h-2 rounded-full transition-all"
          :style="{ width: lockPercentage + '%' }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { httpClient } from '@/utils/http'

const props = defineProps<{
  flightId: string
}>()

const emit = defineEmits<{
  'seats-selected': [seats: Array<{seatNumber: string, seatId: string}>]
}>()

// State
const selectedSeats = ref<Array<{seatNumber: string, seatId: string}>>([])
const lockedSeats = ref<Set<string>>(new Set())
const loadingSeats = ref<Set<string>>(new Set())
const lockTimeRemaining = ref(900) // 15 minutes in seconds
const lockStartTime = ref<number | null>(null)

// Computed
const lockPercentage = computed(() => {
  if (!lockStartTime.value) return 100
  const elapsed = (Date.now() - lockStartTime.value) / 1000
  return Math.max(0, ((900 - elapsed) / 900) * 100)
})

// Methods
const handleSeatClick = async (row: number, col: string) => {
  const seatNumber = `${row}${col}`
  const seatId = `seat-${row}-${col}`

  // Check if already selected
  const alreadySelected = selectedSeats.value.find(s => s.seatNumber === seatNumber)
  if (alreadySelected) {
    deselectSeat(selectedSeats.value.indexOf(alreadySelected))
    return
  }

  // Try to lock seat
  loadingSeats.value.add(seatNumber)

  try {
    const response = await httpClient.post('/api/v1/seats/lock', {
      flight_id: props.flightId,
      seat_id: seatId,
      user_id: 'current-user-id' // TODO: get from auth store
    })

    if (response.data.success) {
      // Lock successful
      selectedSeats.value.push({
        seatNumber,
        seatId
      })
      
      // Set lock countdown timer
      if (lockStartTime.value === null) {
        lockStartTime.value = Date.now()
        startLockCountdown()
      }

      emit('seats-selected', selectedSeats.value)
    } else {
      // Lock failed
      alert(`Kursi ${seatNumber} sudah terkunci oleh ${response.data.locked_by}`)
    }
  } catch (error: any) {
    console.error('Lock seat error:', error)
    alert('Gagal mengunci kursi. Silakan coba lagi.')
  } finally {
    loadingSeats.value.delete(seatNumber)
  }
}

const deselectSeat = (index: number) => {
  const seat = selectedSeats.value[index]
  
  // Unlock seat from backend
  httpClient.post('/api/v1/seats/unlock', {
    flight_id: props.flightId,
    seat_id: seat.seatId
  }).catch(err => console.error('Unlock error:', err))

  selectedSeats.value.splice(index, 1)

  // Reset timer if no more seats
  if (selectedSeats.value.length === 0) {
    lockStartTime.value = null
    lockTimeRemaining.value = 900
  }

  emit('seats-selected', selectedSeats.value)
}

const isSeatLocked = (row: number, col: string) => {
  const seatNumber = `${row}${col}`
  return lockedSeats.value.has(seatNumber)
}

const isSeatLoading = (row: number, col: string) => {
  const seatNumber = `${row}${col}`
  return loadingSeats.value.has(seatNumber)
}

const isSeatSelected = (row: number, col: string) => {
  const seatNumber = `${row}${col}`
  return selectedSeats.value.some(s => s.seatNumber === seatNumber)
}

const getSeatClass = (row: number, col: string) => {
  const seatNumber = `${row}${col}`
  
  if (isSeatSelected(row, col)) {
    return 'bg-blue-500 text-white hover:bg-blue-600'
  }
  if (isSeatLocked(row, col)) {
    return 'bg-red-500 text-white cursor-not-allowed'
  }
  return 'bg-green-500 text-white hover:bg-green-600'
}

// Countdown timer
const startLockCountdown = () => {
  const interval = setInterval(() => {
    lockTimeRemaining.value--

    if (lockTimeRemaining.value <= 0) {
      clearInterval(interval)
      alert('Waktu penguncian kursi habis. Silakan pilih kursi kembali.')
      selectedSeats.value = []
      lockStartTime.value = null
      lockTimeRemaining.value = 900
    }
  }, 1000)
}

// Lifecycle
onMounted(() => {
  // Could load locked seats dari server
})

onUnmounted(() => {
  // Unlock all seats saat component unmount
  selectedSeats.value.forEach(seat => {
    httpClient.post('/api/v1/seats/unlock', {
      flight_id: props.flightId,
      seat_id: seat.seatId
    }).catch(err => console.error('Cleanup unlock error:', err))
  })
})
</script>
