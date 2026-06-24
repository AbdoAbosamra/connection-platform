<template>
  <div class="min-h-screen bg-gray-950 flex flex-col items-center justify-center px-4 py-12 relative overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-900/30 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-violet-900/30 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-950/20 rounded-full blur-3xl pointer-events-none" />

    <!-- Logo -->
    <RouterLink to="/" class="mb-10 flex items-center gap-3 relative group">
      <img :src="'/logo.png'" onerror="this.onerror=null;this.src='/logo.svg'" alt="RemoteArena" class="h-16 w-16 object-contain drop-shadow-xl" />
      <span class="text-2xl font-bold text-white">
        RemoteArena
      </span>
    </RouterLink>

    <!-- Card -->
    <div class="w-full max-w-md relative">
      <div class="bg-gray-900 rounded-3xl shadow-2xl border border-gray-800 p-8 animate-fade-in">
        <RouterView />
      </div>
      <!-- Bottom decorative line -->
      <div class="h-1 mx-8 bg-gradient-to-r from-amber-400 to-yellow-300 rounded-b-full opacity-60" />
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
