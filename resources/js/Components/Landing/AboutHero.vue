<script setup lang="ts">
// Static hero — no interactivity needed
</script>

<template>
  <section
    class="relative min-h-[500px] flex flex-col items-center justify-center overflow-hidden"
    aria-label="Hero Tentang Kami"
  >
    <!-- Background: dark overlay biar navbar putih keliatan -->
    <div class="absolute inset-0">
      <!-- Airplane image -->
      <img
        src="https://images.unsplash.com/photo-1570710891163-6d3b5c47248b?w=1400&auto=format&fit=crop&q=80"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover object-center"
        loading="eager"
      />
      <!-- Dark overlay biar teks navbar putih terbaca -->
      <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/30 to-black/10" />
      <!-- Bottom fade to white -->
      <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white to-transparent" />
    </div>

    <!-- Content — pt-20 untuk kasih ruang navbar fixed -->
    <div class="relative z-10 text-center px-6 pt-28 pb-16">
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight mb-6 tracking-tight drop-shadow-lg">
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
