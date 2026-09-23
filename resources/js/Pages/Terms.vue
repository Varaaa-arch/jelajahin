<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'

const lastUpdated = '15 November 2024'
const agreed      = ref(false)
const activeSection = ref('penggunaan')

// ─── Sections ─────────────────────────────────────────────────────────────────
const sections = [
  { id: 'penggunaan',    label: 'Penggunaan Layanan' },
  { id: 'akun',         label: 'Akun Pengguna' },
  { id: 'pemesanan',    label: 'Pemesanan & Pembayaran' },
  { id: 'pembatalan',   label: 'Kebijakan Pembatalan' },
  { id: 'tanggung-pengguna', label: 'Tanggung Jawab Pengguna' },
  { id: 'tanggung-jelajahin', label: 'Tanggung Jawab Jelajahin' },
  { id: 'pembatasan',   label: 'Pembatasan Kewajiban' },
  { id: 'maskapai',     label: 'Maskapai Penerbangan' },
  { id: 'haki',         label: 'Hak Kekayaan Intelektual' },
  { id: 'perubahan',    label: 'Perubahan Syarat & Ketentuan' },
  { id: 'sengketa',     label: 'Penyelesaian Sengketa' },
  { id: 'lainnya',      label: 'Lainnya' },
]

// Scroll-spy
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
  const el = document.getElementById(id)
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  activeSection.value = id
}

function printDoc() {
  window.print()
}

onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))
</script>

<template>
  <Head title="Syarat dan Ketentuan – Jelajahin" />

  <div class="min-h-screen bg-white font-sans">
    <Navbar :transparent="false" />

    <!-- ═══ HERO ════════════════════════════════════════════════════════════════ -->
    <section class="pt-24 pb-10 px-6 text-center bg-white border-b border-gray-100">
      <h1 class="text-4xl md:text-5xl font-black text-gray-900">Syarat dan Ketentuan</h1>
      <p class="mt-3 text-gray-500 text-sm max-w-xl mx-auto">
        Mohon baca dengan seksama sebelum menggunakan layanan Jelajahin.
      </p>
      <div class="mt-4 inline-flex items-center gap-2 text-xs text-gray-400 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-full">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
        Terakhir diperbarui: {{ lastUpdated }}
      </div>
    </section>

    <!-- ═══ CONTENT ════════════════════════════════════════════════════════════ -->
    <div class="max-w-6xl mx-auto px-6 py-14">
      <div class="flex flex-col lg:flex-row gap-10 items-start">

        <!-- ── Sidebar TOC ─────────────────────────────────────────────────── -->
        <aside class="w-full lg:w-60 shrink-0 lg:sticky lg:top-24 print:hidden">
          <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5">
            <h2 class="text-xs font-black text-gray-700 uppercase tracking-wider mb-4">Daftar Isi</h2>
            <nav class="flex flex-col gap-0.5" aria-label="Daftar isi">
              <button
                v-for="(sec, idx) in sections"
                :key="sec.id"
                :class="[
                  'text-left text-xs px-3 py-2 rounded-lg transition-all',
                  activeSection === sec.id
                    ? 'bg-teal/10 text-teal font-semibold'
                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800',
                ]"
                @click="scrollTo(sec.id)"
              >
                {{ idx + 1 }}. {{ sec.label }}
              </button>
            </nav>

            <!-- Print button -->
            <button
              class="mt-5 w-full flex items-center justify-center gap-2 border border-gray-200 text-gray-600 text-xs font-semibold py-2.5 rounded-xl hover:bg-gray-100 transition-all"
              @click="printDoc"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
              </svg>
              Cetak Dokumen
            </button>
          </div>
        </aside>

        <!-- ── Main content ──────────────────────────────────────────────────── -->
        <article class="flex-1 min-w-0 text-sm text-gray-700 leading-relaxed space-y-10">

          <!-- Intro -->
          <p class="text-gray-600">
            Selamat datang di Jelajahin. Syarat dan Ketentuan ini <strong>("Perjanjian")</strong> mengatur akses dan penggunaan Anda atas situs web, aplikasi seluler, dan layanan terkait <strong>("Layanan")</strong> yang disediakan oleh Jelajahin Travel <strong>("kami"</strong>, <strong>"kita"</strong>, atau <strong>"milik kami"</strong>). Dengan mengakses atau menggunakan Layanan kami, Anda menyetujui untuk terikat oleh Perjanjian ini.
          </p>

          <!-- 1 -->
          <section :id="sections[0].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">1. Penggunaan Layanan</h2>
            <p>Anda diizinkan menggunakan Layanan kami secara eksklusif untuk tujuan pribadi dan non-komersial, semata-mata untuk mencari informasi perjalanan, menentukan ketersediaan produk dan layanan terkait perjalanan, membuat pemesanan yang sah, atau bertransaksi dengan pemakai kami. Setiap penggunaan Layanan untuk tujuan lain dilarang keras. Anda setuju untuk tidak menggunakan Layanan untuk membuat pemesanan yang spekulatif, palsu, atau curang.</p>
          </section>

          <!-- 2 -->
          <section :id="sections[1].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">2. Akun Pengguna</h2>
            <p>Untuk mengakses fitur tertentu dari Layanan, Anda mungkin perlu membuat akun. Anda bertanggung jawab untuk menjaga kerahasiaan kredensial akun Anda dan untuk semua aktivitas yang terjadi di bawah akun Anda. Anda setuju untuk segera memberi tahu kami tentang penggunaan tidak sah atas akun Anda. Kami berhak menangguhkan atau menghentikan akun Anda jika kami mencurigai adanya aktivitas penipuan atau pelanggaran terhadap Perjanjian ini.</p>
          </section>

          <!-- 3 -->
          <section :id="sections[2].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">3. Pemesanan & Pembayaran</h2>

            <!-- Callout box -->
            <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-xl px-5 py-4 mb-5">
              <p class="text-xs font-black text-amber-700 mb-1.5 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                PENTING: Batas Waktu Pembayaran
              </p>
              <p class="text-xs text-amber-700 leading-relaxed">
                Semua pemesanan memerlukan pembayaran penuh pada saat pemesanan kecuali dinyatakan lain. <strong>Pembayaran harus diselesaikan dalam waktu 15 menit</strong> setelah pemesanan dikonfirmasi pada sistem kami. Jika pembayaran tidak diterima dalam jangka waktu tersebut, sistem akan secara otomatis membatalkan pemesanan Anda tanpa pemberitahuan lebih lanjut.
              </p>
            </div>

            <p>Harga yang ditampilkan mungkin tidak termasuk pajak dan biaya tambahan yang akan ditambahkan pada saat checkout. Kami menerima berbagai metode pembayaran sebagaimana tercantum di halaman pembayaran kami. Dengan memberikan informasi pembayaran, Anda menyatakan bahwa Anda berhak menggunakan metode pembayaran tersebut.</p>
          </section>

          <!-- 4 -->
          <section :id="sections[3].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">4. Kebijakan Pembatalan & Pengembalian Dana</h2>
            <p class="mb-4">Kebijakan pembatalan bervariasi tergantung pada layanan dan penyedia spesifik. Namun, pedoman umum pengembalian dana (refund) untuk layanan langsung Jelajahin adalah sebagai berikut:</p>
            <ul class="space-y-2.5 mb-4">
              <li class="flex items-start gap-3">
                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-teal shrink-0"/>
                <span><strong>Pembatalan H-7 atau lebih:</strong> Pengembalian dana 100% dari total nilai pesanan (dikurangi biaya administrasi yang berlaku).</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-teal shrink-0"/>
                <span><strong>Pembatalan H-3 sampai dengan H-7:</strong> Pengembalian dana 50% dari total nilai pesanan.</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"/>
                <span><strong>Pembatalan kurang dari 24 jam (H-1):</strong> Tidak ada pengembalian dana (No refund).</span>
              </li>
            </ul>
            <p>Kebijakan ini tunduk pada syarat dan ketentuan spesifik dari maskapai penerbangan, hotel, atau penyedia layanan lainnya yang mungkin lebih ketat. Silakan tinjau kebijakan pembatalan spesifik selama proses pemesanan.</p>
          </section>

          <!-- 5 -->
          <section :id="sections[4].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">5. Tanggung Jawab Pengguna</h2>
            <p>Anda bertanggung jawab untuk memastikan bahwa semua informasi yang Anda berikan adalah akurat dan lengkap. Anda juga bertanggung jawab untuk mendapatkan semua dokumen perjalanan yang diperlukan (seperti paspor, visa, dan sertifikat kesehatan) yang diwajibkan oleh destinasi yang Anda tuju. Jelajahin tidak bertanggung jawab atas kerugian yang timbul akibat kegagalan Anda memenuhi persyaratan perjalanan ini.</p>
          </section>

          <!-- 6 -->
          <section :id="sections[5].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">6. Tanggung Jawab Jelajahin</h2>
            <p>Kami bertindak sebagai perantara antara Anda dan penyedia layanan pihak ketiga (maskapai, hotel, dll.). Kami berusaha untuk memastikan keakuratan informasi di platform kami, tetapi kami tidak menjamin bahwa informasi tersebut bebas dari kesalahan. Kami tidak bertanggung jawab atas tindakan, kelalaian, kesalahan, atau ketidakwajaran yang dilakukan oleh penyedia layanan pihak ketiga tersebut.</p>
          </section>

          <!-- 7 -->
          <section :id="sections[6].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">7. Pembatasan Kewajiban</h2>
            <p>Sejauh diizinkan oleh hukum yang berlaku, Jelajahin, afiliasinya, direktur, karyawan, atau agennya tidak akan bertanggung jawab atas kerugian langsung, tidak langsung, insidental, khusus, atau konsekuensial yang timbul dari atau sehubungan dengan penggunaan Anda atas Layanan kami, keterlambatan perjalanan, pembatalan, overbooking, pengecekan, force majeure, atau penyebab lain di luar kendali wajar kami.</p>
          </section>

          <!-- 8 -->
          <section :id="sections[7].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">8. Aturan Maskapai Penerbangan</h2>
            <p>Perjalanan udara tunduk pada ketentuan pengangkutan maskapai penerbangan yang bersangkutan. Penumpang wajib mematuhi aturan maskapai terkait bagasi, check-in, dan perilaku di pesawat. Perubahan jadwal penerbangan oleh maskapai berada di luar kendali Jelajahin, dan kami akan membantu memfasilitasi komunikasi namun tidak bertanggung jawab atas kompensasi akibat perubahan tersebut.</p>
          </section>

          <!-- 9 -->
          <section :id="sections[8].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">9. Hak Kekayaan Intelektual</h2>
            <p>Semua konten yang terdapat pada Layanan, termasuk teks, grafik, logo, ikon tombol, gambar, klip audio, unduhan digital, dan kompilasi data, adalah milik Jelajahin atau pemasok kontennya dan dilindungi oleh undang-undang hak cipta internasional. Anda tidak diizinkan untuk menyalin, memodifikasi, mendistribusikan, atau menggunakan konten kami tanpa izin tertulis dari kami.</p>
          </section>

          <!-- 10 -->
          <section :id="sections[9].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">10. Perubahan Syarat & Ketentuan</h2>
            <p>Kami berhak untuk mengubah atau memperbarui Syarat dan Ketentuan ini kapan saja tanpa pemberitahuan sebelumnya. Perubahan yang berlaku akan segera dipasang di platform kami. Penggunaan berkelanjutan Anda atas Layanan setelah perubahan tersebut merupakan penerimaan Anda terhadap Syarat dan Ketentuan yang direvisi.</p>
          </section>

          <!-- 11 -->
          <section :id="sections[10].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">11. Penyelesaian Sengketa</h2>
            <p>Setiap sengketa yang timbul dari atau berkaitan dengan Perjanjian ini atau penggunaan Layanan akan diselesaikan melalui negosiasi itikad baik. Jika sengketa tidak dapat diselesaikan melalui negosiasi, sengketa tersebut akan diselesaikan secara eksklusif oleh pengadilan yang berwenang di yurisdiksi tempat Jelajahin Travel terdaftar secara hukum.</p>
          </section>

          <!-- 12 -->
          <section :id="sections[11].id" class="scroll-mt-28">
            <h2 class="text-base font-black text-gray-900 mb-3">12. Lainnya</h2>
            <p>Jika ada ketentuan dalam Perjanjian ini yang dianggap tidak sah atau tidak dapat dilaksanakan, ketentuan tersebut akan dipisahkan, dan ketentuan yang tersisa akan tetap berlaku dan memiliki kekuatan hukum penuh. Kegagalan kami untuk mengakses hak atau ketentuan apa pun dari Perjanjian ini tidak akan dianggap sebagai pengabaian hak tersebut.</p>
          </section>

          <!-- ── Need help callout ──────────────────────────────────────────── -->
          <div class="bg-gray-50 border border-gray-200 rounded-2xl p-7">
            <h3 class="text-base font-black text-gray-900 mb-1.5">Butuh Bantuan?</h3>
            <p class="text-sm text-gray-500 mb-5">
              Jika Anda memiliki pertanyaan tentang Syarat & Ketentuan ini, silakan hubungi tim dukungan kami:
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <a
                href="mailto:support@jelajahin.com"
                class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 hover:border-teal/40 hover:bg-teal/5 transition-all group"
              >
                <div class="w-8 h-8 bg-teal/10 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-teal/20 transition-colors">
                  <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Email</p>
                  <p class="text-xs font-bold text-gray-800">support@jelajahin.com</p>
                </div>
              </a>
              <a
                href="tel:+622155550000"
                class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 hover:border-teal/40 hover:bg-teal/5 transition-all group"
              >
                <div class="w-8 h-8 bg-teal/10 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-teal/20 transition-colors">
                  <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Telepon</p>
                  <p class="text-xs font-bold text-gray-800">+62 21 XXXX-XXXX</p>
                </div>
              </a>
            </div>
          </div>

          <!-- ── Agreement checkbox ─────────────────────────────────────────── -->
          <div class="border-t border-gray-100 pt-8">
            <label class="flex items-start gap-3 cursor-pointer group">
              <input
                v-model="agreed"
                type="checkbox"
                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-teal focus:ring-teal/30 cursor-pointer shrink-0"
              />
              <div>
                <p class="text-sm font-semibold text-gray-800">
                  Saya telah membaca, memahami, dan menerima Syarat & Ketentuan ini.
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Dengan mencentang kotak ini, Anda menyetujui seluruh kebijakan yang berlaku di atas untuk melanjutkan penggunaan layanan Jelajahin.
                </p>
              </div>
            </label>

            <div class="flex justify-end mt-5">
              <Link
                href="/"
                :class="[
                  'inline-flex items-center gap-2 px-8 py-3 rounded-xl text-sm font-bold transition-all',
                  agreed
                    ? 'bg-teal hover:bg-teal-dark text-white shadow-lg shadow-teal/25 hover:-translate-y-px'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed pointer-events-none',
                ]"
              >
                Lanjutkan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>

        </article>
      </div>
    </div>

    <AppFooter />
  </div>
</template>

<style scoped>
@media print {
  .print\\:hidden { display: none !important; }
}
</style>
