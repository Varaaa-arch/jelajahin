<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    LayoutDashboard,
    Plane,
    Settings,
    ShoppingCart,
    User,
    Users,
    Wallet,
} from 'lucide-vue-next';
import AdminTopbar from '@/Components/Admin/AdminTopbar.vue';

const showingSidebar = ref(false);
const activeKey = ref('dashboard');

interface NavItem {
    key: string;
    label: string;
    href: string;
    icon: unknown;
}

const navItems: NavItem[] = [
    { key: 'dashboard', label: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { key: 'flights', label: 'Manage Flights', href: '/admin/flights', icon: Plane },
    { key: 'passengers', label: 'Manage Passengers', href: '/admin/passengers', icon: Users },
    { key: 'orders', label: 'Manage Orders', href: '/admin/dashboard', icon: ShoppingCart },
    { key: 'payments', label: 'Manage Payments', href: '/admin/dashboard', icon: Wallet },
    { key: 'users', label: 'Manage Users', href: '/admin/dashboard', icon: User },
    { key: 'reports', label: 'Reports', href: '/admin/dashboard', icon: BarChart3 },
    { key: 'settings', label: 'Settings', href: '/admin/dashboard', icon: Settings },
];

const page = usePage();
const authUser = computed(() => page.props.auth.user as { name: string; email: string; role?: string } | undefined);

function syncActive(): void {
    const path = window.location.pathname;
    if (path.startsWith('/admin/flights')) {
        activeKey.value = 'flights';
        return;
    }
    if (path.startsWith('/admin/passengers')) {
        activeKey.value = 'passengers';
        return;
    }
    const found = navItems.find((i) => i.key !== 'dashboard' && i.key !== 'flights' && i.key !== 'passengers' && path.includes(i.key));
    activeKey.value = found ? found.key : 'dashboard';
}

onMounted(() => {
    syncActive();
    window.addEventListener('hashchange', syncActive);
});

onUnmounted(() => {
    window.removeEventListener('hashchange', syncActive);
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans">
        <!-- Sidebar desktop -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-navy lg:flex">
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-navy">
                    <Plane class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-lg font-black leading-none text-white">Jelajahi</p>
                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-widest text-teal-300">
                        Enterprise Admin
                    </p>
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="
                        activeKey === item.key
                            ? 'bg-teal text-white shadow-lg shadow-gray-900/20'
                            : 'text-white/60 hover:bg-white/5 hover:text-white'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-white/10 p-3">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/60 transition hover:bg-white/5 hover:text-white"
                >
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- Mobile drawer -->
        <div v-if="showingSidebar" class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-navy/60 backdrop-blur-sm" @click="showingSidebar = false" />
            <div class="absolute inset-y-0 left-0 flex w-72 flex-col bg-navy shadow-2xl">
                <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-navy">
                            <Plane class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-lg font-black leading-none text-white">Jelajahi</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-widest text-teal-300">
                                Enterprise Admin
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white"
                        @click="showingSidebar = false"
                        aria-label="Tutup menu"
                    >
                        X
                    </button>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="
                            activeKey === item.key
                                ? 'bg-teal text-white'
                                : 'text-white/60 hover:bg-white/5 hover:text-white'
                        "
                        @click="showingSidebar = false"
                    >
                        <component :is="item.icon" class="h-5 w-5 shrink-0" />
                        {{ item.label }}
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Main column -->
        <div class="lg:pl-64">
            <AdminTopbar :user-name="authUser?.name ?? 'Admin User'" @open-sidebar="showingSidebar = true" />

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>

            <footer
                class="flex flex-col gap-2 border-t border-gray-200 px-4 py-4 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"
            >
                <p>© 2024 Jelajahi Admin. All rights reserved.</p>
                <div class="flex items-center gap-4 font-semibold text-gray-700">
                    <span>Version 2.4.0</span>
                    <span>Privacy Policy</span>
                    <span>System Status</span>
                </div>
            </footer>
        </div>
    </div>
</template>
