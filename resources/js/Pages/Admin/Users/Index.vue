<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Download, Plus } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserFilterBar from '@/Components/Admin/Users/UserFilterBar.vue';
import UserTable from '@/Components/Admin/Users/UserTable.vue';
import UserFormModal from '@/Components/Admin/Users/UserFormModal.vue';
import UserDeleteModal from '@/Components/Admin/Users/UserDeleteModal.vue';
import FlightsPagination from '@/Components/Admin/Flights/FlightsPagination.vue';
import type { AdminUser, AdminUserFilters, PaginatedUsers } from '@/types/admin-user';

const props = defineProps<{
    users: PaginatedUsers;
    filters: AdminUserFilters;
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

const loading = ref(false);
const formOpen = ref(false);
const formMode = ref<'create' | 'edit'>('create');
const selected = ref<AdminUser | null>(null);
const deleteTarget = ref<AdminUser | null>(null);
const formErrors = ref<Record<string, string>>({});
const formProcessing = ref(false);
const deleteProcessing = ref(false);

function queryParams(patch: Partial<AdminUserFilters> = {}, targetPage?: number) {
    return {
        q: props.filters.q,
        role: props.filters.role,
        status: props.filters.status,
        sort: props.filters.sort,
        per_page: props.filters.per_page,
        ...patch,
        ...(targetPage ? { page: targetPage } : {}),
    };
}

function reload(patch: Partial<AdminUserFilters> = {}, targetPage?: number): void {
    loading.value = true;
    router.get('/admin/users', queryParams(patch, targetPage), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['users', 'filters'],
        onFinish: () => (loading.value = false),
    });
}

function resetFilters(): void {
    router.get(
        '/admin/users',
        { per_page: props.filters.per_page },
        { preserveScroll: true, replace: true, only: ['users', 'filters'] },
    );
}

function openCreate(): void {
    formMode.value = 'create';
    selected.value = null;
    formErrors.value = {};
    formOpen.value = true;
}

function openEdit(user: AdminUser): void {
    formMode.value = 'edit';
    selected.value = user;
    formErrors.value = {};
    formOpen.value = true;
}

function submitForm(payload: Record<string, unknown>): void {
    formProcessing.value = true;
    formErrors.value = {};
    if (formMode.value === 'create') {
        router.post('/admin/users', payload, {
            preserveScroll: true,
            onError: (e) => (formErrors.value = e as Record<string, string>),
            onSuccess: () => (formOpen.value = false),
            onFinish: () => (formProcessing.value = false),
        });
    } else if (selected.value) {
        router.put(`/admin/users/${selected.value.id}`, payload, {
            preserveScroll: true,
            onError: (e) => (formErrors.value = e as Record<string, string>),
            onSuccess: () => (formOpen.value = false),
            onFinish: () => (formProcessing.value = false),
        });
    }
}

function submitResetPassword(payload: Record<string, unknown>): void {
    if (!selected.value) return;
    formProcessing.value = true;
    formErrors.value = {};
    router.put(`/admin/users/${selected.value.id}/password`, payload, {
        preserveScroll: true,
        onError: (e) => (formErrors.value = e as Record<string, string>),
        onSuccess: () => (formOpen.value = false),
        onFinish: () => (formProcessing.value = false),
    });
}

function confirmDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/admin/users/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => (deleteTarget.value = null),
        onError: () => {
            toastTone.value = 'error';
            toastMessage.value = 'Gagal menghapus pengguna.';
            showToast.value = true;
        },
        onFinish: () => (deleteProcessing.value = false),
    });
}

function exportCsv(): void {
    const rows = [
        ['Name', 'Email', 'Role', 'Status', 'Bookings', 'Joined'],
        ...props.users.data.map((u) => [
            u.name,
            u.email,
            u.role,
            u.status,
            String(u.bookings_count),
            u.joined_at ?? '',
        ]),
    ];
    const csv = rows
        .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `users-page-${props.users.current_page}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toastTone.value = 'success';
    toastMessage.value = `${props.users.data.length} data pengguna diekspor ke CSV.`;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 4200);
}

const offset = computed(() => (props.users.from ? props.users.from - 1 : 0));
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AdminLayout>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Pengguna</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all registered users across the platform.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 self-start">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-800 bg-white px-4 py-2.5 text-xs font-bold tracking-wide text-gray-900 transition hover:bg-gray-900 hover:text-white"
                    @click="exportCsv"
                >
                    <Download class="h-4 w-4" />
                    EXPORT USERS
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-navy px-4 py-2.5 text-xs font-bold tracking-wide text-white transition hover:bg-navy-mid"
                    @click="openCreate"
                >
                    <Plus class="h-4 w-4" />
                    TAMBAH PENGGUNA
                </button>
            </div>
        </div>

        <div class="mt-5">
            <UserFilterBar :filters="filters" @change="reload" @reset="resetFilters" />
        </div>

        <div class="mt-4">
            <UserTable :users="users.data" :loading="loading" :offset="offset" @edit="openEdit" @delete="(u) => (deleteTarget = u)" />
            <FlightsPagination
                :meta="users"
                item-label="pengguna"
                show-per-page
                @page="(p) => reload({}, p)"
                @per-page="(n) => reload({ per_page: n })"
            />
        </div>

        <UserFormModal
            :open="formOpen"
            :mode="formMode"
            :user="selected"
            :processing="formProcessing"
            :errors="{ ...formErrors, ...pageErrors }"
            @close="formOpen = false"
            @submit="submitForm"
            @reset-password="submitResetPassword"
        />

        <UserDeleteModal
            :open="!!deleteTarget"
            :user="deleteTarget"
            :processing="deleteProcessing"
            @close="deleteTarget = null"
            @confirm="confirmDelete"
        />

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
