<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AuthModal from '@/Components/AuthModal.vue'

const props = withDefaults(defineProps<{
  transparent?: boolean
}>(), {
  transparent: false,
})

const isScrolled        = ref(false)
const isMobileMenuOpen  = ref(false)
const showAuthModal     = ref(false)
const authModalTab      = ref<'login' | 'register'>('login')
const userMenuOpen      = ref(false)
const page = usePage()

const user = computed(() => {
  const auth = page.props.auth as { user?: { name: string; email: string; email_verified_at?: string | null } | null } | undefined
  return auth?.user ?? null
})

const userInitials = computed(() =>
  user.value
    ? user.value.name.trim().split(/\s+/).slice(0, 2).map(p => p[0] ?? '').join('').toUpperCase()
    : ''
)

const profileMenu = [
  { label: 'Dashboard',          icon: 'layout', href: () => route('dashboard') },
  { label: 'Pemesanan Saya',     icon: 'plane',  href: () => route('dashboard') + '#pemesanan' },
  { label: 'E-Ticket & Invoice', icon: 'ticket', href: () => route('dashboard') + '#eticket' },
  { label: 'Profil',             icon: 'user',   href: () => route('profile.edit') },
]

const iconPaths: Record<string, string> = {
  layout: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
  plane: 'm22 2-7 20-4-9-9-4Z M22 2 11 13',
  ticket: 'M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z M13 5v2 M13 17v2 M13 11v2',
  user: 'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2 M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
  logout: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9',
}

const transparentRoutes = ['/', '/about']
const currentRoute = computed(() => page.url)

const shouldBeTransparent = computed(() =>
  props.transparent || transparentRoutes.some(r =>
    r === '/' ? currentRoute.value === '/' : currentRoute.value.startsWith(r)
  )
)
const isSolid = computed(() => isScrolled.value || !shouldBeTransparent.value)

function isActive(href: string) {
  if (href === '/') return currentRoute.value === '/'
  return currentRoute.value === href || currentRoute.value.startsWith(href + '/')
}

function openAuth(tab: 'login' | 'register') {
  authModalTab.value = tab
  showAuthModal.value = true
  isMobileMenuOpen.value = false
  document.body.style.overflow = 'hidden'
}

function closeAuth() {
  showAuthModal.value = false
  document.body.style.overflow = ''
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

function handleDocumentClick(e: MouseEvent) {
  if (userMenuOpen.value) {
    const el = e.target as HTMLElement | null
    if (!el?.closest('[data-user-menu]')) userMenuOpen.value = false
  }
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    if (showAuthModal.value) closeAuth()
    else if (isMobileMenuOpen.value) closeMobileMenu()
    else if (userMenuOpen.value) userMenuOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('mousedown', handleDocumentClick)
  isScrolled.value = window.scrollY > 60
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('mousedown', handleDocumentClick)
  document.body.style.overflow = ''
})

const navLinks = [
  { label: 'Beranda',      href: '/' },
  { label: 'Tentang Kami', href: '/about' },
  { label: 'Penerbangan',  href: '/flights/search' },
  { label: 'Panduan',      href: '/faq' },
]
</script>

<template>
  <!-- Navbar -->
  <nav
    :class="[
      'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
      isSolid
        ? 'bg-navy/95 border-b border-white/5 shadow-2xl backdrop-blur-md'
        : 'bg-transparent border-b border-white/10',
    ]"
    aria-label="Menu utama"
  >
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center h-16 gap-8">

        <!-- Logo -->
        <Link href="/" class="shrink-0 hover:opacity-90 transition-opacity">
          <img src="/images/logo.png" alt="Jelajahin" class="h-28 w-auto" />
        </Link>

        <!-- Search pill (desktop) -->
        <div class="hidden md:flex flex-1 items-center gap-2 bg-white/8 border border-white/10 rounded-full px-4 py-2 max-w-sm hover:bg-white/12 hover:border-white/20 transition-all">
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
        <nav class="hidden lg:flex items-center gap-1" aria-label="Navigasi utama">
          <Link
            v-for="link in navLinks"
            :key="link.label"
            :href="link.href"
            :class="[
              'text-sm font-medium px-3 py-1.5 rounded-md transition-all',
              isActive(link.href)
                ? 'text-white bg-white/12'
                : 'text-white/75 hover:text-white hover:bg-white/7',
            ]"
            :aria-current="isActive(link.href) ? 'page' : undefined"
          >
            {{ link.label }}
          </Link>
        </nav>

        <!-- Profile menu (desktop, logged in) -->
        <div v-if="user" class="hidden md:block relative ml-2" data-user-menu>
          <button
            type="button"
            class="flex items-center gap-2.5 py-1.5 pl-1.5 pr-3 rounded-full border border-white/15 bg-white/8 hover:bg-white/12 hover:border-white/25 transition-all"
            :aria-expanded="userMenuOpen"
            aria-haspopup="menu"
            @click="userMenuOpen = !userMenuOpen"
          >
            <span class="w-8 h-8 rounded-full bg-teal text-white text-sm font-black uppercase flex items-center justify-center shadow-lg shadow-teal/30">
              {{ userInitials }}
            </span>
            <span class="hidden lg:block text-sm font-semibold text-white max-w-[9rem] truncate">
              {{ user.name }}
            </span>
            <svg class="w-4 h-4 text-white/60 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1.5 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-1.5 scale-95"
          >
            <div
              v-if="userMenuOpen"
              role="menu"
              class="absolute right-0 mt-2.5 w-60 rounded-2xl bg-navy-mid border border-white/10 shadow-2xl shadow-black/40 backdrop-blur-md overflow-hidden"
            >
              <div class="px-4 py-3.5 border-b border-white/8">
                <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                <p class="text-xs text-white/50 truncate mt-0.5">{{ user.email }}</p>
              </div>

              <div class="p-1.5">
                <Link
                  v-for="item in profileMenu"
                  :key="item.label"
                  :href="item.href()"
                  role="menuitem"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/80 hover:text-white hover:bg-white/7 transition-all"
                  @click="userMenuOpen = false"
                >
                  <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path :d="iconPaths[item.icon]"/>
                  </svg>
                  {{ item.label }}
                </Link>
              </div>

              <div class="p-1.5 pt-0 border-t border-white/8">
                <Link
                  method="post"
                  :href="route('logout')"
                  as="button"
                  role="menuitem"
                  class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-400 hover:text-white hover:bg-red-500/15 transition-all"
                  @click="userMenuOpen = false"
                >
                  <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path :d="iconPaths.logout"/>
                  </svg>
                  Keluar
                </Link>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Auth buttons (desktop, guest) -->
        <div v-else class="hidden md:flex items-center gap-2.5 ml-2">
          <button
            type="button"
            class="border border-white/25 text-white text-sm font-medium px-4 py-1.5 rounded-md hover:bg-white/7 hover:border-white/40 transition-all"
            @click="openAuth('login')"
          >
            Masuk
          </button>
          <button
            type="button"
            class="bg-teal text-white text-sm font-semibold px-5 py-1.5 rounded-md hover:bg-teal-dark transition-all shadow-lg shadow-teal/20 hover:-translate-y-px"
            @click="openAuth('register')"
          >
            Daftar
          </button>
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
              <img src="/images/logo.png" alt="Jelajahin" class="h-9 w-auto brightness-0 invert" />
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
                isActive(link.href)
                  ? 'text-white bg-white/12'
                  : 'text-white/75 hover:text-white hover:bg-white/7',
              ]"
              :aria-current="isActive(link.href) ? 'page' : undefined"
              @click="closeMobileMenu"
            >
              {{ link.label }}
            </Link>

            <!-- User (mobile, logged in) -->
            <div v-if="user" class="mt-4 pt-5 border-t border-white/8">
              <div class="flex items-center gap-3 px-4 mb-2">
                <span class="w-9 h-9 rounded-full bg-teal text-white text-sm font-black uppercase flex items-center justify-center shadow-lg shadow-teal/30 shrink-0">
                  {{ userInitials }}
                </span>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                  <p class="text-xs text-white/50 truncate">{{ user.email }}</p>
                </div>
              </div>

              <Link
                v-for="item in profileMenu"
                :key="item.label"
                :href="item.href()"
                :class="[
                  'flex items-center gap-3 text-base font-medium px-4 py-3 rounded-md transition-all',
                  isActive(item.href())
                    ? 'text-white bg-white/12'
                    : 'text-white/75 hover:text-white hover:bg-white/7',
                ]"
                @click="closeMobileMenu"
              >
                <svg class="w-4 h-4 text-teal shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                  <path :d="iconPaths[item.icon]"/>
                </svg>
                {{ item.label }}
              </Link>

              <Link
                method="post"
                :href="route('logout')"
                as="button"
                class="w-full flex items-center gap-3 text-base font-medium text-red-400 hover:text-white hover:bg-red-500/15 px-4 py-3 rounded-md transition-all"
                @click="closeMobileMenu"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                  <path :d="iconPaths.logout"/>
                </svg>
                Keluar
              </Link>
            </div>

            <!-- Auth (mobile, guest) -->
            <div v-else class="flex gap-3 mt-4 pt-5 border-t border-white/8">
              <button
                type="button"
                class="flex-1 text-center border border-white/25 text-white text-sm font-medium py-2.5 rounded-md hover:bg-white/7 transition-all"
                @click="openAuth('login')"
              >
                Masuk
              </button>
              <button
                type="button"
                class="flex-1 text-center bg-teal text-white text-sm font-semibold py-2.5 rounded-md hover:bg-teal-dark transition-all"
                @click="openAuth('register')"
              >
                Daftar
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>

  <!-- Auth Modal -->
  <AuthModal
    :show="showAuthModal"
    :initial-tab="authModalTab"
    @close="closeAuth"
  /></template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
</style>
