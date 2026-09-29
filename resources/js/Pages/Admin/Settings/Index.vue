<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Megaphone, Settings2, UserRound } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    settings: { site_name: string; support_email: string; tax_rate: string; announcement: string };
    profile: { name: string; email: string };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);
const pageErrors = computed(() => (page.props.errors as Record<string, string> | undefined) ?? {});

const showToast = ref(false);
const toastMessage = ref('');
const toastTone = ref<'success' | 'error'>('success');

watch([flashSuccess, flashError], ([s, e]) => {
    if (s || e) {
        toastTone.value = s ? 'success' : 'error';
        toastMessage.value = (s ?? e) as string;
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 4200);
    }
}, { immediate: true });

const processing = ref<'settings' | 'profile' | 'password' | null>(null);

const settingsForm = ref({ ...props.settings });
const profileForm = ref({ ...props.profile });
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });

function submit(url: string, payload: Record<string, unknown>, key: 'settings' | 'profile' | 'password'): void {
    processing.value = key;
    router.put(url, payload, {
        preserveScroll: true,
        onSuccess: () => {
            if (key === 'password') passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
        },
        onFinish: () => (processing.value = null),
    });
}

function inputClass(): string {
    return 'w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-800 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20';
}

function err(key: string): string | undefined {
    return pageErrors.value[key];
}
</script>

<template>
    <Head title="Settings" />

    <AdminLayout>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Application preferences and your admin profile.</p>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
                    <Settings2 class="h-5 w-5 text-teal-dark" />
                    General
                </h2>
                <form class="mt-4 space-y-4" @submit.prevent="submit('/admin/settings', { ...settingsForm }, 'settings')">
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Site Name</span>
                        <input v-model="settingsForm.site_name" type="text" :class="inputClass()" />
                        <span v-if="err('site_name')" class="mt-1 block text-xs text-red-600">{{ err('site_name') }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Support Email</span>
                        <input v-model="settingsForm.support_email" type="email" :class="inputClass()" />
                        <span v-if="err('support_email')" class="mt-1 block text-xs text-red-600">{{ err('support_email') }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Tax Rate (%)</span>
                        <input v-model="settingsForm.tax_rate" type="number" min="0" max="100" step="0.1" :class="inputClass()" />
                        <span v-if="err('tax_rate')" class="mt-1 block text-xs text-red-600">{{ err('tax_rate') }}</span>
                        <span class="mt-1 block text-xs text-gray-500">Dipakai otomatis saat menghitung total booking baru.</span>
                    </label>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="processing !== null" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                            {{ processing === 'settings' ? 'MENYIMPAN...' : 'SIMPAN PENGATURAN' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
                    <Megaphone class="h-5 w-5 text-teal-dark" />
                    Announcement
                </h2>
                <form class="mt-4 space-y-4" @submit.prevent="submit('/admin/settings', { ...settingsForm }, 'settings')">
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Pesan pengumuman</span>
                        <textarea v-model="settingsForm.announcement" rows="4" maxlength="500" placeholder="Kosongkan untuk menyembunyikan..." :class="inputClass()" />
                        <span v-if="err('announcement')" class="mt-1 block text-xs text-red-600">{{ err('announcement') }}</span>
                    </label>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="processing !== null" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                            {{ processing === 'settings' ? 'MENYIMPAN...' : 'SIMPAN PENGUMUMAN' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
                    <UserRound class="h-5 w-5 text-teal-dark" />
                    My Profile
                </h2>
                <form class="mt-4 space-y-4" @submit.prevent="submit('/admin/settings/profile', { ...profileForm }, 'profile')">
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Name</span>
                        <input v-model="profileForm.name" type="text" :class="inputClass()" />
                        <span v-if="err('name')" class="mt-1 block text-xs text-red-600">{{ err('name') }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Email</span>
                        <input v-model="profileForm.email" type="email" :class="inputClass()" />
                        <span v-if="err('email')" class="mt-1 block text-xs text-red-600">{{ err('email') }}</span>
                        <span class="mt-1 block text-xs text-gray-500">Ganti email akan meminta verifikasi ulang.</span>
                    </label>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="processing !== null" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                            {{ processing === 'profile' ? 'MENYIMPAN...' : 'SIMPAN PROFIL' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 border-b border-gray-200 pb-4 text-base font-black text-gray-900">
                    <UserRound class="h-5 w-5 text-teal-dark" />
                    Change Password
                </h2>
                <form class="mt-4 space-y-4" @submit.prevent="submit('/admin/settings/password', { ...passwordForm }, 'password')">
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Current Password</span>
                        <input v-model="passwordForm.current_password" type="password" :class="inputClass()" />
                        <span v-if="err('current_password')" class="mt-1 block text-xs text-red-600">{{ err('current_password') }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">New Password</span>
                        <input v-model="passwordForm.password" type="password" :class="inputClass()" />
                        <span v-if="err('password')" class="mt-1 block text-xs text-red-600">{{ err('password') }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-semibold text-gray-700">Confirm New Password</span>
                        <input v-model="passwordForm.password_confirmation" type="password" :class="inputClass()" />
                    </label>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="processing !== null" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                            {{ processing === 'password' ? 'MENYIMPAN...' : 'GANTI PASSWORD' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <Transition name="toast-slide">
            <div
                v-if="showToast"
                class="fixed bottom-6 right-6 z-[60] flex max-w-sm items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 shadow-2xl"
            >
                <CircleCheck v-if="toastTone === 'success'" class="mt-0.5 h-5 w-5 shrink-0 text-teal-dark" />
                <CircleAlert v-else class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                <div class="text-sm">
                    <p class="font-bold text-gray-900">{{ toastTone === 'success' ? 'Berhasil' : 'Gagal' }}</p>
                    <p class="mt-0.5 text-gray-600">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.toast-slide-enter-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.toast-slide-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}
.toast-slide-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.97);
}
.toast-slide-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
