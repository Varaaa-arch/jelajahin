<script setup lang="ts">
import { BarChart3, Plus, Settings, UserCog } from 'lucide-vue-next';

const actions = [
    { label: 'Add Flight', icon: Plus, primary: true },
    { label: 'View Reports', icon: BarChart3, primary: false },
    { label: 'Manage Users', icon: UserCog, primary: false },
    { label: 'Settings', icon: Settings, primary: false },
];
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <p class="text-sm text-gray-700">Quick administrative actions:</p>
        <div class="flex flex-wrap gap-2.5">
            <button
                v-for="a in actions"
                :key="a.label"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold tracking-wide transition"
                :class="
                    a.primary
                        ? 'bg-navy text-white hover:bg-navy-mid'
                        : 'border border-gray-300 bg-white text-gray-600 hover:border-teal hover:text-teal-dark'
                "
            >
                <component :is="a.icon" class="h-4 w-4" />
                {{ a.label.toUpperCase() }}
            </button>
        </div>
    </div>
</template>
