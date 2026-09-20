<script setup lang="ts">
interface Pillar {
  id: number
  title: string
  description: string
  icon: string
}

const pillars: Pillar[] = [
  {
    id: 1,
    title: 'Misi',
    description:
      'Memberikan pengalaman pemesanan penerbangan yang paling mulus, transparan, dan terjangkau, menghubungkan orang dengan destinasi impian mereka.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>`,
  },
  {
    id: 2,
    title: 'Visi',
    description:
      'Menjadi platform perjalanan nomor satu di Asia yang diakui karena inovasi teknologi dan komitmen tak tergoyahkan terhadap kepuasan pelanggan.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`,
  },
  {
    id: 3,
    title: 'Nilai-Nilai',
    description:
      'Integritas, Inovasi Berkelanjutan, Fokus Pelanggan, dan Keberlanjutan dalam setiap aspek operasional kami.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>`,
  },
]
</script>

<template>
  <section
    class="py-20 bg-[#0f3028]"
    aria-label="Fondasi Jelajahin"
  >
    <div class="max-w-6xl mx-auto px-6">

      <!-- Title -->
      <h2 class="text-3xl font-extrabold text-white text-center mb-14 tracking-tight">
        Fondasi Kami
      </h2>

      <!-- 3 columns -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <article
          v-for="pillar in pillars"
          :key="pillar.id"
          class="group"
          :aria-label="pillar.title"
        >
          <!-- Icon -->
          <div class="mb-4">
            <svg
              class="w-8 h-8 text-teal-light"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              viewBox="0 0 24 24"
              aria-hidden="true"
              v-html="pillar.icon"
            />
          </div>

          <h3 class="text-white font-bold text-xl mb-3">{{ pillar.title }}</h3>
          <p class="text-white/65 text-sm leading-relaxed">{{ pillar.description }}</p>
        </article>
      </div>

    </div>
  </section>
</template>
