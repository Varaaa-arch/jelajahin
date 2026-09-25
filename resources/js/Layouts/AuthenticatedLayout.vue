<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const showingSidebar = ref(false);
const activeLink = ref('dashboard');

interface LinkItem {
    key: string;
    label: string;
    href: string;
    hash?: string;
    icon: string;
}

const navLinks: LinkItem[] = [
    {
        key: 'dashboard',
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4h6v4a1 1 0 001 1h3a1 1 0 001-1V10',
    },
    {
        key: 'pemesanan',
        label: 'Pemesanan Saya',
        href: '/dashboard',
        hash: '#pemesanan',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    },
    {
        key: 'eticket',
        label: 'E-Ticket & Invoice',
        href: '/dashboard',
        hash: '#eticket',
        icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 110 2v3a2 2 0 002 2h14a2 2 0 002-2v-3a1 1 0 110-2V7a2 2 0 00-2-2H5zM8.5 12h.01M12 12h.01M15.5 12h.01',
    },
    {
        key: 'profil',
        label: 'Profil',
        href: '/profile',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
];

function syncActiveLink(): void {
    const hash = window.location.hash;
    const path = window.location.pathname;

    if (hash === '#pemesanan') {
        activeLink.value = 'pemesanan';
    } else if (hash === '#eticket') {
        activeLink.value = 'eticket';
    } else if (path === '/dashboard') {
        activeLink.value = 'dashboard';
    } else if (path.startsWith('/profile')) {
        activeLink.value = 'profil';
    }
}

onMounted(() => {
    syncActiveLink();
    window.addEventListener('hashchange', syncActiveLink);
});

onUnmounted(() => {
    window.removeEventListener('hashchange', syncActiveLink);
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar (desktop) -->
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-navy lg:flex"
        >
            <div class="flex h-16 items-center border-b border-white/10 px-5">
                <img src="/images/logo.png" alt="Jelajahin" class="h-28 w-auto brightness-0 invert" />
            </div>

            <nav class="flex flex-col flex-1 overflow-y-auto px-3 py-4 gap-0.5">
                <span class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-white/40">
                    Menu
                </span>
                <Link
                    v-for="item in navLinks"
                    :key="item.key"
                    :href="item.href + (item.hash ?? '')"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="
                        activeLink === item.key
                            ? 'bg-teal text-white shadow-lg shadow-gray-900/10'
                            : 'text-white/60 hover:bg-white/5 hover:text-white'
                    "
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :d="item.icon"
                        />
                    </svg>
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
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- Mobile drawer -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-40 lg:hidden"
        >
            <div
                class="absolute inset-0 bg-navy/60 backdrop-blur-sm"
                @click="showingSidebar = false"
            />
            <div class="absolute inset-y-0 left-0 flex w-72 flex-col bg-navy shadow-2xl">
                <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Jelajahin" class="h-28 w-auto brightness-0 invert" />
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-2 text-white/60 transition hover:bg-white/10 hover:text-white"
                        @click="showingSidebar = false"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <Link
                        v-for="item in navLinks"
                        :key="item.key"
                        :href="item.href + (item.hash ?? '')"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="
                            activeLink === item.key
                                ? 'bg-teal text-white shadow-lg shadow-gray-900/10'
                                : 'text-white/60 hover:bg-white/5 hover:text-white'
                        "
                        @click="showingSidebar = false"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :d="item.icon"
                            />
                        </svg>
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="border-t border-white/10 p-3">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/60 transition hover:bg-white/5 hover:text-white"
                        @click="showingSidebar = false"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />
                        </svg>
                        Keluar
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main column -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <nav class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden"
                        @click="showingSidebar = true"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-400">Jelajahin</span>
                        <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-semibold text-gray-800">
                            <slot name="header">
                                Dashboard
                            </slot>
                        </span>
                    </div>
                </div>

                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-full p-1 pr-3 transition hover:bg-gray-100 focus:outline-none"
                            >
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-teal font-black uppercase text-white shadow-md shadow-gray-900/10"
                                >
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block text-sm font-bold text-gray-800">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <span class="block text-xs text-gray-500">
                                        Member Jelajahin
                                    </span>
                                </span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profil
                            </DropdownLink>
                            <DropdownLink
                                href="/logout"
                                method="post"
                                as="button"
                            >
                                Keluar
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <main class="px-4 py-8 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>