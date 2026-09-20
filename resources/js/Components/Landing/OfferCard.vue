<script setup lang="ts">
export interface Offer {
  id: number
  title: string
  description: string
  price: string
  image: string
  imageAlt: string
  badge: string
  badgeVariant: 'green' | 'blue' | 'yellow'
  href: string
}

defineProps<{ offer: Offer }>()

const badgeClasses: Record<string, string> = {
  green: 'bg-emerald-500/90',
  blue:  'bg-blue-500/90',
  yellow: 'bg-amber-500/90',
}
</script>

<template>
  <article
    class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 cursor-pointer group"
    :aria-label="`Penawaran: ${offer.title}`"
  >
    <!-- Image -->
    <div class="relative h-48 overflow-hidden">
      <img
        :src="offer.image"
        :alt="offer.imageAlt"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
        width="600"
        height="192"
      />
      <!-- Badge -->
      <span
        :class="['absolute top-3 left-3 flex items-center gap-1.5 text-white text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm', badgeClasses[offer.badgeVariant]]"
      >
        {{ offer.badge }}
      </span>
    </div>

    <!-- Body -->
    <div class="p-5">
      <h3 class="font-bold text-navy text-base mb-1.5">{{ offer.title }}</h3>
      <p class="text-sm text-gray-500">
        {{ offer.description }}
        <strong class="text-teal font-semibold">{{ offer.price }}</strong>
      </p>
      <a
        :href="offer.href"
        class="inline-flex items-center gap-1.5 mt-3.5 text-teal text-sm font-semibold hover:gap-3 transition-all"
        :aria-label="`Lihat detail: ${offer.title}`"
      >
        Lihat Detail
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </a>
    </div>
  </article>
</template>
