<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-navy/5 via-white to-navy/5 px-4 py-12 sm:py-0"
    >
        <div class="mb-8 text-center">
            <Link href="/" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 bg-teal rounded-2xl flex items-center justify-center shadow-lg shadow-teal/30 group-hover:shadow-teal/50 transition-shadow">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
                    </svg>
                </div>
                <span class="text-2xl font-black text-gray-900 tracking-tight">Jelajahin</span>
            </Link>
        </div>

        <div
            class="w-full overflow-hidden bg-white rounded-3xl shadow-2xl px-8 py-8 sm:max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
