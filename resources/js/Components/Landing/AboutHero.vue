<script setup lang="ts">
// Static hero — no interactivity needed
</script>

<template>
  <section
    class="relative min-h-[420px] flex flex-col items-center justify-center overflow-hidden"
    aria-label="Hero Tentang Kami"
  >
    <!-- Background: sky + airplane -->
    <div class="absolute inset-0">
      <!-- Sky gradient base -->
      <div class="absolute inset-0 bg-gradient-to-b from-[#c8d8f0] via-[#e8c9a0] to-[#f0dfc0]" />
      <!-- Airplane image -->
      <img
        src="https://images.unsplash.com/photo-1570710891163-6d3b5c47248b?w=1400&auto=format&fit=crop&q=80"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover object-center mix-blend-multiply opacity-60"
        loading="eager"
      />
      <!-- Bottom fade to white -->
      <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white to-transparent" />
    </div>

    <!-- Content -->
    <div class="relative z-10 text-center px-6 pt-24 pb-16">
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-[#1a3a2e] leading-tight mb-6 tracking-tight">
        Tentang Jelajahin
      </h1>

      <!-- Tagline card -->
      <div class="inline-block bg-white/90 backdrop-blur-sm rounded-2xl px-8 py-4 shadow-lg max-w-lg">
        <p class="text-gray-600 text-base md:text-lg leading-relaxed">
          Membuat perjalanan Anda lebih mudah, terjangkau, dan menyenangkan.
        </p>
      </div>
    </div>
  </section>
</template>
