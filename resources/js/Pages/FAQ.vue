<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'

// ─── Kategori & FAQ data ───────────────────────────────────────────────────────
const categories = [
  {
    id: 'pemesanan',
    label: 'Pemesanan Tiket',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>`,
    faqs: [
      {
        q: 'Bagaimana cara memesan tiket pesawat di Jelajahin?',
        a: 'Pilih kota asal dan tujuan, tentukan tanggal keberangkatan, lalu klik "Ayo Cari". Setelah hasil muncul, pilih penerbangan yang sesuai dan ikuti langkah pengisian data penumpang serta pembayaran.',
      },
      {
        q: 'Apakah saya bisa memesan untuk orang lain?',
        a: 'Ya, Anda bisa memesan tiket untuk orang lain. Pastikan data yang diisi (nama, NIK/nomor paspor) sesuai dengan identitas penumpang yang akan terbang.',
      },
      {
        q: 'Berapa lama proses konfirmasi booking?',
        a: 'Konfirmasi booking biasanya langsung diterima setelah pembayaran berhasil. E-tiket akan dikirim ke email Anda dalam waktu maksimal 15 menit.',
      },
      {
        q: 'Apakah ada batas waktu untuk menyelesaikan pembayaran?',
        a: 'Ya, Anda memiliki waktu 30 menit untuk menyelesaikan pembayaran setelah booking dibuat. Jika melewati batas waktu, booking akan otomatis dibatalkan.',
      },
    ],
  },
  {
    id: 'pembayaran',
    label: 'Pembayaran & Harga',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>`,
    faqs: [
      {
        q: 'Metode pembayaran apa saja yang tersedia?',
        a: 'Jelajahin menerima berbagai metode pembayaran: kartu kredit/debit (Visa, Mastercard), transfer bank, dompet digital (GoPay, OVO, Dana), dan QRIS.',
      },
      {
        q: 'Apakah harga yang ditampilkan sudah termasuk pajak?',
        a: 'Harga yang tampil di halaman pencarian adalah harga per orang sebelum pajak. Total akhir yang perlu dibayar (termasuk pajak dan biaya layanan) akan ditampilkan di halaman ringkasan sebelum checkout.',
      },
      {
        q: 'Bisakah saya menggunakan promo atau kode diskon?',
        a: 'Ya, Anda bisa memasukkan kode promo di halaman ringkasan pemesanan. Pastikan kode promo belum kedaluwarsa dan sesuai dengan syarat yang berlaku.',
      },
    ],
  },
  {
    id: 'perubahan',
    label: 'Perubahan & Pembatalan',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>`,
    faqs: [
      {
        q: 'Bagaimana cara membatalkan tiket saya?',
        a: 'Pembatalan tiket dapat dilakukan melalui halaman "Pesanan Saya" di akun Anda. Pilih booking yang ingin dibatalkan, klik "Batalkan", dan ikuti prosedur yang tersedia. Kebijakan refund tergantung pada ketentuan maskapai.',
      },
      {
        q: 'Apakah bisa reschedule penerbangan?',
        a: 'Reschedule tersedia untuk beberapa maskapai dan kelas tiket. Biaya reschedule tergantung pada kebijakan maskapai dan selisih harga tiket.',
      },
      {
        q: 'Berapa lama proses refund setelah pembatalan?',
        a: 'Proses refund memakan waktu 7–14 hari kerja tergantung metode pembayaran dan kebijakan maskapai. Refund ke kartu kredit biasanya 5–7 hari kerja.',
      },
    ],
  },
  {
    id: 'etiket',
    label: 'E-Ticket & Boarding',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
    faqs: [
      {
        q: 'Bagaimana cara mengunduh e-tiket saya?',
        a: 'E-tiket dapat diunduh dari email konfirmasi atau melalui halaman "Pesanan Saya" di akun Anda. Klik tombol "Unduh E-Tiket" untuk mendapatkan file PDF.',
      },
      {
        q: 'Apakah e-tiket digital diterima saat check-in?',
        a: 'Ya, e-tiket digital (PDF atau tampilan di layar ponsel) diterima di semua bandara. Pastikan layar tidak terkunci saat petugas melakukan pemindaian.',
      },
      {
        q: 'Kapan bisa melakukan check-in online?',
        a: 'Check-in online biasanya dibuka 24–48 jam sebelum jadwal penerbangan, tergantung maskapai. Anda akan menerima notifikasi email saat check-in sudah bisa dilakukan.',
      },
    ],
  },
]

// ─── State ────────────────────────────────────────────────────────────────────
const activeCategory = ref('pemesanan')
const openFaq = ref<string | null>(null)
const searchQuery = ref('')

// ─── Computed ─────────────────────────────────────────────────────────────────
const currentCategory = computed(
  () => categories.find(c => c.id === activeCategory.value) ?? categories[0]
)

const filteredFaqs = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return currentCategory.value.faqs
  return currentCategory.value.faqs.filter(
    f => f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q)
  )
})

const searchResults = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return []
  return categories.flatMap(cat =>
    cat.faqs
      .filter(f => f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q))
      .map(f => ({ ...f, catLabel: cat.label, catId: cat.id }))
  )
})

const isSearching = computed(() => searchQuery.value.trim().length > 0)

// ─── Helpers ─────────────────────────────────────────────────────────────────
function toggleFaq(id: string) {
  openFaq.value = openFaq.value === id ? null : id
}

function selectSearchResult(catId: string, q: string) {
  activeCategory.value = catId
  searchQuery.value = ''
  openFaq.value = q
}
</script>

<template>
  <Head title="Pusat Bantuan – Jelajahin" />

  <div class="min-h-screen bg-gray-50 font-sans">
    <Navbar :transparent="false" />

    <!-- ═══ HERO ════════════════════════════════════════════════════════════════ -->
    <section class="bg-gray-50 pt-24 pb-14 px-6">
      <div class="max-w-3xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight">
          Pusat Bantuan &<br />
          <span class="text-teal">Pertanyaan Umum</span>
        </h1>
        <p class="mt-4 text-gray-500 text-base">
          Temukan jawaban untuk pertanyaan Anda
        </p>

        <!-- Search bar -->
        <div class="mt-8 relative max-w-xl mx-auto">
          <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Cari pertanyaan..."
            class="w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal shadow-sm transition-all"
            aria-label="Cari pertanyaan"
          />

          <!-- Search dropdown results -->
          <div
            v-if="isSearching && searchResults.length > 0"
            class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-20 overflow-hidden"
          >
            <button
              v-for="result in searchResults.slice(0, 5)"
              :key="result.q"
              class="w-full text-left px-5 py-3.5 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors"
              @click="selectSearchResult(result.catId, result.q)"
            >
              <p class="text-sm font-semibold text-gray-800 truncate">{{ result.q }}</p>
              <p class="text-xs text-teal mt-0.5">{{ result.catLabel }}</p>
            </button>
          </div>
          <div
            v-else-if="isSearching && searchResults.length === 0"
            class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-20 px-5 py-4"
          >
            <p class="text-sm text-gray-400 text-center">Tidak ada pertanyaan yang cocok.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CONTENT ════════════════════════════════════════════════════════════ -->
    <main class="max-w-6xl mx-auto px-6 pb-20">
      <div class="flex flex-col lg:flex-row gap-8 items-start">

        <!-- ── Sidebar kategori ──────────────────────────────────────────────── -->
        <aside class="w-full lg:w-64 shrink-0 lg:sticky lg:top-24">
          <h2 class="text-base font-black text-gray-800 mb-4">Kategori Topik</h2>
          <nav class="flex flex-col gap-1" aria-label="Kategori FAQ">
            <button
              v-for="cat in categories"
              :key="cat.id"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-left transition-all',
                activeCategory === cat.id
                  ? 'bg-teal/10 text-teal border border-teal/20 font-semibold'
                  : 'text-gray-600 hover:bg-gray-100 border border-transparent',
              ]"
              @click="activeCategory = cat.id; openFaq = null; searchQuery = ''"
            >
              <!-- icon -->
              <span
                :class="activeCategory === cat.id ? 'text-teal' : 'text-gray-400'"
                v-html="cat.icon"
              />
              {{ cat.label }}
            </button>
          </nav>
        </aside>

        <!-- ── FAQ Accordion ─────────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 space-y-5">

          <!-- Category title -->
          <div class="flex items-center gap-3 mb-2">
            <span class="text-gray-400" v-html="currentCategory.icon" />
            <h2 class="text-xl font-black text-gray-900">{{ currentCategory.label }}</h2>
          </div>

          <!-- FAQ items -->
          <div class="space-y-3">
            <template v-if="filteredFaqs.length > 0">
              <div
                v-for="faq in filteredFaqs"
                :key="faq.q"
                class="bg-white rounded-2xl border transition-all overflow-hidden"
                :class="openFaq === faq.q ? 'border-teal/30 shadow-sm' : 'border-gray-100 hover:border-gray-200'"
              >
                <!-- Question row -->
                <button
                  class="w-full flex items-center justify-between px-6 py-5 text-left gap-4"
                  :aria-expanded="openFaq === faq.q"
                  @click="toggleFaq(faq.q)"
                >
                  <span class="text-sm font-semibold text-gray-800 leading-snug">{{ faq.q }}</span>
                  <span
                    class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center transition-all"
                    :class="openFaq === faq.q ? 'bg-teal text-white rotate-180' : 'bg-gray-100 text-gray-400'"
                  >
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path d="M6 9l6 6 6-6"/>
                    </svg>
                  </span>
                </button>

                <!-- Answer -->
                <Transition
                  enter-active-class="transition-all duration-200 ease-out"
                  enter-from-class="opacity-0 max-h-0"
                  enter-to-class="opacity-100 max-h-96"
                  leave-active-class="transition-all duration-150 ease-in"
                  leave-from-class="opacity-100 max-h-96"
                  leave-to-class="opacity-0 max-h-0"
                >
                  <div v-if="openFaq === faq.q" class="px-6 pb-5 overflow-hidden">
                    <div class="h-px bg-gray-100 mb-4" />
                    <p class="text-sm text-gray-600 leading-relaxed">{{ faq.a }}</p>
                  </div>
                </Transition>
              </div>
            </template>
            <div v-else class="text-center py-12 text-gray-400 text-sm">
              Tidak ada FAQ untuk kategori ini.
            </div>
          </div>

          <!-- ── Contact card ────────────────────────────────────────────────── -->
          <div class="mt-8 rounded-2xl overflow-hidden bg-gradient-to-br from-navy to-navy-mid border border-white/5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 p-7">

              <!-- Left text -->
              <div class="flex-1">
                <h3 class="text-lg font-black text-white">Masih ada pertanyaan?</h3>
                <p class="text-sm text-white/60 mt-1 leading-relaxed">
                  Jika Anda tidak menemukan jawaban, hubungi tim support kami yang siap membantu 24/7.
                </p>
              </div>

              <!-- Buttons -->
              <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                <a
                  href="mailto:support@jelajahin.com"
                  class="flex items-center gap-2 px-5 py-3 bg-teal hover:bg-teal-dark text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-teal/25 hover:-translate-y-px"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  Hubungi Kami
                </a>
                <a
                  href="#"
                  class="flex items-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/15 text-white text-sm font-semibold rounded-xl border border-white/15 transition-all hover:-translate-y-px"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                  </svg>
                  Live Chat
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<style scoped>
/* Smooth accordion height transition */
.transition-all {
  transition-property: all;
}
</style>
