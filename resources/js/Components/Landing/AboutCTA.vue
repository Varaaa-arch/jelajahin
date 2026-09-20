<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <section
    class="py-20 bg-gray-50"
    aria-label="Mulai Perjalanan Anda"
  >
    <div class="max-w-6xl mx-auto px-6">
      <div class="bg-white rounded-3xl border border-gray-200 shadow-sm px-8 py-16 text-center max-w-2xl mx-auto">

        <h2 class="text-3xl font-extrabold text-[#1a3a2e] mb-4 tracking-tight">
          Siap Memulai Perjalanan Anda?
        </h2>
        <p class="text-gray-500 text-base mb-10 leading-relaxed">
          Bergabunglah dengan jutaan pelancong yang telah mempercayakan
          perjalanan mereka kepada Jelajahin.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <!-- Primary CTA -->
          <Link
            :href="route('flights.search')"
            class="inline-flex items-center gap-2.5 bg-[#0f3028] hover:bg-[#1a3a2e] text-white font-bold text-sm px-8 py-3.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-lg shadow-[#0f3028]/20"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
            </svg>
            Cari Penerbangan
          </Link>

          <!-- Secondary CTA -->
          <a
            href="mailto:hello@jelajahin.com"
            class="inline-flex items-center gap-2.5 border-2 border-[#0f3028] text-[#0f3028] hover:bg-[#0f3028] hover:text-white font-bold text-sm px-8 py-3.5 rounded-xl transition-all hover:-translate-y-0.5"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
            </svg>
            Hubungi Kami
          </a>
        </div>

      </div>
    </div>
  </section>
</template>
