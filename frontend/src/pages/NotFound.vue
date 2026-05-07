<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 text-center px-4 relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary-100 rounded-full blur-3xl opacity-50" />
      <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-violet-100 rounded-full blur-3xl opacity-40" />
    </div>

    <div class="relative">
      <p class="text-9xl font-extrabold bg-gradient-to-r from-primary-200 to-violet-200 bg-clip-text text-transparent leading-none mb-4">
        404
      </p>
      <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Page not found</h1>
      <p class="text-gray-500 mb-8 max-w-sm">
        Sorry, we couldn't find the page you're looking for. It may have moved or doesn't exist.
      </p>
      <RouterLink to="/" class="btn-primary !px-8 !py-3 text-base">
        Back to Home
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
