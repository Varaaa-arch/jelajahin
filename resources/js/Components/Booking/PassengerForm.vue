<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Passenger Details</h2>

    <div v-for="(passenger, idx) in passengers" :key="idx" class="mb-8 p-6 bg-gray-50 rounded-lg border-l-4 border-blue-600">
      <h3 class="font-bold mb-4">Passenger {{ idx + 1 }}</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Title -->
        <div>
          <label class="block text-sm font-medium mb-2">Title</label>
          <select 
            v-model="passengers[idx].title"
            class="w-full px-4 py-2 border rounded-lg"
          >
            <option value="Mr">Mr</option>
            <option value="Mrs">Mrs</option>
            <option value="Ms">Ms</option>
            <option value="Dr">Dr</option>
          </select>
        </div>

        <!-- First Name -->
        <div>
          <label class="block text-sm font-medium mb-2">First Name *</label>
          <input 
            v-model="passengers[idx].first_name"
            type="text"
            class="w-full px-4 py-2 border rounded-lg"
            placeholder="John"
            required
          />
        </div>

        <!-- Last Name -->
        <div>
          <label class="block text-sm font-medium mb-2">Last Name *</label>
          <input 
            v-model="passengers[idx].last_name"
            type="text"
            class="w-full px-4 py-2 border rounded-lg"
            placeholder="Doe"
            required
          />
        </div>

        <!-- Date of Birth -->
        <div>
          <label class="block text-sm font-medium mb-2">Date of Birth *</label>
          <input 
            v-model="passengers[idx].date_of_birth"
            type="date"
            class="w-full px-4 py-2 border rounded-lg"
            required
          />
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-sm font-medium mb-2">Gender *</label>
          <select 
            v-model="passengers[idx].gender"
            class="w-full px-4 py-2 border rounded-lg"
            required
          >
            <option value="">Select Gender</option>
            <option value="M">Male</option>
            <option value="F">Female</option>
          </select>
        </div>

        <!-- Identity Type -->
        <div>
          <label class="block text-sm font-medium mb-2">Identity Type *</label>
          <select 
            v-model="passengers[idx].identity_type"
            class="w-full px-4 py-2 border rounded-lg"
            required
          >
            <option value="">Select Type</option>
            <option value="passport">Passport</option>
            <option value="id_card">ID Card</option>
            <option value="driver_license">Driver License</option>
          </select>
        </div>

        <!-- Identity Number -->
        <div>
          <label class="block text-sm font-medium mb-2">Identity Number *</label>
          <input 
            v-model="passengers[idx].identity_number"
            type="text"
            class="w-full px-4 py-2 border rounded-lg"
            placeholder="AB123456"
            required
          />
        </div>

        <!-- Nationality -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-2">Nationality</label>
          <input 
            v-model="passengers[idx].nationality"
            type="text"
            class="w-full px-4 py-2 border rounded-lg"
            placeholder="Indonesian"
          />
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="flex gap-4 mt-8">
      <button 
        @click="goBack"
        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 rounded-lg"
      >
        Back
      </button>
      <button 
        @click="submitPassengers"
        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg"
      >
        Continue to Summary
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  seatCount: number
}>()

const emit = defineEmits<{
  'passengers-submitted': [passengers: any[]]
  'back': []
}>()

const passengers = ref<any[]>([])

// Initialize passengers array
watch(() => props.seatCount, (newCount) => {
  passengers.value = Array(newCount).fill(null).map(() => ({
    title: 'Mr',
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: '',
    identity_type: '',
    identity_number: '',
    nationality: 'Indonesian',
  }))
}, { immediate: true })

const submitPassengers = () => {
  if (passengers.value.length === 0) {
    alert('No passengers to submit. Please go back and select your seats first.')
    return
  }

  // Validate all passengers
  const allValid = passengers.value.every(p => 
    p.first_name && p.last_name && p.date_of_birth && p.gender && p.identity_type && p.identity_number
  )

  if (!allValid) {
    alert('Please fill all required passenger fields')
    return
  }

  emit('passengers-submitted', passengers.value)
}

const goBack = () => {
  emit('back')
}
</script>
