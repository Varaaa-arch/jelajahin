<script setup lang="ts">
import { computed } from 'vue';
import { CircleAlert, CircleCheck, Info, TriangleAlert } from 'lucide-vue-next';

export interface AlertItem {
    key: string;
    title: string;
    message: string;
    tone: 'amber' | 'red' | 'blue' | 'emerald';
    icon: string;
}

defineProps<{
    alerts: AlertItem[];
}>();

const iconMap: Record<string, unknown> = {
    TriangleAlert,
    CircleAlert,
    Info,
    CircleCheck,
};

const toneClasses: Record<AlertItem['tone'], string> = {
    amber: 'border-amber-200 bg-amber-100 text-amber-800',
    red: 'border-red-200 bg-red-100 text-red-800',
    blue: 'border-blue-200 bg-blue-100 text-blue-800',
    emerald: 'border-emerald-200 bg-emerald-100 text-emerald-800',
};

const iconFor = (icon: string) => computed(() => iconMap[icon] ?? Info).value;
</script>

<template>
    <section>
        <h3 class="text-base font-bold text-gray-900">System Alerts and Notifications</h3>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="a in alerts"
                :key="a.key"
                class="flex gap-3 rounded-xl border p-4"
                :class="toneClasses[a.tone]"
            >
                <component :is="iconFor(a.icon)" class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="text-xs font-bold">{{ a.title }}</p>
                    <p class="mt-1 text-xs leading-relaxed opacity-90">{{ a.message }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
