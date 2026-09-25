<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, Inertia } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import OtpModal from '@/Components/OtpModal.vue'

const props = defineProps<{
    status?: string
}>()

const form = useForm({
    email: '',
})

const showOtp = ref(false)
const emailSubmitted = ref('')
const otpVerified = ref(false)

const sendRoute = () => route('password.email')
const verifyRoute = () => route('password.otp.verify')

function submit() {
    form.post(route('password.email'), {
        onSuccess: () => {
            emailSubmitted.value = form.email
            showOtp.value = true
        },
    })
}

function handleOtpVerified(payload: { token: string | null; email: string }) {
    if (payload.token) {
        Inertia.get(route('password.reset', payload.token), {
            email: payload.email,
        })
    }
}
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-teal/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-teal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900">Lupa Password?</h1>
            <p class="text-sm text-gray-500 mt-2">Masukkan email Anda dan kami kirim kode OTP untuk mengatur ulang password.</p>
        </div>

        <div>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5" for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="email@contoh.com"
                        autocomplete="email"
                        required
                        autofocus
                        :class="[
                            'w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal transition-all',
                            form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200',
                        ]"
                    />
                    <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm py-3 rounded-xl transition-all shadow-lg shadow-teal/25 hover:-translate-y-px"
                >
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    {{ form.processing ? 'Mengirim...' : 'Kirim Kode OTP' }}
                </button>

                <p class="text-center text-xs text-gray-400">
                    Sudah ingat password?
                    <a :href="route('login')" class="text-teal font-bold hover:underline">Masuk sekarang</a>
                </p>
            </form>

            <p v-if="status" class="mt-4 text-sm font-medium text-green-600 text-center">
                {{ status }}
            </p>
        </div>

        <OtpModal
            :show="showOtp"
            :email="emailSubmitted"
            :send-route="sendRoute"
            :verify-route="verifyRoute"
            @verified="handleOtpVerified"
            @close="showOtp = false"
        />
    </GuestLayout>
</template>
