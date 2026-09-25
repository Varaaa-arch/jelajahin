<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps<{
    email: string
    token: string
}>()

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showConfirm = ref(false)

const passwordStrength = computed(() => {
    const pw = form.password
    let score = 0
    if (pw.length >= 8) score++
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++
    if (/\d/.test(pw)) score++
    if (/[^a-zA-Z0-9]/.test(pw)) score++
    return score
})

const strengthLabel = computed(() => {
    const labels = ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat']
    return labels[passwordStrength.value] ?? ''
})

const strengthColor = computed(() => {
    const colors = ['', 'bg-red-500', 'bg-yellow-500', 'bg-teal', 'bg-teal']
    return colors[passwordStrength.value] ?? ''
})

const isStrongEnough = computed(() => passwordStrength.value >= 2)

function submit() {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Atur Ulang Password" />

        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-teal/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-teal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900">Atur Ulang Password</h1>
            <p class="text-sm text-gray-500 mt-2">Masukkan password baru untuk {{ email }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <input type="hidden" v-model="form.token" />
            <input type="hidden" v-model="form.email" />

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="password">Password Baru</label>
                <div class="relative">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        required
                        :class="[
                            'w-full px-4 py-2.5 pr-11 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                            form.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200',
                        ]"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        @click="showPassword = !showPassword"
                    >
                        <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>

                <div v-if="form.password" class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors" :class="i <= passwordStrength.value ? strengthColor : ''"></div>
                    </div>
                    <p class="text-[11px] text-gray-400">{{ strengthLabel }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="password_confirmation">Konfirmasi Password</label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                        required
                        :class="[
                            'w-full px-4 py-2.5 pr-11 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                            form.errors.password_confirmation ? 'border-red-300 bg-red-50' : 'border-gray-200',
                        ]"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        :aria-label="showConfirm ? 'Sembunyikan password' : 'Tampilkan password'"
                        @click="showConfirm = !showConfirm"
                    >
                        <svg v-if="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ form.errors.password_confirmation }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing || !isStrongEnough"
                class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm py-3 rounded-xl transition-all shadow-lg shadow-teal/25 hover:-translate-y-px"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ form.processing ? 'Mengatur ulang...' : 'Atur Ulang Password' }}
            </button>

            <p class="text-center text-xs text-gray-400">
                <a :href="route('login')" class="text-teal font-bold hover:underline">Kembali ke login</a>
            </p>
        </form>
    </GuestLayout>
</template>
