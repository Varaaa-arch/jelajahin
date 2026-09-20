<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Stat {
  value: string
  label: string
}

const stats: Stat[] = [
  { value: '5M+',  label: 'Pengguna Aktif' },
  { value: '50+',  label: 'Mitra Maskapai' },
  { value: '200+', label: 'Rute Penerbangan' },
  { value: '4.8',  label: 'Rating Pengguna' },
]

// Animate numbers into view
const visible = ref(false)
let observer: IntersectionObserver | null = null
const sectionRef = ref<HTMLElement | null>(null)

onMounted(() => {
  if ('IntersectionObserver' in window && sectionRef.value) {
    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          visible.value = true
          observer?.disconnect()
        }
      },
      { threshold: 0.3 },
    )
    observer.observe(sectionRef.value)
  } else {
    visible.value = true
  }
})
</script>

<template>
  <section
    ref="sectionRef"
    class="py-12 bg-white border-t border-gray-100"
    aria-label="Statistik Jelajahin"
  >
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          :class="[
            'flex flex-col items-center justify-center text-center p-6 rounded-2xl border border-gray-200 transition-all duration-500',
            visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4',
          ]"
        >
          <span class="text-4xl font-black text-[#1a3a2e] tracking-tight mb-2">
            {{ stat.value }}
          </span>
          <span class="text-sm text-gray-500 font-medium">{{ stat.label }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
