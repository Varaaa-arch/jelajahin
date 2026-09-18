<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Payment Method</h2>

    <div class="space-y-4 mb-8">
      <label v-for="method in paymentMethods" :key="method.id" class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="selectedMethod === method.id ? 'border-blue-600 bg-blue-50' : 'border-gray-200'">
        <input 
          type="radio" 
          :value="method.id" 
          v-model="selectedMethod"
          class="mr-4"
        />
        <div>
          <p class="font-semibold">{{ method.name }}</p>
          <p class="text-sm text-gray-600">{{ method.description }}</p>
        </div>
      </label>
    </div>

    <!-- Amount Summary -->
    <div class="bg-gray-50 p-6 rounded-lg mb-6">
      <div class="flex justify-between mb-2">
        <span>Total Amount</span>
        <span class="font-bold text-lg text-blue-600">Rp {{ formatPrice(totalAmount) }}</span>
      </div>
      <p class="text-sm text-gray-600">Selected Method: {{ selectedMethodName }}</p>
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
import { ref, computed } from 'vue'

const props = defineProps<{
  totalAmount: number
}>()

const emit = defineEmits<{
  'payment-method-selected': [method: string]
  'back': []
}>()

const selectedMethod = ref('credit_card')

const paymentMethods = [
  { id: 'credit_card', name: 'Credit Card', description: 'Visa, Mastercard, Amex' },
  { id: 'debit_card', name: 'Debit Card', description: 'Bank Debit Card' },
  { id: 'bank_transfer', name: 'Bank Transfer', description: 'Direct bank transfer' },
  { id: 'ewallet', name: 'E-Wallet', description: 'GCash, PayMaya, OVO' },
]

const selectedMethodName = computed(() => {
  return paymentMethods.find(m => m.id === selectedMethod.value)?.name || 'Unknown'
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(Math.round(price))
}

const proceedToPayment = () => {
  emit('payment-method-selected', selectedMethod.value)
}

const goBack = () => {
  emit('back')
}
</script>
