<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'

const lastUpdated   = '15 November 2024'
const effectiveDate = '1 Desember 2024'
const activeSection = ref('pendahuluan')

const sections = [
  {
    id: 'pendahuluan',
    label: 'Pendahuluan',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>`,
  },
  {
    id: 'informasi',
    label: 'Informasi Dikumpulkan',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
  },
  {
    id: 'penggunaan',
    label: 'Penggunaan Data',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`,
  },
  {
    id: 'keamanan',
    label: 'Keamanan',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
  },
  {
    id: 'cookie',
    label: 'Kebijakan Cookie',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>`,
  },
  {
    id: 'hak',
    label: 'Hak Privasi',
    icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>`,
  },
]

function handleScroll() {
  for (let i = sections.length - 1; i >= 0; i--) {
    const el = document.getElementById(sections[i].id)
    if (el && el.getBoundingClientRect().top <= 120) {
      activeSection.value = sections[i].id
      break
    }
  }
}

function scrollTo(id: string) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  activeSection.value = id
}

onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

// Data usage cards
const usageCards = [
  {
    icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>`,
    title: 'Operasional',
    desc: 'Untuk memproses transaksi, mengelola pemesanan tiket, dan menyediakan layanan pelanggan terkait perjalanan Anda.',
  },
  {
    icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>`,
    title: 'Komunikasi',
    desc: 'Untuk mengirim pembaruan penting, konfirmasi pesanan, dan penawaran promosi (jika Anda berlangganan).',
  },
  {
    icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>`,
    title: 'Peningkatan',
    desc: 'Untuk menganalisis perilaku pengguna dalam rangka meningkatkan UI/UX platform dan menyempurnakan fitur produk.',
  },
  {
    icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>`,
    title: 'Kepatuhan Hukum',
    desc: 'Untuk mematuhi kewajiban hukum yang berlaku, mencegah penipuan, dan menyelesaikan sengketa.',
  },
]
</script>

<template>
  <Head title="Kebijakan Privasi – Jelajahin" />

  <div class="min-h-screen bg-gray-50 font-sans">
    <Navbar :transparent="false" />

    <!-- ═══ HERO ════════════════════════════════════════════════════════════════ -->
    <section class="pt-24 pb-12 px-6 bg-gray-50 border-b border-gray-200">
      <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-3">Kebijakan Privasi</h1>
        <p class="text-gray-500 text-sm max-w-md leading-relaxed">
          Kami menghormati dan melindungi privasi Anda. Dokumen ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.
        </p>
        <!-- Meta badges -->
        <div class="flex flex-wrap items-center gap-4 mt-5">
          <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
            Terakhir diperbarui: {{ lastUpdated }}
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            Tanggal efektif: {{ effectiveDate }}
          </span>
        </div>
      </div>
    </section>

    <!-- ═══ CONTENT ════════════════════════════════════════════════════════════ -->
    <div class="max-w-6xl mx-auto px-6 py-14">
      <div class="flex flex-col lg:flex-row gap-8 items-start">

        <!-- ── Sidebar TOC ─────────────────────────────────────────────────── -->
        <aside class="w-full lg:w-56 shrink-0 lg:sticky lg:top-24 print:hidden">
          <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-black text-gray-800 mb-0.5">Daftar Isi</p>
            <p class="text-xs text-gray-400 mb-4">Kebijakan Privasi</p>
            <nav class="flex flex-col gap-0.5" aria-label="Daftar isi">
              <button
                v-for="sec in sections"
                :key="sec.id"
                :class="[
                  'flex items-center gap-2.5 text-left text-xs px-3 py-2.5 rounded-xl transition-all',
                  activeSection === sec.id
                    ? 'bg-teal/10 text-teal font-semibold'
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800',
                ]"
                @click="scrollTo(sec.id)"
              >
                <span
                  :class="activeSection === sec.id ? 'text-teal' : 'text-gray-400'"
                  v-html="sec.icon"
                />
                {{ sec.label }}
              </button>
            </nav>
          </div>
        </aside>

        <!-- ── Main content ─────────────────────────────────────────────────── -->
        <article class="flex-1 min-w-0">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">

              <!-- ─ 1. Pendahuluan ─────────────────────────────────────────── -->
              <section :id="sections[0].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  1. Pendahuluan
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-4">
                  <p>
                    Selamat datang di Jelajahin. Kebijakan Privasi ini mengatur cara kami mengumpulkan, menggunakan, memelihara, dan mengungkapkan informasi yang dikumpulkan dari pengguna (masing-masing disebut <strong>"Pengguna"</strong>) dari situs web Jelajahin <strong>("Situs")</strong>. Kebijakan privasi ini berlaku untuk Situs dan semua produk serta layanan yang ditawarkan oleh Jelajahin Travel.
                  </p>
                  <p>
                    Kami berkomitmen kuat untuk melindungi privasi Anda dan memastikan bahwa data pribadi Anda ditangani dengan cara yang aman dan bertanggung jawab.
                  </p>
                </div>
              </section>

              <!-- ─ 2. Informasi Dikumpulkan ────────────────────────────────── -->
              <section :id="sections[1].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  2. Informasi yang Kami Kumpulkan
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-6">

                  <div>
                    <h3 class="text-sm font-black text-gray-800 mb-3">Informasi yang Anda Berikan Secara Langsung</h3>
                    <ul class="space-y-2.5">
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-teal shrink-0"/>
                        <span><strong>Informasi Pendaftaran dan Profil:</strong> Nama, alamat email, nomor telepon, tanggal lahir, dan preferensi perjalanan saat Anda membuat akun.</span>
                      </li>
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-teal shrink-0"/>
                        <span><strong>Informasi Pemesanan:</strong> Detail penumpang, rincian pembayaran (kami hanya menyimpan token yang aman, bukan nomor kartu kredit penuh), dan tujuan perjalanan.</span>
                      </li>
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-teal shrink-0"/>
                        <span><strong>Komunikasi:</strong> Catatan interaksi saat Anda menghubungi layanan pelanggan kami.</span>
                      </li>
                    </ul>
                  </div>

                  <div>
                    <h3 class="text-sm font-black text-gray-800 mb-3">Informasi yang Dikumpulkan Secara Otomatis</h3>
                    <p class="mb-3">Saat Anda menggunakan layanan kami, kami secara otomatis mengumpulkan informasi tentang perangkat Anda dan bagaimana Anda berinteraksi dengan platform kami:</p>
                    <ul class="space-y-2.5">
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"/>
                        <span>Alamat IP, tipe peramban, penyedia layanan internet (ISP), dan sistem operasi.</span>
                      </li>
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"/>
                        <span>Data log sistem, stempel tanggal/waktu, dan data aliran klik (clickstream data).</span>
                      </li>
                      <li class="flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"/>
                        <span>Informasi lokasi (jika Anda memberikan izin).</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </section>

              <!-- ─ 3. Penggunaan Data ──────────────────────────────────────── -->
              <section :id="sections[2].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  3. Bagaimana Kami Menggunakan Data Anda
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-6">
                  <p>Kami menggunakan informasi yang dikumpulkan untuk berbagai tujuan operasional dan peningkatan layanan:</p>

                  <!-- Usage cards grid -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div
                      v-for="card in usageCards"
                      :key="card.title"
                      class="flex items-start gap-3 border border-gray-100 rounded-xl p-4 hover:border-teal/30 hover:bg-teal/5 transition-all"
                    >
                      <div class="w-9 h-9 bg-teal/10 rounded-xl flex items-center justify-center text-teal shrink-0" v-html="card.icon" />
                      <div>
                        <p class="text-xs font-black text-gray-800 mb-1">{{ card.title }}</p>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ card.desc }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Red callout -->
                  <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4">
                    <p class="text-xs font-black text-red-700 mb-1.5 flex items-center gap-1.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/>
                      </svg>
                      Apa yang TIDAK Kami Lakukan
                    </p>
                    <p class="text-xs text-red-600 leading-relaxed">
                      Kami secara tegas tidak akan pernah menjual, menyewakan, atau memperdagangkan informasi identifikasi pribadi Anda kepada pihak ketiga untuk tujuan pemasaran komersial mereka tanpa persetujuan eksplisit dari Anda.
                    </p>
                  </div>
                </div>
              </section>

              <!-- ─ 4. Keamanan ────────────────────────────────────────────── -->
              <section :id="sections[3].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  4. Keamanan Data
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-4">
                  <p>
                    Kami menerapkan praktik pengumpulan, penyimpanan, dan pemrosesan data yang tepat serta langkah-langkah keamanan untuk melindungi dari akses yang tidak sah, perubahan, pengungkapan, atau penghancuran informasi pribadi, nama pengguna, kata sandi, informasi transaksi, dan data yang tersimpan di Situs kami.
                  </p>

                  <!-- Security features -->
                  <div class="space-y-3 mt-4">
                    <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                      <div class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs font-black text-gray-800 mb-0.5">Enkripsi SSL</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Pertukaran data sensitif antara Situs dan Penggunanya terjadi melalui saluran komunikasi aman bersertifikat SSL dan dienkripsi serta dilindungi dengan tanda tangan digital.</p>
                      </div>
                    </div>
                    <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                      <div class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs font-black text-gray-800 mb-0.5">Audit Berkala</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Kami melakukan tinjauan internal reguler terhadap praktik keamanan kami untuk memastikan standar perlindungan data selalu terjaga.</p>
                      </div>
                    </div>
                    <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                      <div class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs font-black text-gray-800 mb-0.5">Akses Terbatas</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Akses ke data pribadi Anda dibatasi hanya untuk karyawan yang membutuhkannya untuk menjalankan fungsi pekerjaan mereka.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- ─ 5. Cookie ──────────────────────────────────────────────── -->
              <section :id="sections[4].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  5. Kebijakan Cookie
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-4">
                  <p>
                    Situs kami menggunakan "cookies" untuk meningkatkan pengalaman pengguna. Browser Anda menempatkan cookie di hard drive untuk keperluan pencatatan dan terkadang untuk melacak informasi tentang Anda. Anda dapat memilih untuk mengatur browser Anda untuk menolak cookie, atau untuk memperingatkan Anda saat cookie sedang dikirimkan.
                  </p>
                  <!-- Cookie types -->
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2">
                    <div class="border border-gray-100 rounded-xl p-4 text-center">
                      <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                      </div>
                      <p class="text-xs font-black text-gray-800">Esensial</p>
                      <p class="text-xs text-gray-400 mt-1">Diperlukan untuk fungsi dasar situs</p>
                    </div>
                    <div class="border border-gray-100 rounded-xl p-4 text-center">
                      <div class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                      </div>
                      <p class="text-xs font-black text-gray-800">Analitik</p>
                      <p class="text-xs text-gray-400 mt-1">Membantu kami memahami penggunaan situs</p>
                    </div>
                    <div class="border border-gray-100 rounded-xl p-4 text-center">
                      <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                      </div>
                      <p class="text-xs font-black text-gray-800">Preferensi</p>
                      <p class="text-xs text-gray-400 mt-1">Menyimpan pengaturan dan preferensi Anda</p>
                    </div>
                  </div>
                </div>
              </section>

              <!-- ─ 6. Hak Privasi ─────────────────────────────────────────── -->
              <section :id="sections[5].id" class="px-8 py-8 scroll-mt-28">
                <h2 class="text-xl font-black text-gray-900 mb-5 pb-4 border-b border-gray-100">
                  6. Hak Privasi Anda
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-4">
                  <p>Anda memiliki hak-hak tertentu sehubungan dengan data pribadi Anda yang kami simpan:</p>
                  <div class="space-y-2">
                    <div v-for="right in [
                      { label: 'Hak Akses', desc: 'Anda berhak meminta salinan data pribadi yang kami simpan tentang Anda.' },
                      { label: 'Hak Koreksi', desc: 'Anda berhak meminta kami memperbaiki data yang tidak akurat atau tidak lengkap.' },
                      { label: 'Hak Penghapusan', desc: 'Anda berhak meminta penghapusan data pribadi Anda dalam kondisi tertentu.' },
                      { label: 'Hak Portabilitas', desc: 'Anda berhak menerima data Anda dalam format yang dapat dibaca mesin.' },
                      { label: 'Hak Keberatan', desc: 'Anda berhak mengajukan keberatan terhadap pemrosesan data Anda untuk tujuan tertentu.' },
                    ]" :key="right.label" class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-teal/30 hover:bg-teal/5 transition-all">
                      <div class="w-2 h-2 mt-1.5 rounded-full bg-teal shrink-0" />
                      <div>
                        <span class="text-xs font-black text-gray-800">{{ right.label }}: </span>
                        <span class="text-xs text-gray-500">{{ right.desc }}</span>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 bg-gray-50 border border-gray-100 rounded-xl p-4">
                    Untuk menggunakan hak-hak di atas, silakan hubungi kami di
                    <a href="mailto:privacy@jelajahin.com" class="text-teal hover:underline font-semibold">privacy@jelajahin.com</a>.
                    Kami akan merespons permintaan Anda dalam waktu 30 hari.
                  </p>
                </div>
              </section>

            </div>
          </div>

          <!-- Contact bar bawah -->
          <div class="mt-6 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <h3 class="text-sm font-black text-gray-900">Ada pertanyaan tentang privasi Anda?</h3>
              <p class="text-xs text-gray-500 mt-0.5">Tim privasi kami siap membantu. Hubungi kami kapan saja.</p>
            </div>
            <div class="flex gap-3 shrink-0">
              <a
                href="mailto:privacy@jelajahin.com"
                class="flex items-center gap-2 px-4 py-2.5 bg-teal hover:bg-teal-dark text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-teal/20 hover:-translate-y-px"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Hubungi Tim Privasi
              </a>
              <a
                href="/contact"
                class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 transition-all"
              >
                Pusat Bantuan
              </a>
            </div>
          </div>

        </article>
      </div>
    </div>

    <AppFooter />
  </div>
</template>
