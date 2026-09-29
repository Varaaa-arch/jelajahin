<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { KeyRound, X } from 'lucide-vue-next';
import type { AdminUser } from '@/types/admin-user';

const props = defineProps<{
    open: boolean;
    mode: 'create' | 'edit';
    user: AdminUser | null;
    processing?: boolean;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'submit', payload: any): void;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (e: 'reset-password', payload: any): void;
}>();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    status: 'active',
});

const showPasswordReset = ref(false);
const resetForm = reactive({ password: '', password_confirmation: '' });

function resetAll(): void {
    form.name = props.user?.name ?? '';
    form.email = props.user?.email ?? '';
    form.password = '';
    form.password_confirmation = '';
    form.role = props.user?.role ?? 'user';
    form.status = props.user?.status ?? 'active';
    showPasswordReset.value = false;
    resetForm.password = '';
    resetForm.password_confirmation = '';
}

watch(() => props.open, (v) => { if (v) resetAll(); }, { immediate: true });

function submit(): void {
    if (props.mode === 'create') {
        emit('submit', { ...form });
        return;
    }
    emit('submit', { name: form.name, role: form.role, status: form.status });
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-navy/60 p-4 backdrop-blur-sm sm:items-center"
                @click.self="emit('close')"
            >
                <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <div>
                            <h3 class="text-base font-black text-gray-900">
                                {{ mode === 'create' ? 'Tambah Pengguna' : 'Ubah Pengguna' }}
                            </h3>
                            <p v-if="user" class="mt-0.5 text-xs text-gray-500">{{ user.email }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                            aria-label="Tutup"
                            @click="emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form class="space-y-4 px-6 py-5" @submit.prevent="submit">
                        <label class="block text-sm">
                            <span class="mb-1 block font-semibold text-gray-700">Nama Lengkap</span>
                            <input v-model="form.name" type="text" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                            <span v-if="errors?.name" class="mt-1 block text-xs text-red-600">{{ errors.name }}</span>
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-semibold text-gray-700">Email</span>
                            <input
                                v-model="form.email"
                                type="email"
                                :required="mode === 'create'"
                                :disabled="mode === 'edit'"
                                placeholder="nama@example.com"
                                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20 disabled:bg-gray-100 disabled:text-gray-500"
                            />
                            <span v-if="errors?.email" class="mt-1 block text-xs text-red-600">{{ errors.email }}</span>
                        </label>

                        <template v-if="mode === 'create'">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block text-sm">
                                    <span class="mb-1 block font-semibold text-gray-700">Password</span>
                                    <input v-model="form.password" type="password" required minlength="8" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                                    <span v-if="errors?.password" class="mt-1 block text-xs text-red-600">{{ errors.password }}</span>
                                </label>
                                <label class="block text-sm">
                                    <span class="mb-1 block font-semibold text-gray-700">Konfirmasi Password</span>
                                    <input v-model="form.password_confirmation" type="password" required minlength="8" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                                </label>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block text-sm">
                                <span class="mb-1 block font-semibold text-gray-700">Tipe</span>
                                <select v-model="form.role" :disabled="user?.is_self" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20 disabled:bg-gray-100">
                                    <option value="user">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <span v-if="errors?.role" class="mt-1 block text-xs text-red-600">{{ errors.role }}</span>
                            </label>
                            <label v-if="mode === 'edit'" class="block text-sm">
                                <span class="mb-1 block font-semibold text-gray-700">Status</span>
                                <select v-model="form.status" :disabled="user?.is_self" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20 disabled:bg-gray-100">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <span v-if="errors?.status" class="mt-1 block text-xs text-red-600">{{ errors.status }}</span>
                            </label>
                        </div>
                        <p v-if="user?.is_self" class="text-xs text-gray-500">Role/status akun sendiri tidak dapat diubah.</p>

                        <div v-if="mode === 'edit'" class="rounded-xl border border-gray-200 p-4">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-bold text-teal-dark hover:underline"
                                @click="showPasswordReset = !showPasswordReset"
                            >
                                <KeyRound class="h-4 w-4" />
                                {{ showPasswordReset ? 'Tutup reset password' : 'Reset password' }}
                            </button>
                            <div v-if="showPasswordReset" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <input v-model="resetForm.password" type="password" placeholder="Password baru (min 8)" minlength="8" class="rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                                <input v-model="resetForm.password_confirmation" type="password" placeholder="Konfirmasi" minlength="8" class="rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" />
                                <p v-if="errors?.password" class="text-xs text-red-600 sm:col-span-2">{{ errors.password }}</p>
                                <div class="sm:col-span-2">
                                    <button
                                        type="button"
                                        :disabled="processing || resetForm.password.length < 8"
                                        class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700 disabled:opacity-50"
                                        @click="emit('reset-password', { ...resetForm })"
                                    >
                                        SIMPAN PASSWORD BARU
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50" @click="emit('close')">
                                BATAL
                            </button>
                            <button type="submit" :disabled="processing" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:bg-navy-mid disabled:opacity-50">
                                {{ processing ? 'MENYIMPAN...' : mode === 'create' ? 'TAMBAH PENGGUNA' : 'SIMPAN PERUBAHAN' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
