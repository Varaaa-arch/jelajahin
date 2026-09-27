<template>
  <div class="space-y-5">
    <!-- Bagasi Tambahan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-1">
        <svg class="w-5 h-5 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M6 7h12M6 7a2 2 0 01-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 01-2-2M9 7V5a3 3 0 013-3h0a3 3 0 013 3v2" />
          <path d="M12 11v6M9 14h6" />
        </svg>
        <h3 class="font-bold text-gray-900">Bagasi Tambahan</h3>
      </div>
      <p class="text-sm text-gray-500 mb-4">Setiap penumpang sudah mendapatkan 20kg bagasi terdaftar gratis.</p>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button
          v-for="opt in baggageOptions"
          :key="opt.value"
          type="button"
          @click="$emit('update:baggage', opt.value)"
          class="relative rounded-xl border-2 p-4 text-center transition-all"
          :class="modelBaggage === opt.value ? 'border-teal bg-teal/5' : 'border-gray-200 bg-white hover:border-gray-300'"
        >
          <span v-if="opt.badge" class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-teal text-white text-[10px] font-bold tracking-widest px-2 py-0.5 rounded">PALING POPULER</span>
          <p class="text-sm font-semibold text-gray-900">{{ opt.label }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ opt.priceLabel }}</p>
          <div class="mt-3 flex justify-center">
            <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="modelBaggage === opt.value ? 'border-teal bg-teal' : 'border-gray-300'">
              <svg v-if="modelBaggage === opt.value" class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            </span>
          </div>
        </button>
      </div>
    </div>

    <!-- Asuransi Perjalanan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z"/><path d="M9 12l2 2 4-4"/>
        </svg>
        <h3 class="font-bold text-gray-900">Asuransi Perjalanan</h3>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="opt in insuranceOptions"
          :key="opt.value"
          class="relative rounded-xl border-2 p-5 flex flex-col"
          :class="modelInsurance === opt.value ? 'border-teal bg-teal/5' : 'border-gray-200'"
        >
          <span v-if="opt.badge" class="absolute -top-2.5 right-4 bg-teal text-white text-[10px] font-bold tracking-widest px-2 py-0.5 rounded">REKOMENDASI</span>
          <p class="font-bold text-gray-900">{{ opt.label }}</p>
          <ul class="mt-3 space-y-1.5 text-sm text-gray-600 flex-1">
            <li v-for="f in opt.features" :key="f" class="flex gap-2">
              <svg class="w-4 h-4 shrink-0 mt-0.5" :class="modelInsurance === opt.value ? 'text-teal' : 'text-emerald-500'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              {{ f }}
            </li>
          </ul>
          <div class="border-t border-gray-200 mt-4 pt-4 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700">{{ opt.priceLabel }}</span>
            <button
              type="button"
              @click="$emit('update:insurance', modelInsurance === opt.value ? 'none' : opt.value)"
              class="px-4 py-1.5 rounded-lg text-sm font-semibold border transition-colors"
              :class="modelInsurance === opt.value ? 'bg-teal border-teal text-white' : 'border-teal text-teal hover:bg-teal hover:text-white'"
            >{{ modelInsurance === opt.value ? 'Terpilih' : 'Pilih' }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pilihan Makanan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 3v10a2 2 0 002 2h0"/><path d="M7 3v7"/><path d="M11 3v7"/><path d="M15 3v18"/><path d="M18 8a3 3 0 100 6h2V8h-2z"/>
        </svg>
        <h3 class="font-bold text-gray-900">Pilihan Makanan</h3>
      </div>
      <div class="space-y-3">
        <div
          v-for="(slot, idx) in passengerSlots"
          :key="slot.key"
          class="flex items-center justify-between gap-3 bg-gray-50 rounded-xl px-4 py-3"
        >
          <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-gray-200 text-gray-700 text-xs font-bold flex items-center justify-center">P{{ idx+1 }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ slot.label }}</p>
              <p class="text-xs text-gray-500">{{ slot.subtitle ?? 'Dewasa' }}</p>
            </div>
          </div>
          <select
            :value="modelMeals[idx] ?? 'Halal Meal (Included)'"
            @change="$emit('update:meal', { idx, value: ($event.target as HTMLSelectElement).value })"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/10"
          >
            <option>Halal Meal (Included)</option>
            <option>Vegan Meal (Included)</option>
            <option>Vegetarian Meal (Included)</option>
            <option>No Meal</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelBaggage: string
  modelInsurance: string
  modelMeals: Record<number, string>
  passengerSlots: Array<{ key: string; label: string; subtitle?: string }>
}>()

defineEmits<{
  'update:baggage': [value: string]
  'update:insurance': [value: string]
  'update:meal': [payload: { idx: number; value: string }]
}>()

const baggageOptions = [
  { value: 'none', label: 'Tanpa Tambahan', priceLabel: 'Rp 0', badge: '' },
  { value: '5kg', label: 'Ekstra 5kg', priceLabel: '+ Rp 150.000', badge: 'PALING POPULER' },
  { value: '10kg', label: 'Ekstra 10kg', priceLabel: '+ Rp 280.000', badge: '' },
]

const insuranceOptions = [
  { value: 'basic', label: 'Basic Protect', priceLabel: '+ Rp 45.000/pax', badge: '', features: ['Keterlambatan bagasi','Medis dasar'] },
  { value: 'premium', label: 'Premium Shield', priceLabel: '+ Rp 85.000/pax', badge: 'REKOMENDASI', features: ['Pembatalan tiket 100%','Perlindungan medis penuh','Perlindungan COVID-19'] },
]
</script>
