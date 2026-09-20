<script setup lang="ts">
import OfferCard, { type Offer } from './OfferCard.vue'

const offers: Offer[] = [
  {
    id: 1,
    title: 'Jakarta ke Bali',
    description: 'Mulai dari ',
    price: 'Rp 600rb',
    image: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600&auto=format&fit=crop&q=75',
    imageAlt: 'Pantai Bali dengan pemandangan yang indah',
    badge: '⚡ Cepat Habis',
    badgeVariant: 'green',
    href: '#',
  },
  {
    id: 2,
    title: 'Coldplay Live in Jakarta',
    description: 'Tiket mulai dari ',
    price: 'Rp 1,6jt',
    image: 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&auto=format&fit=crop&q=75',
    imageAlt: 'Konser musik dengan panggung penuh cahaya',
    badge: '★ Booking Cepat',
    badgeVariant: 'blue',
    href: '#',
  },
  {
    id: 3,
    title: 'Kereta ke Jogja',
    description: 'Eksekutif dari ',
    price: 'Rp 200rb',
    image: 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&auto=format&fit=crop&q=75',
    imageAlt: 'Kereta api di jalur pegunungan hijau',
    badge: '% Diskon Kereta',
    badgeVariant: 'yellow',
    href: '#',
  },
]
</script>

<template>
  <section class="py-16 bg-white" aria-label="Penawaran Spesial Hari Ini">
    <div class="max-w-7xl mx-auto px-6">

      <!-- Header -->
      <div class="flex items-end justify-between mb-7 gap-4">
        <div>
          <h2 class="text-2xl font-extrabold text-navy tracking-tight">Penawaran Spesial Hari Ini</h2>
          <p class="text-sm text-gray-500 mt-1">Harga terbaik untuk petualangan Anda selanjutnya.</p>
        </div>
        <a
          href="#"
          class="flex items-center gap-1.5 text-teal text-sm font-semibold whitespace-nowrap hover:gap-3 transition-all"
          aria-label="Lihat semua penawaran"
        >
          Lihat Semua
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>
      </div>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <OfferCard
          v-for="offer in offers"
          :key="offer.id"
          :offer="offer"
        />
      </div>

    </div>
  </section>
</template>
