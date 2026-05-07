<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center px-4 py-12 relative overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-100 rounded-full blur-3xl opacity-50 pointer-events-none" />
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-violet-100 rounded-full blur-3xl opacity-50 pointer-events-none" />
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-50 rounded-full blur-3xl opacity-30 pointer-events-none" />

    <!-- Logo -->
    <RouterLink to="/" class="mb-10 flex items-center gap-3 relative group">
      <div class="w-11 h-11 bg-gradient-to-br from-primary-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-xl shadow-primary-500/40 group-hover:shadow-primary-500/60 transition-shadow">
        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
        </svg>
      </div>
      <span class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-violet-600 bg-clip-text text-transparent">
        Connextion
      </span>
    </RouterLink>

    <!-- Card -->
    <div class="w-full max-w-md relative">
      <div class="bg-white rounded-3xl shadow-lifted border border-gray-100 p-8 animate-fade-in">
        <RouterView />
      </div>
      <!-- Bottom decorative line -->
      <div class="h-1 mx-8 bg-gradient-to-r from-primary-400 to-violet-500 rounded-b-full opacity-60" />
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
