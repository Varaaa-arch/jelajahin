<script setup lang="ts">
const footerLinks = {
  perusahaan: [
    { label: 'Tentang Kami',       href: '/about' },
    { label: 'Syarat & Ketentuan', href: '/terms' },
    { label: 'Kebijakan Privasi',  href: '/privacy' },
  ],
  produk: [
    { label: 'Tiket Pesawat',      href: '/flights/search' },
    { label: 'Booking Hotel',      href: '#' },
    { label: 'Tiket Kereta Api',   href: '#' },
    { label: 'Sewa Mobil',         href: '#' },
    { label: 'Atraksi & Aktivitas',href: '#' },
  ],
  layanan: [
    { label: 'Pusat Bantuan (FAQ)', href: '/faq' },
    { label: 'Hubungi Kami',        href: '/contact' },
    { label: 'Promo Hari Ini',      href: '#' },
    { label: 'PayLater',            href: '#' },
    { label: 'Cek Pesanan',         href: '#' },
  ],
}
</script>

<template>
  <footer class="bg-navy-mid border-t border-white/5" role="contentinfo" aria-label="Footer Jelajahin">
    <div class="max-w-7xl mx-auto px-6">

      <!-- Main grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr] gap-10 py-14">

        <!-- Brand -->
        <div>
          <span class="text-white font-black text-xl block mb-3">Jelajahin</span>
          <p class="text-white/50 text-sm leading-relaxed mb-5">
            Memudahkan Anda menuju destinasi impian dengan cara yang
            lebih mudah, aman, dan terpercaya.
          </p>
          <!-- Quick links pills -->
          <div class="flex flex-wrap gap-2">
            <a
              href="/faq"
              class="inline-flex items-center gap-1.5 text-xs text-white/50 bg-white/5 hover:bg-teal/20 hover:text-teal-light border border-white/8 hover:border-teal/30 px-3 py-1.5 rounded-full transition-all"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>
              </svg>
              FAQ
            </a>
            <a
              href="/contact"
              class="inline-flex items-center gap-1.5 text-xs text-white/50 bg-white/5 hover:bg-teal/20 hover:text-teal-light border border-white/8 hover:border-teal/30 px-3 py-1.5 rounded-full transition-all"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              Kontak
            </a>
            <a
              href="/terms"
              class="inline-flex items-center gap-1.5 text-xs text-white/50 bg-white/5 hover:bg-teal/20 hover:text-teal-light border border-white/8 hover:border-teal/30 px-3 py-1.5 rounded-full transition-all"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              S&K
            </a>
            <a
              href="/privacy"
              class="inline-flex items-center gap-1.5 text-xs text-white/50 bg-white/5 hover:bg-teal/20 hover:text-teal-light border border-white/8 hover:border-teal/30 px-3 py-1.5 rounded-full transition-all"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              Privasi
            </a>
          </div>
        </div>

        <!-- Perusahaan -->
        <div>
          <h4 class="text-white text-sm font-bold mb-4 tracking-wide">Perusahaan</h4>
          <nav class="flex flex-col gap-3" aria-label="Tautan Perusahaan">
            <a
              v-for="link in footerLinks.perusahaan"
              :key="link.label"
              :href="link.href"
              class="text-white/50 text-sm hover:text-teal-light transition-colors"
            >
              {{ link.label }}
            </a>
          </nav>
        </div>

        <!-- Produk -->
        <div>
          <h4 class="text-white text-sm font-bold mb-4 tracking-wide">Produk</h4>
          <nav class="flex flex-col gap-3" aria-label="Tautan Produk">
            <a
              v-for="link in footerLinks.produk"
              :key="link.label"
              :href="link.href"
              class="text-white/50 text-sm hover:text-teal-light transition-colors"
            >
              {{ link.label }}
            </a>
          </nav>
        </div>

        <!-- Layanan & Bantuan -->
        <div>
          <h4 class="text-white text-sm font-bold mb-4 tracking-wide">Layanan & Bantuan</h4>
          <nav class="flex flex-col gap-3" aria-label="Tautan Layanan">
            <a
              v-for="link in footerLinks.layanan"
              :key="link.label"
              :href="link.href"
              :class="[
                'text-sm transition-colors flex items-center gap-1.5',
                link.href !== '#'
                  ? 'text-white/70 hover:text-teal-light font-medium'
                  : 'text-white/50 hover:text-teal-light',
              ]"
            >
              <!-- dot indicator untuk page yang sudah ada -->
              <span
                v-if="link.href !== '#'"
                class="w-1 h-1 rounded-full bg-teal/60 shrink-0"
              />
              {{ link.label }}
            </a>
          </nav>
        </div>

      </div>

      <!-- Bottom bar -->
      <div class="border-t border-white/7 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-center gap-4 order-last sm:order-first">
          <p class="text-white/30 text-xs">
            &copy; {{ new Date().getFullYear() }} Jelajahin.com — Semua Hak Cipta Dilindungi.
          </p>
          <!-- Legal links inline -->
          <div class="flex items-center gap-3 text-xs text-white/25">
            <span class="hidden sm:block">·</span>
            <a href="/terms"   class="hover:text-white/60 transition-colors">Syarat & Ketentuan</a>
            <span>·</span>
            <a href="/privacy" class="hover:text-white/60 transition-colors">Kebijakan Privasi</a>
            <span>·</span>
            <a href="/contact" class="hover:text-white/60 transition-colors">Kontak</a>
          </div>
        </div>

        <!-- Socials -->
        <div class="flex items-center gap-3" aria-label="Media sosial Jelajahin">
          <a href="#" aria-label="Instagram" class="w-8 h-8 rounded-full border border-white/12 flex items-center justify-center text-white/40 hover:text-white hover:border-teal hover:bg-teal/15 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
          </a>
          <a href="#" aria-label="Twitter" class="w-8 h-8 rounded-full border border-white/12 flex items-center justify-center text-white/40 hover:text-white hover:border-teal hover:bg-teal/15 transition-all">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
          </a>
          <a href="#" aria-label="Facebook" class="w-8 h-8 rounded-full border border-white/12 flex items-center justify-center text-white/40 hover:text-white hover:border-teal hover:bg-teal/15 transition-all">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>
          <a href="#" aria-label="Ganti bahasa" class="flex items-center gap-1.5 text-white/35 text-xs hover:text-white/60 transition-colors ml-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
            </svg>
            Indonesia (ID)
          </a>
        </div>
      </div>

    </div>
  </footer>
</template>
