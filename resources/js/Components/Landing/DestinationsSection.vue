<script setup lang="ts">
interface Destination {
  id: number
  name: string
  country?: string
  price: string
  image: string
  imageAlt: string
  featured?: boolean
  badge?: string
  description?: string
}

const destinations: Destination[] = [
  {
    id: 1,
    name: 'Tokyo',
    country: 'Jepang',
    price: 'Rp 8Jt',
    image: 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800&auto=format&fit=crop&q=80',
    imageAlt: 'Panorama kota Tokyo di malam hari dengan Tokyo Tower',
    featured: true,
    badge: 'Populer',
    description: 'Eksplorasi perpaduan tradisi dan modernitas di kota metropolitan yang tak pernah tidur.',
  },
  {
    id: 2,
    name: 'Maldives',
    price: 'Rp 10Jt',
    image: 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=500&auto=format&fit=crop&q=75',
    imageAlt: 'Overwater bungalow di Maldives dengan air biru jernih',
  },
  {
    id: 3,
    name: 'Paris',
    country: 'France',
    price: 'Rp 12Jt',
    image: 'https://images.unsplash.com/photo-1431274172761-fca41d930114?w=500&auto=format&fit=crop&q=75',
    imageAlt: 'Menara Eiffel Paris di senja hari',
  },
  {
    id: 4,
    name: 'Kawah Ijen',
    price: 'Rp 2Jt',
    image: 'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?w=500&auto=format&fit=crop&q=75',
    imageAlt: 'Kawah Ijen dengan api biru dan danau belerang',
  },
  {
    id: 5,
    name: 'Labuan Bajo',
    price: 'Rp 3,2Jt',
    image: 'https://images.unsplash.com/photo-1518002054494-3a6f94352e9d?w=500&auto=format&fit=crop&q=75',
    imageAlt: 'Pelabuhan dan perbukitan Labuan Bajo saat matahari terbenam',
  },
]

const featured = destinations.find(d => d.featured)!
const tiles = destinations.filter(d => !d.featured)
</script>

<template>
  <section class="py-16 bg-gray-50" aria-label="Destinasi Premium">
    <div class="max-w-7xl mx-auto px-6">

      <!-- Header -->
      <div class="flex items-end justify-between mb-7 gap-4">
        <div>
          <h2 class="text-2xl font-extrabold text-navy tracking-tight">Destinasi Premium</h2>
          <p class="text-sm text-gray-500 mt-1">Jelajahi keindahan dunia bersama partner terpercaya.</p>
        </div>
        <a
          href="#"
          class="flex items-center gap-1.5 text-teal text-sm font-semibold whitespace-nowrap hover:gap-3 transition-all"
          aria-label="Lihat semua destinasi"
        >
          Lihat Semua
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>
      </div>

      <!-- Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Featured card (left, tall) -->
        <article
          class="relative rounded-2xl overflow-hidden cursor-pointer group h-[460px]"
          :aria-label="`Destinasi: ${featured.name}, ${featured.country}`"
        >
          <img
            :src="featured.image"
            :alt="featured.imageAlt"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
            width="700"
            height="460"
          />
          <!-- Gradient overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-navy/85 via-navy/30 to-transparent" aria-hidden="true" />
          <!-- Content -->
          <div class="absolute bottom-0 left-0 right-0 p-6">
            <span v-if="featured.badge" class="inline-flex items-center gap-1.5 bg-teal text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
              {{ featured.badge }}
            </span>
            <h3 class="text-white font-black text-3xl leading-tight mb-2">
              {{ featured.name }}<span v-if="featured.country">, {{ featured.country }}</span>
            </h3>
            <p v-if="featured.description" class="text-white/75 text-sm mb-4 leading-relaxed">
              {{ featured.description }}
            </p>
            <button class="inline-flex items-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-bold px-5 py-2.5 rounded-full transition-all hover:-translate-y-0.5 shadow-lg shadow-teal/30">
              Eksplorasi
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </article>

        <!-- 2×2 tiles grid (right) -->
        <div class="grid grid-cols-2 gap-5">
          <article
            v-for="dest in tiles"
            :key="dest.id"
            class="relative rounded-2xl overflow-hidden cursor-pointer group h-[210px]"
            :aria-label="`Destinasi: ${dest.name}`"
          >
            <img
              :src="dest.image"
              :alt="dest.imageAlt"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
              width="400"
              height="210"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-navy/20 to-transparent" aria-hidden="true" />
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <h3 class="text-white font-bold text-base leading-tight">
                {{ dest.name }}<span v-if="dest.country">, {{ dest.country }}</span>
              </h3>
              <p class="text-white/70 text-xs mt-0.5">
                Dari <strong class="text-teal-light font-semibold">{{ dest.price }}</strong>
              </p>
            </div>
          </article>
        </div>

      </div>
    </div>
  </section>
</template>
