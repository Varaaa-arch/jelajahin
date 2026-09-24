<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'

// ─── Props & Emits ────────────────────────────────────────────────────────────
const props = defineProps<{
  show: boolean
  initialTab?: 'login' | 'register'
}>()

const emit = defineEmits<{
  close: []
}>()

// ─── Tab state ────────────────────────────────────────────────────────────────
const activeTab = ref<'login' | 'register'>(props.initialTab ?? 'login')

watch(() => props.initialTab, (v) => { if (v) activeTab.value = v })
watch(() => props.show, (v) => {
  if (v) {
    document.body.style.overflow = 'hidden'
    nextTick(() => {
      const el = document.getElementById('modal-email')
      el?.focus()
    })
  } else {
    document.body.style.overflow = ''
  }
})

// ─── Show/hide password ───────────────────────────────────────────────────────
const showLoginPw  = ref(false)
const showRegPw    = ref(false)
const showRegPwCfm = ref(false)

// ─── Login form ───────────────────────────────────────────────────────────────
const loginForm = useForm({
  email:    '',
  password: '',
  remember: false,
})

function submitLogin() {
  loginForm.post(route('login'), {
    onSuccess: () => emit('close'),
    onFinish:  () => loginForm.reset('password'),
  })
}

// ─── Register form ────────────────────────────────────────────────────────────
const registerForm = useForm({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

function submitRegister() {
  registerForm.post(route('register'), {
    onSuccess: () => emit('close'),
    onFinish:  () => registerForm.reset('password', 'password_confirmation'),
  })
}

function close() {
  loginForm.reset()
  registerForm.reset()
  loginForm.clearErrors()
  registerForm.clearErrors()
  emit('close')
}

function handleBackdrop(e: MouseEvent) {
  if ((e.target as HTMLElement).id === 'auth-modal-backdrop') close()
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        id="auth-modal-backdrop"
        class="fixed inset-0 z-[100] flex items-center justify-center px-4"
        role="dialog"
        aria-modal="true"
        :aria-label="activeTab === 'login' ? 'Login' : 'Daftar akun'"
        @click="handleBackdrop"
        @keydown="handleKeydown"
      >
        <!-- Backdrop blur -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" />

        <!-- Modal card -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-150 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="show"
            class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-y-auto max-h-[90dvh] scrollbar-hide"
            @click.stop
          >
            <!-- Close button -->
            <button
              type="button"
              class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-all z-10"
              aria-label="Tutup"
              @click="close"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>

            <!-- Header -->
            <div class="px-8 pt-6 pb-0 text-center">
              <!-- Logo icon -->
              <div class="w-10 h-10 bg-teal rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-teal/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                </svg>
              </div>
              <h2 class="text-lg font-black text-gray-900">
                {{ activeTab === 'login' ? 'Selamat datang kembali' : 'Buat akun baru' }}
              </h2>
              <p class="text-sm text-gray-400 mt-0.5 mb-4">
                {{ activeTab === 'login' ? 'Masuk ke akun Jelajahin Anda' : 'Bergabung dan mulai menjelajah' }}
              </p>

              <!-- Tabs -->
              <div class="flex bg-gray-100 rounded-xl p-1 mb-4">
                <button
                  type="button"
                  :class="[
                    'flex-1 py-1.5 text-sm font-bold rounded-lg transition-all',
                    activeTab === 'login'
                      ? 'bg-white text-gray-900 shadow-sm'
                      : 'text-gray-500 hover:text-gray-700',
                  ]"
                  @click="activeTab = 'login'"
                >
                  Masuk
                </button>
                <button
                  type="button"
                  :class="[
                    'flex-1 py-1.5 text-sm font-bold rounded-lg transition-all',
                    activeTab === 'register'
                      ? 'bg-white text-gray-900 shadow-sm'
                      : 'text-gray-500 hover:text-gray-700',
                  ]"
                  @click="activeTab = 'register'"
                >
                  Daftar
                </button>
              </div>
            </div>

            <!-- ── LOGIN FORM ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'login'" class="px-8 pb-6">
              <form class="space-y-3" @submit.prevent="submitLogin">

                <!-- Email -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="modal-email">
                    Email
                  </label>
                  <input
                    id="modal-email"
                    v-model="loginForm.email"
                    type="email"
                    placeholder="email@contoh.com"
                    autocomplete="email"
                    required
                    :class="[
                      'w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                      loginForm.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200',
                    ]"
                  />
                  <p v-if="loginForm.errors.email" class="text-xs text-red-500 mt-1">{{ loginForm.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                  <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-gray-700" for="modal-password">
                      Password
                    </label>
                    <a href="/forgot-password" class="text-xs text-teal hover:underline">Lupa password?</a>
                  </div>
                  <div class="relative">
                    <input
                      id="modal-password"
                      v-model="loginForm.password"
                      :type="showLoginPw ? 'text' : 'password'"
                      placeholder="••••••••"
                      autocomplete="current-password"
                      required
                      :class="[
                        'w-full px-4 py-2.5 pr-11 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                        loginForm.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200',
                      ]"
                    />
                    <button
                      type="button"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                      :aria-label="showLoginPw ? 'Sembunyikan password' : 'Tampilkan password'"
                      @click="showLoginPw = !showLoginPw"
                    >
                      <svg v-if="!showLoginPw" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="loginForm.errors.password" class="text-xs text-red-500 mt-1">{{ loginForm.errors.password }}</p>
                </div>

                <!-- Remember me -->
                <label class="flex items-center gap-2.5 cursor-pointer">
                  <input
                    v-model="loginForm.remember"
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-teal focus:ring-teal/30 cursor-pointer"
                  />
                  <span class="text-xs text-gray-600">Ingat saya</span>
                </label>

                <!-- Submit -->
                <button
                  type="submit"
                  :disabled="loginForm.processing"
                  class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm py-3 rounded-xl transition-all shadow-lg shadow-teal/25 hover:-translate-y-px"
                >
                  <svg v-if="loginForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ loginForm.processing ? 'Memproses...' : 'Masuk' }}
                </button>

                <!-- Divider -->
                <div class="flex items-center gap-3 my-0.5">
                  <div class="flex-1 h-px bg-gray-100"/>
                  <span class="text-xs text-gray-400">atau</span>
                  <div class="flex-1 h-px bg-gray-100"/>
                </div>

                <!-- Social login buttons -->
                <div class="grid grid-cols-3 gap-2.5">
                  <!-- Google -->
                  <a
                    :href="route('social.redirect', { provider: 'google' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all group"
                    aria-label="Masuk dengan Google"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                      <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                      <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                      <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-gray-700 transition-colors">Google</span>
                  </a>

                  <!-- Facebook -->
                  <a
                    :href="route('social.redirect', { provider: 'facebook' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-[#1877F2]/30 hover:bg-blue-50/50 transition-all group"
                    aria-label="Masuk dengan Facebook"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-[#1877F2] transition-colors">Facebook</span>
                  </a>

                  <!-- TikTok -->
                  <a
                    :href="route('social.redirect', { provider: 'tiktok' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-gray-800 hover:bg-gray-900/5 transition-all group"
                    aria-label="Masuk dengan TikTok"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.53V6.75a4.86 4.86 0 01-1-.06z" fill="currentColor" class="text-gray-800 group-hover:text-gray-900"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-gray-900 transition-colors">TikTok</span>
                  </a>
                </div>

                <!-- Switch to register -->
                <p class="text-center text-xs text-gray-500">
                  Belum punya akun?
                  <button type="button" class="text-teal font-bold hover:underline" @click="activeTab = 'register'">
                    Daftar sekarang
                  </button>
                </p>

              </form>
            </div>

            <!-- ── REGISTER FORM ──────────────────────────────────────────── -->
            <div v-else-if="activeTab === 'register'" class="px-8 pb-6">
              <form class="space-y-3" @submit.prevent="submitRegister">

                <!-- Name -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="modal-name">
                    Nama Lengkap
                  </label>
                  <input
                    id="modal-name"
                    v-model="registerForm.name"
                    type="text"
                    placeholder="Nama Anda"
                    autocomplete="name"
                    required
                    :class="[
                      'w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                      registerForm.errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200',
                    ]"
                  />
                  <p v-if="registerForm.errors.name" class="text-xs text-red-500 mt-1">{{ registerForm.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="modal-reg-email">
                    Email
                  </label>
                  <input
                    id="modal-reg-email"
                    v-model="registerForm.email"
                    type="email"
                    placeholder="email@contoh.com"
                    autocomplete="email"
                    required
                    :class="[
                      'w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                      registerForm.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200',
                    ]"
                  />
                  <p v-if="registerForm.errors.email" class="text-xs text-red-500 mt-1">{{ registerForm.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="modal-reg-password">
                    Password
                  </label>
                  <div class="relative">
                    <input
                      id="modal-reg-password"
                      v-model="registerForm.password"
                      :type="showRegPw ? 'text' : 'password'"
                      placeholder="Min. 8 karakter"
                      autocomplete="new-password"
                      required
                      :class="[
                        'w-full px-4 py-2.5 pr-11 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                        registerForm.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200',
                      ]"
                    />
                    <button
                      type="button"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                      @click="showRegPw = !showRegPw"
                    >
                      <svg v-if="!showRegPw" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="registerForm.errors.password" class="text-xs text-red-500 mt-1">{{ registerForm.errors.password }}</p>
                </div>

                <!-- Confirm password -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="modal-reg-password-confirm">
                    Konfirmasi Password
                  </label>
                  <div class="relative">
                    <input
                      id="modal-reg-password-confirm"
                      v-model="registerForm.password_confirmation"
                      :type="showRegPwCfm ? 'text' : 'password'"
                      placeholder="Ulangi password"
                      autocomplete="new-password"
                      required
                      :class="[
                        'w-full px-4 py-2.5 pr-11 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                        registerForm.errors.password_confirmation ? 'border-red-300 bg-red-50' : 'border-gray-200',
                      ]"
                    />
                    <button
                      type="button"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                      @click="showRegPwCfm = !showRegPwCfm"
                    >
                      <svg v-if="!showRegPwCfm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="registerForm.errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ registerForm.errors.password_confirmation }}</p>
                </div>

                <!-- Submit -->
                <button
                  type="submit"
                  :disabled="registerForm.processing"
                  class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm py-3 rounded-xl transition-all shadow-lg shadow-teal/25 hover:-translate-y-px"
                >
                  <svg v-if="registerForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ registerForm.processing ? 'Memproses...' : 'Buat Akun' }}
                </button>

                <!-- Divider -->
                <div class="flex items-center gap-3 my-0.5">
                  <div class="flex-1 h-px bg-gray-100"/>
                  <span class="text-xs text-gray-400">atau</span>
                  <div class="flex-1 h-px bg-gray-100"/>
                </div>

                <!-- Social register buttons -->
                <div class="grid grid-cols-3 gap-2.5">
                  <!-- Google -->
                  <a
                    :href="route('social.redirect', { provider: 'google' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all group"
                    aria-label="Daftar dengan Google"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                      <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                      <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                      <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-gray-700 transition-colors">Google</span>
                  </a>

                  <!-- Facebook -->
                  <a
                    :href="route('social.redirect', { provider: 'facebook' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-[#1877F2]/30 hover:bg-blue-50/50 transition-all group"
                    aria-label="Daftar dengan Facebook"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-[#1877F2] transition-colors">Facebook</span>
                  </a>

                  <!-- TikTok -->
                  <a
                    :href="route('social.redirect', { provider: 'tiktok' })"
                    class="flex flex-col items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-gray-200 hover:border-gray-800 hover:bg-gray-900/5 transition-all group"
                    aria-label="Daftar dengan TikTok"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.53V6.75a4.86 4.86 0 01-1-.06z" fill="currentColor" class="text-gray-800 group-hover:text-gray-900"/>
                    </svg>
                    <span class="text-[10px] font-semibold text-gray-500 group-hover:text-gray-900 transition-colors">TikTok</span>
                  </a>
                </div>

                <!-- Switch to login -->
                <p class="text-center text-xs text-gray-500">
                  Sudah punya akun?
                  <button type="button" class="text-teal font-bold hover:underline" @click="activeTab = 'login'">
                    Masuk di sini
                  </button>
                </p>

              </form>
            </div>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
