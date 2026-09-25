<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import OtpModal from '@/Components/OtpModal.vue'

const props = defineProps<{
  email: string
}>()

const showOtp = ref(true)
const otpVerified = ref(false)
</script>

<template>
  <GuestLayout>
    <Head title="Verifikasi Email" />

    <div class="flex flex-col items-center justify-center min-h-[80vh]">
      <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal to-teal-dark flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal/25">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900">Verifikasi Email</h1>
        <p class="text-sm text-gray-500 mt-2">Masukkan kode 6 digit yang kami kirim ke email Anda</p>
      </div>

      <OtpModal
        :show="showOtp"
        :email="email"
        @verified="otpVerified = true"
        @close="showOtp = false"
      />

      <Transition v-if="otpVerified">
        <div class="text-center mt-8">
          <div class="w-16 h-16 rounded-full bg-teal/10 flex items-center justify-center mx-auto mb-4">
            <div class="w-12 h-12 rounded-full bg-teal flex items-center justify-center shadow-lg shadow-teal/30 animate-bounce">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>
          <h2 class="text-xl font-black text-gray-900">Email Terverifikasi!</h2>
          <p class="text-sm text-gray-500 mt-2">Akun Anda sudah aktif. Mengalihkan...</p>
          <a :href="route('dashboard')" class="mt-4 inline-block bg-teal hover:bg-teal-dark text-white font-bold text-sm py-3 px-8 rounded-xl transition-all shadow-lg shadow-teal/25">
            Lanjut ke Dashboard
          </a>
        </div>
      </Transition>
    </div>
  </GuestLayout>
</template>
