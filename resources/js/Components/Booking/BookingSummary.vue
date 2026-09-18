<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Booking Summary</h2>

    <!-- Flight Info -->
    <div class="bg-blue-50 p-6 rounded-lg mb-6 border-l-4 border-blue-600">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
          <p class="text-sm text-gray-600">Flight</p>
          <p class="font-bold">{{ flight.flight_number }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Date</p>
          <p class="font-bold">{{ formatDate(flight.departure_date) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Time</p>
          <p class="font-bold">{{ flight.departure_time.substring(0, 5) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Passengers</p>
          <p class="font-bold">{{ passengers.length }}</p>
        </div>
      </div>
    </div>

    <!-- Passengers List -->
    <div class="mb-6">
      <h3 class="font-bold mb-4">Passengers</h3>
      <div class="space-y-2">
        <div v-for="(p, idx) in passengers" :key="idx" class="p-3 bg-gray-50 rounded">
          {{ idx + 1 }}. {{ p.title }}. {{ p.first_name }} {{ p.last_name }}
        </div>
      </div>
    </div>

    <!-- Price Breakdown -->
    <div class="bg-gray-50 p-6 rounded-lg mb-6">
      <h3 class="font-bold mb-4">Price Breakdown</h3>
      <div class="space-y-2">
        <div class="flex justify-between">
          <span>Base Price ({{ passengers.length }} pax × Rp {{ formatPrice(flight.base_price) }})</span>
          <span>Rp {{ formatPrice(baseAmount) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Tax (10%)</span>
          <span>Rp {{ formatPrice(taxAmount) }}</span>
        </div>
        <div v-if="discountAmount > 0" class="flex justify-between text-green-600">
          <span>Discount</span>
          <span>-Rp {{ formatPrice(discountAmount) }}</span>
        </div>
        <div class="border-t-2 pt-2 flex justify-between font-bold text-lg">
          <span>Total</span>
          <span class="text-blue-600">Rp {{ formatPrice(totalAmount) }}</span>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="flex gap-4">
      <button 
        @click="goBack"
        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 rounded-lg"
      >
        Back
      </button>
      <button 
        @click="proceedToPayment"
        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg"
      >
        Proceed to Payment
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  flight: any
  passengers: any[]
}>()

const emit = defineEmits<{
  'proceed-to-payment': []
  'back': []
}>()

const baseAmount = computed(() => props.flight.base_price * props.passengers.length)
const taxAmount = computed(() => Math.round(baseAmount.value * 0.1))
const discountAmount = computed(() => 0) // TODO: implement promo
const totalAmount = computed(() => baseAmount.value + taxAmount.value - discountAmount.value)

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(Math.round(price))
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

const proceedToPayment = () => {
  emit('proceed-to-payment')
}

const goBack = () => {
  emit('back')
}
</script>
