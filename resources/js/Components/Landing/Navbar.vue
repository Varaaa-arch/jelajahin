<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Prop to force transparent mode (e.g. on hero pages)
const props = withDefaults(defineProps<{
  transparent?: boolean
}>(), {
  transparent: false,
})

const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)
const page = usePage()

// Active route helper
const currentRoute = computed(() => page.url)

function isActive(href: string) {
  return currentRoute.value === href || currentRoute.value.startsWith(href + '/')
}

function handleScroll() {
  isScrolled.value = window.scrollY > 60
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  document.body.style.overflow = isMobileMenuOpen.value ? 'hidden' : ''
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false
  document.body.style.overflow = ''
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && isMobileMenuOpen.value) closeMobileMenu()
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

// Nav links — pakai route name untuk Inertia Link
const navLinks = [
  { label: 'Beranda',      routeName: 'home',    href: '/' },
  { label: 'Tentang Kami', routeName: 'about',   href: '/about' },
  { label: 'Penerbangan',  routeName: 'flights.search', href: '/flights/search' },
  { label: 'Panduan',      routeName: null,      href: '#' },
  { label: 'Dukungan',     routeName: null,      href: '#' },
]
</script>

<template>
  <!-- Navbar -->
  <nav
    :class="[
      'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
      // When transparent mode & not scrolled: fully see-through
      props.transparent && !isScrolled
        ? 'bg-transparent border-b border-white/10'
        : 'bg-navy border-b border-white/5',
      isScrolled ? 'shadow-2xl backdrop-blur-md bg-navy/95' : '',
    ]"
    aria-label="Menu utama"
  >
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center h-16 gap-8">

        <!-- Logo -->
        <Link href="/" class="text-white font-black text-xl tracking-tight shrink-0 hover:opacity-90 transition-opacity">
          Jelajahin
        </Link>

        <!-- Search pill (desktop) -->
        <div class="hidden md:flex items-center gap-2 bg-white/8 border border-white/10 rounded-full px-4 py-2 w-52 hover:bg-white/12 hover:border-white/20 transition-all">
          <svg class="w-4 h-4 text-white/40 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
          </svg>
          <input
            type="search"
            placeholder="Cari tujuan…"
            class="bg-transparent border-none outline-none text-white text-sm placeholder-white/40 w-full p-0 focus:ring-0"
            aria-label="Cari"
          />
        </div>

        <!-- Nav links (desktop) -->
        <nav class="hidden lg:flex items-center gap-1 ml-auto" aria-label="Navigasi utama">
          <Link
            v-for="link in navLinks"
            :key="link.label"
            :href="link.href"
            :class="[
              'text-sm font-medium px-3 py-1.5 rounded-md transition-all',
              isActive(link.href) && link.href !== '#'
                ? 'text-white bg-white/12'
                : 'text-white/75 hover:text-white hover:bg-white/7',
            ]"
            :aria-current="isActive(link.href) && link.href !== '#' ? 'page' : undefined"
          >
            {{ link.label }}
          </Link>
        </nav>

        <!-- Auth buttons (desktop) -->
        <div class="hidden md:flex items-center gap-2.5 ml-2">
          <Link
            :href="route('login')"
            class="border border-white/25 text-white text-sm font-medium px-4 py-1.5 rounded-md hover:bg-white/7 hover:border-white/40 transition-all"
          >
            Masuk
          </Link>
          <Link
            :href="route('register')"
            class="bg-teal text-white text-sm font-semibold px-5 py-1.5 rounded-md hover:bg-teal-dark transition-all shadow-lg shadow-teal/20 hover:-translate-y-px"
          >
            Daftar
          </Link>
        </div>

        <!-- Hamburger (mobile) -->
        <button
          class="lg:hidden ml-auto p-1.5 rounded-md text-white hover:bg-white/7 transition-all"
          :aria-expanded="isMobileMenuOpen"
          aria-label="Buka menu"
          @click="toggleMobileMenu"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
            <template v-if="!isMobileMenuOpen">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </template>
            <template v-else>
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </template>
          </svg>
        </button>

      </div>
    </div>
  </nav>

  <!-- Mobile Menu Overlay -->
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 z-[60] lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Menu navigasi"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/70 backdrop-blur-sm"
          @click="closeMobileMenu"
        />

        <!-- Panel -->
        <Transition name="slide-right">
          <div
            v-if="isMobileMenuOpen"
            class="absolute top-0 right-0 h-full w-[min(320px,90vw)] bg-navy-mid flex flex-col p-6 gap-1 overflow-y-auto"
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <span class="text-white font-black text-xl">Jelajahin</span>
              <button
                class="p-1.5 rounded-md text-white/60 hover:text-white hover:bg-white/7 transition-all"
                aria-label="Tutup menu"
                @click="closeMobileMenu"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>

            <!-- Mobile nav links -->
            <Link
              v-for="link in navLinks"
              :key="link.label"
              :href="link.href"
              :class="[
                'text-base font-medium px-4 py-3 rounded-md transition-all',
                isActive(link.href) && link.href !== '#'
                  ? 'text-white bg-white/12'
                  : 'text-white/75 hover:text-white hover:bg-white/7',
              ]"
              :aria-current="isActive(link.href) && link.href !== '#' ? 'page' : undefined"
              @click="closeMobileMenu"
            >
              {{ link.label }}
            </Link>

            <!-- Auth -->
            <div class="flex gap-3 mt-4 pt-5 border-t border-white/8">
              <Link
                :href="route('login')"
                class="flex-1 text-center border border-white/25 text-white text-sm font-medium py-2.5 rounded-md hover:bg-white/7 transition-all"
                @click="closeMobileMenu"
              >
                Masuk
              </Link>
              <Link
                :href="route('register')"
                class="flex-1 text-center bg-teal text-white text-sm font-semibold py-2.5 rounded-md hover:bg-teal-dark transition-all"
                @click="closeMobileMenu"
              >
                Daftar
              </Link>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
</style>
