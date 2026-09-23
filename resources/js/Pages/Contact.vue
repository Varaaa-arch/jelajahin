<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'

// ─── Contact form state ───────────────────────────────────────────────────────
const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  agree: false,
})

const subjects = [
  'Pilih subjek pesan',
  'Pemesanan Tiket',
  'Pembayaran & Refund',
  'Perubahan & Pembatalan',
  'E-Ticket & Boarding',
  'Masalah Teknis',
  'Lainnya',
]

const dragOver  = ref(false)
const fileName  = ref('')
const submitted = ref(false)
const loading   = ref(false)
const error     = ref('')

function handleDrop(e: DragEvent) {
  dragOver.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) fileName.value = file.name
}

function handleFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) fileName.value = file.name
}

async function handleSubmit() {
  if (!form.name || !form.email || !form.message || !form.agree) {
    error.value = 'Mohon lengkapi semua field wajib dan setujui kebijakan privasi.'
    return
  }
  error.value = ''
  loading.value = true
  // Simulasi kirim
  await new Promise(r => setTimeout(r, 1200))
  loading.value = false
  submitted.value = true
}

// ─── Channel cards ────────────────────────────────────────────────────────────
const channels = [
  {
    id: 'chat',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>`,
    title: 'Live Chat',
    desc: 'Chat langsung dengan tim support kami dalam hitungan detik.',
    badge: 'Available sekarang',
    badgeGreen: true,
    meta: '24/7',
    metaIcon: 'clock',
    cta: 'Mulai Chat',
    ctaDark: true,
    href: '#chat',
  },
  {
    id: 'email',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`,
    title: 'Email',
    desc: 'Email kami untuk pertanyaan yang lebih detail.',
    badge: null,
    badgeGreen: false,
    meta: 'Balasan dalam 4 jam',
    metaIcon: 'clock',
    value: 'support@jelajahin.com',
    cta: 'Kirim Email',
    ctaDark: false,
    href: 'mailto:support@jelajahin.com',
  },
  {
    id: 'phone',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>`,
    title: 'Telepon',
    desc: 'Hubungi kami melalui telepon untuk bantuan cepat.',
    badge: null,
    badgeGreen: false,
    meta: 'Sen–Jum: 09:00–18:00',
    metaIcon: 'clock',
    value: '+62 21 5555-XXXX',
    cta: 'Hubungi Sekarang',
    ctaDark: false,
    href: 'tel:+622155550000',
  },
  {
    id: 'whatsapp',
    icon: `<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>`,
    title: 'WhatsApp',
    desc: 'Hubungi kami via WhatsApp untuk respons yang cepat.',
    badge: null,
    badgeGreen: false,
    meta: '24/7',
    metaIcon: 'clock',
    value: '+62 821 XXXX-XXXX',
    cta: 'Chat WhatsApp',
    ctaGreen: true,
    href: 'https://wa.me/628210000000',
  },
]

// ─── Office data ──────────────────────────────────────────────────────────────
const offices = [
  {
    city: 'Jakarta',
    badge: 'HQ',
    address: 'Gedung Jelajahin Tower Lt. 12\nJl. Jend. Sudirman Kav. 50\nJakarta Selatan 12930',
    phone: '+62 21 555 1234',
    hours: 'Sen – Jum: 08:00 – 17:00',
    mapsUrl: 'https://maps.google.com/?q=Sudirman+Jakarta',
  },
  {
    city: 'Surabaya',
    badge: null,
    address: 'Jelajahin Hub Surabaya\nJl. Basuki Rahmat No. 101\nSurabaya Pusat 60271',
    phone: '+62 31 555 5678',
    hours: 'Sen – Jum: 08:00 – 17:00',
    mapsUrl: 'https://maps.google.com/?q=Basuki+Rahmat+Surabaya',
  },
  {
    city: 'Bali',
    badge: null,
    address: 'Jelajahin Lounge Bali\nJl. Sunset Road No. 88\nKuta, Bali 80361',
    phone: '+62 361 555 9012',
    hours: 'Sen – Min: 09:00 – 20:00',
    mapsUrl: 'https://maps.google.com/?q=Sunset+Road+Kuta+Bali',
  },
]

// ─── Quick FAQ ────────────────────────────────────────────────────────────────
const quickFaqs = [
  { q: 'Bagaimana cara booking?', a: 'Panduan langkah demi langkah.' },
  { q: 'Berapa tarif pembatalan?', a: 'Kebijakan refund dan biaya.' },
  { q: 'Bagaimana jika ada masalah teknis?', a: 'Solusi error pada aplikasi.' },
]
</script>

<template>
  <Head title="Hubungi Kami – Jelajahin" />

  <div class="min-h-screen bg-gray-50 font-sans">
    <Navbar :transparent="false" />

    <!-- ═══ HERO ════════════════════════════════════════════════════════════════ -->
    <section class="relative pt-16 overflow-hidden">
      <!-- Background gradient teal-to-white -->
      <div class="absolute inset-0 bg-gradient-to-b from-teal/80 via-teal/30 to-gray-50 pointer-events-none" />
      <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1534536281715-e28d76689b4d?w=1600&auto=format&fit=crop&q=60')] bg-cover bg-center opacity-10 pointer-events-none" />

      <div class="relative max-w-3xl mx-auto px-6 py-24 text-center">
        <h1 class="text-4xl md:text-5xl font-black text-white drop-shadow-md">
          Hubungi Kami
        </h1>
        <p class="mt-4 text-white/80 text-base max-w-xl mx-auto leading-relaxed">
          Tim kami siap membantu Anda 24/7. Temukan jawaban cepat atau hubungi tim kami melalui berbagai saluran komunikasi yang tersedia.
        </p>
      </div>
    </section>

    <!-- ═══ CHANNEL CARDS ══════════════════════════════════════════════════════ -->
    <section class="max-w-6xl mx-auto px-6 -mt-8 relative z-10">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="ch in channels"
          :key="ch.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md hover:border-gray-200 transition-all"
        >
          <!-- Icon -->
          <div class="w-11 h-11 bg-teal/10 rounded-xl flex items-center justify-center text-teal" v-html="ch.icon" />

          <!-- Title + desc -->
          <div>
            <h3 class="text-sm font-black text-gray-900">{{ ch.title }}</h3>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ ch.desc }}</p>
          </div>

          <!-- Badge available -->
          <span
            v-if="ch.badge"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full w-fit"
          >
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse" />
            {{ ch.badge }}
          </span>

          <!-- Value (email / phone) -->
          <p v-if="ch.value" class="text-xs font-bold text-gray-700">{{ ch.value }}</p>

          <!-- Meta -->
          <p class="text-xs text-gray-400 flex items-center gap-1 mt-auto">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
            {{ ch.meta }}
          </p>

          <!-- CTA button -->
          <a
            :href="ch.href"
            :class="[
              'mt-1 w-full text-center text-xs font-bold py-2.5 rounded-xl transition-all',
              ch.ctaDark
                ? 'bg-navy text-white hover:bg-navy-mid'
                : ch.ctaGreen
                  ? 'bg-green-500 hover:bg-green-600 text-white'
                  : 'border border-gray-200 text-gray-700 hover:bg-gray-50',
            ]"
          >
            {{ ch.cta }}
          </a>
        </div>
      </div>
    </section>

    <!-- ═══ FORM + SIDEBAR ═════════════════════════════════════════════════════ -->
    <section class="max-w-6xl mx-auto px-6 py-16">
      <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-8 items-start">

        <!-- ── Contact Form ───────────────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">

          <!-- Success state -->
          <div v-if="submitted" class="text-center py-12">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900">Pesan Terkirim!</h3>
            <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto">
              Tim kami akan menghubungi Anda dalam waktu 4 jam melalui email yang didaftarkan.
            </p>
            <button
              class="mt-6 px-6 py-2.5 bg-teal text-white text-sm font-bold rounded-xl hover:bg-teal-dark transition-all"
              @click="submitted = false"
            >
              Kirim Pesan Lain
            </button>
          </div>

          <template v-else>
            <h2 class="text-xl font-black text-gray-900 mb-1">Kirim Pesan Kepada Kami</h2>
            <p class="text-sm text-gray-500 mb-7">Isi form di bawah dan kami akan menghubungi Anda segera.</p>

            <form class="space-y-5" @submit.prevent="handleSubmit">

              <!-- Row 1: Nama + Email -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="name">
                    Nama Lengkap <span class="text-red-400">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan nama Anda"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="email">
                    Alamat Email <span class="text-red-400">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="email@contoh.com"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all"
                  />
                </div>
              </div>

              <!-- Row 2: Phone + Subject -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="phone">
                    Nomor Telepon
                  </label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    placeholder="+62 812 XXXX XXXX"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="subject">
                    Subjek
                  </label>
                  <select
                    id="subject"
                    v-model="form.subject"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all bg-white"
                  >
                    <option v-for="s in subjects" :key="s" :value="s === subjects[0] ? '' : s">
                      {{ s }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Message -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="message">
                  Pesan Anda <span class="text-red-400">*</span>
                </label>
                <textarea
                  id="message"
                  v-model="form.message"
                  rows="5"
                  placeholder="Jelaskan detail pertanyaan atau masalah Anda di sini..."
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all resize-none"
                />
              </div>

              <!-- File upload -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                  Lampiran <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <label
                  class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-6 py-6 cursor-pointer transition-all"
                  :class="dragOver ? 'border-teal bg-teal/5' : 'border-gray-200 hover:border-teal/50 hover:bg-gray-50'"
                  @dragover.prevent="dragOver = true"
                  @dragleave="dragOver = false"
                  @drop.prevent="handleDrop"
                >
                  <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  <p class="text-xs text-gray-500 text-center">
                    <span v-if="fileName" class="font-semibold text-teal">{{ fileName }}</span>
                    <span v-else>Klik atau seret file ke sini untuk mengunggah<br/><span class="text-gray-400">Maks 5MB. JPG, PNG, atau PDF.</span></span>
                  </p>
                  <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="handleFileInput" />
                </label>
              </div>

              <!-- Agreement -->
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  v-model="form.agree"
                  type="checkbox"
                  class="mt-0.5 w-4 h-4 rounded border-gray-300 text-teal focus:ring-teal/30 cursor-pointer shrink-0"
                />
                <span class="text-xs text-gray-500 leading-relaxed">
                  Saya menyetujui
                  <a href="/privacy" class="text-teal hover:underline">Kebijakan Privasi</a>
                  dan menyetujui data saya diproses untuk keperluan layanan pelanggan.
                </span>
              </label>

              <!-- Error -->
              <div v-if="error" class="bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                <p class="text-xs text-red-500">{{ error }}</p>
              </div>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex items-center justify-center gap-2 bg-navy hover:bg-navy-mid disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm py-3.5 rounded-xl transition-all shadow-lg shadow-navy/20 hover:-translate-y-px"
              >
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <template v-else>
                  Kirim Pesan
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                </template>
              </button>

            </form>
          </template>
        </div>

        <!-- ── Sidebar ─────────────────────────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Respon Cepat card -->
          <div class="bg-teal rounded-2xl p-6 text-white">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
              </div>
              <h3 class="font-black text-base">Respon Cepat</h3>
            </div>
            <p class="text-sm text-white/80 leading-relaxed">
              Kami berusaha memberikan respons terbaik. Rata-rata waktu tunggu Live Chat & WhatsApp &lt; 5 menit.
            </p>

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-3 mt-5">
              <div class="bg-white/15 rounded-xl p-3 text-center">
                <p class="text-lg font-black">&lt;5m</p>
                <p class="text-xs text-white/70 mt-0.5">Live Chat</p>
              </div>
              <div class="bg-white/15 rounded-xl p-3 text-center">
                <p class="text-lg font-black">&lt;4j</p>
                <p class="text-xs text-white/70 mt-0.5">Email</p>
              </div>
              <div class="bg-white/15 rounded-xl p-3 text-center">
                <p class="text-lg font-black">24/7</p>
                <p class="text-xs text-white/70 mt-0.5">WA</p>
              </div>
            </div>
          </div>

          <!-- Quick FAQ card -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-gray-900 text-sm mb-4 flex items-center gap-2">
              <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>
              </svg>
              Pertanyaan Umum
            </h3>
            <div class="space-y-3">
              <a
                v-for="faq in quickFaqs"
                :key="faq.q"
                href="/faq"
                class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group"
              >
                <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-teal/10 transition-colors">
                  <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-teal transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-800 leading-snug">{{ faq.q }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ faq.a }}</p>
                </div>
              </a>
            </div>
            <Link
              href="/faq"
              class="flex items-center gap-1 text-xs font-bold text-teal hover:underline mt-4"
            >
              Lihat Semua FAQ
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>

          <!-- Operating hours -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-gray-900 text-sm mb-4 flex items-center gap-2">
              <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
              </svg>
              Jam Operasional
            </h3>
            <div class="space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-500">Senin – Jumat</span>
                <span class="font-semibold text-gray-800">08:00 – 17:00</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Sabtu</span>
                <span class="font-semibold text-gray-800">09:00 – 14:00</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Minggu</span>
                <span class="font-semibold text-red-400">Tutup</span>
              </div>
              <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shrink-0" />
                <span class="text-gray-500">Live Chat & WhatsApp tersedia <strong class="text-gray-700">24/7</strong></span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ═══ KANTOR KAMI ════════════════════════════════════════════════════════ -->
    <section class="bg-white border-t border-gray-100 py-20">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-10">
          <h2 class="text-3xl font-black text-gray-900">Kantor Kami</h2>
          <p class="text-gray-500 text-sm mt-2">Kunjungi kantor cabang kami untuk layanan langsung.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="office in offices"
            :key="office.city"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-gray-200 transition-all group"
          >
            <!-- Map placeholder -->
            <div class="relative h-44 bg-gradient-to-br from-teal/10 to-teal/5 overflow-hidden">
              <!-- Decorative map pattern -->
              <div class="absolute inset-0 opacity-20">
                <div class="absolute top-1/3 left-1/4 w-24 h-px bg-gray-400 rotate-12" />
                <div class="absolute top-1/2 left-1/3 w-32 h-px bg-gray-400 -rotate-6" />
                <div class="absolute top-2/3 left-1/5 w-20 h-px bg-gray-400 rotate-45" />
                <div class="absolute top-1/4 right-1/4 w-28 h-px bg-gray-400 rotate-3" />
                <div class="absolute top-1/2 right-1/3 w-16 h-px bg-gray-400 -rotate-12" />
              </div>
              <!-- Pin -->
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="flex flex-col items-center">
                  <div class="w-10 h-10 bg-teal rounded-full flex items-center justify-center shadow-lg shadow-teal/30">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                  </div>
                  <div class="w-px h-4 bg-teal/50" />
                  <div class="w-2 h-1 bg-teal/30 rounded-full" />
                </div>
              </div>
              <!-- City label overlay -->
              <div class="absolute top-3 left-3">
                <span class="text-xs font-black text-teal bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-lg shadow-sm">
                  {{ office.city }}
                </span>
              </div>
            </div>

            <!-- Info -->
            <div class="p-5 space-y-3">
              <div class="flex items-center gap-2">
                <h3 class="text-base font-black text-gray-900">{{ office.city }}</h3>
                <span
                  v-if="office.badge"
                  class="text-xs font-bold text-teal bg-teal/10 px-2 py-0.5 rounded-full"
                >
                  {{ office.badge }}
                </span>
              </div>

              <p class="text-xs text-gray-500 leading-relaxed whitespace-pre-line">{{ office.address }}</p>

              <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-xs text-gray-600">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  {{ office.phone }}
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                  </svg>
                  {{ office.hours }}
                </div>
              </div>

              <a
                :href="office.mapsUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-2 w-full flex items-center justify-center gap-2 border border-gray-200 text-gray-700 text-xs font-semibold py-2.5 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Lihat di Maps
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AppFooter />
  </div>
</template>
