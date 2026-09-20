<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const parallaxY = ref(0)

function handleScroll() {
  if (window.scrollY < 600) {
    parallaxY.value = window.scrollY * 0.25
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <section
    class="relative min-h-[520px] flex items-end overflow-hidden"
    aria-label="Hero – Akses Tanpa Batas"
  >
    <!-- Background image with parallax -->
    <div
      class="absolute inset-0"
      role="img"
      aria-label="Interior pesawat modern"
      :style="{ transform: `translateY(${parallaxY}px)` }"
    >
      <!-- Overlay gradient -->
      <div class="absolute inset-0 z-10 bg-gradient-to-b from-navy/35 via-navy/20 to-navy/85" />
      <!-- Image -->
      <img
        src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1600&auto=format&fit=crop&q=80"
        alt=""
        class="w-full h-full object-cover scale-105"
        aria-hidden="true"
        loading="eager"
      />
    </div>

    <!-- Content -->
    <div class="relative z-20 max-w-7xl mx-auto px-6 w-full">
      <div class="pb-16 pt-32 max-w-2xl">
        <h1 class="text-white font-black leading-[1.08] tracking-tight mb-5 text-5xl lg:text-6xl">
          Akses Tanpa Batas:<br />
          Perjalanan<br />
          <span class="text-teal-light">Impian Dimulai</span> di Sini.
        </h1>
        <p class="text-white/78 text-base leading-relaxed max-w-lg">
          Eksplorasi destini impian dengan layanan premium. Nikmati
          pengalaman tak terlupakan dan wujudkan mimpimu bersama kami.
          Terbang nyaman, tiba bahagia.
        </p>
      </div>
    </div>
  </section>
</template>
