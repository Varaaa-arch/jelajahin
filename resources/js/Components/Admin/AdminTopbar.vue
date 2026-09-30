<script setup lang="ts">
import { History, Search } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

defineProps<{
    userName: string;
}>();

defineEmits<{
    (e: 'open-sidebar'): void;
}>();
</script>

<template>
    <nav
        class="sticky top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-gray-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8"
    >
        <div class="flex flex-1 items-center gap-3">
            <button
                type="button"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
                @click="$emit('open-sidebar')"
                aria-label="Buka menu"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="relative w-full max-w-md">
                <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    type="text"
                    placeholder="Search flights, passengers,"
                    class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20"
                />
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-4">
            <NotificationBell />
            <Link
                href="/notifications"
                class="rounded-full p-2 text-gray-600 hover:bg-gray-100"
                aria-label="Riwayat"
            >
                <History class="h-5 w-5" />
            </Link>
            <div class="hidden h-8 w-px bg-gray-200 sm:block" />
            <div class="flex items-center gap-3">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-navy text-sm font-black text-white"
                >
                    {{ (userName.charAt(0) || 'A').toUpperCase() }}
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-bold text-gray-900">{{ userName }}</span>
                    <span class="block text-xs text-gray-500">System Superuser</span>
                </span>
            </div>
        </div>
    </nav>
</template>
