<script setup lang="ts">
// Static content
</script>

<template>
  <section class="py-20 bg-white" aria-label="Cerita Kami">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

        <!-- Text -->
        <div>
          <h2 class="text-3xl font-extrabold text-[#1a3a2e] mb-6 tracking-tight">
            Cerita Kami
          </h2>
          <div class="space-y-5 text-gray-600 text-base leading-relaxed">
            <p>
              Didirikan pada tahun 2020 di jantung kota Jakarta, Jelajahin lahir dari
              sebuah visi sederhana: membuat perjalanan udara dapat diakses oleh
              semua orang. Kami menyadari bahwa merencanakan perjalanan seringkali
              menjadi proses yang rumit dan membuat stres.
            </p>
            <p>
              Berawal dari tim kecil yang terdiri dari para penggemar perjalanan dan
              ahli teknologi, kami membangun platform yang mengutamakan kemudahan
              pengguna, transparansi harga, dan layanan pelanggan yang luar biasa.
              Saat ini, kami telah berkembang menjadi salah satu platform pemesanan
              penerbangan terkemuka di Asia Tenggara.
            </p>
          </div>
        </div>

        <!-- Photo -->
        <div class="relative">
          <!-- Decorative border -->
          <div class="absolute -inset-2 rounded-2xl bg-gradient-to-br from-teal/20 to-[#1a3a2e]/10 blur-sm" aria-hidden="true" />
          <div class="relative rounded-2xl overflow-hidden shadow-xl">
            <img
              src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=700&auto=format&fit=crop&q=80"
              alt="Tim Jelajahin sedang berdiskusi di kantor"
              class="w-full h-[360px] object-cover"
              loading="lazy"
              width="700"
              height="360"
            />
            <!-- Logo watermark overlay -->
            <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm rounded-xl px-4 py-2 shadow-md">
              <span class="font-black text-[#1a3a2e] text-sm tracking-tight">Jelajahin</span>
              <span class="block text-xs text-gray-400">Indonesia</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
