<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import AppFooter from '@/Components/Landing/AppFooter.vue'
import AboutHero from '@/Components/Landing/AboutHero.vue'
import AboutStory from '@/Components/Landing/AboutStory.vue'
import AboutStats from '@/Components/Landing/AboutStats.vue'
import AboutFoundation from '@/Components/Landing/AboutFoundation.vue'
import AboutCTA from '@/Components/Landing/AboutCTA.vue'
</script>

<template>
  <Head title="Tentang Kami" />

  <div class="min-h-screen bg-white font-sans">
    <Navbar />

    <!-- pt-16 untuk offset fixed navbar -->
    <main class="pt-16">
      <AboutHero />
      <AboutStory />
      <AboutStats />
      <AboutFoundation />
      <AboutCTA />
    </main>

    <AppFooter />
  </div>
</template>
