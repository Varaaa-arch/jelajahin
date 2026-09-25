<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import Navbar from '@/Components/Landing/Navbar.vue'
import HeroSection from '@/Components/Landing/HeroSection.vue'
import SearchBox from '@/Components/Landing/SearchBox.vue'
import OffersSection from '@/Components/Landing/OffersSection.vue'
import DestinationsSection from '@/Components/Landing/DestinationsSection.vue'
import TrustSection from '@/Components/Landing/TrustSection.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'
import OtpModal from '@/Components/OtpModal.vue'

const props = defineProps<{
  canLogin?: boolean
  canRegister?: boolean
  verifyOtp?: boolean
}>()

const page = usePage()
const authUser = computed(() => (page.props.auth as { user?: { name: string; email: string } | null } | undefined)?.user ?? null)

const showOtp = ref(false)

watch(() => props.verifyOtp, (v) => {
  if (v) showOtp.value = true
}, { immediate: true })

function handleOtpVerified() {
  showOtp.value = false
  router.visit(route('dashboard'), { preserveState: false })
}

// Scroll-to-top
const showScrollTop = ref(false)

function handleScroll() {
  showScrollTop.value = window.scrollY > 400
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <Head title="Perjalanan Impian Dimulai di Sini" />

  <div class="min-h-screen bg-white font-sans">

    <!-- Navbar (fixed, z-50) -->
    <Navbar />

    <!-- Hero + Search overlay -->
    <div class="relative">
      <HeroSection />
      <!-- SearchBox sits at the bottom edge of hero, overlapping into content below -->
      <div class="relative -mt-6 pb-2">
        <SearchBox />
      </div>
    </div>

    <!-- Main content -->
    <main>
      <OffersSection />
      <DestinationsSection />
      <TrustSection />
    </main>

    <AppFooter />

    <!-- Auto-open OTP verification after login/register -->
    <OtpModal
      v-if="showOtp && authUser"
      :show="showOtp"
      :email="authUser.email"
      @verified="handleOtpVerified"
      @close="showOtp = false"
    />

    <!-- Scroll to top FAB -->
    <Transition name="fade-up">
      <button
        v-if="showScrollTop"
        class="fixed bottom-7 right-7 z-40 w-11 h-11 bg-teal hover:bg-teal-dark text-white rounded-full shadow-lg shadow-teal/35 flex items-center justify-center transition-all hover:-translate-y-0.5"
        aria-label="Kembali ke atas"
        @click="scrollToTop"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
          <polyline points="18 15 12 9 6 15"/>
        </svg>
      </button>
    </Transition>

  </div>
</template>

<style scoped>
.fade-up-enter-active,
.fade-up-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-up-enter-from,
.fade-up-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
