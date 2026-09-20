<script setup lang="ts">
interface TrustItem {
  id: number
  icon: string
  iconBg: string
  iconColor: string
  title: string
  description: string
}

const items: TrustItem[] = [
  {
    id: 1,
    icon: '🛡️',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
    title: 'Ultimate Trust',
    description: 'Sertifikasi Secure Payment menjamin transaksi impian Anda 100% aman dan terlindungi.',
  },
  {
    id: 2,
    icon: '⚡',
    iconBg: 'bg-teal/10',
    iconColor: 'text-teal',
    title: 'Instant Booking',
    description: 'Konfirmasi instan otomatis tanpa menunggu lama. Pesan sekarang, terbang hari ini.',
  },
  {
    id: 3,
    icon: '⭐',
    iconBg: 'bg-purple-50',
    iconColor: 'text-purple-600',
    title: 'Premium Comfort',
    description: 'Pilihan partner terbaik yang telah dikurasi untuk kenyamanan perjalanan Anda.',
  },
]
</script>

<template>
  <section class="py-16 bg-white" aria-label="Keunggulan Jelajahin">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <article
          v-for="item in items"
          :key="item.id"
          class="text-center px-8 py-10 rounded-2xl border border-gray-100 hover:-translate-y-1 hover:shadow-lg hover:border-teal/20 transition-all duration-300"
          :aria-label="`Keunggulan: ${item.title}`"
        >
          <!-- Icon -->
          <div
            :class="['w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 text-2xl', item.iconBg]"
            aria-hidden="true"
          >
            {{ item.icon }}
          </div>
          <h3 class="text-navy font-bold text-lg mb-2.5">{{ item.title }}</h3>
          <p class="text-gray-500 text-sm leading-relaxed">{{ item.description }}</p>
        </article>
      </div>
    </div>
  </section>
</template>
