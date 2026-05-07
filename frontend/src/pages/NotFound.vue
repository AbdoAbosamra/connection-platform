<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center px-4">
    <p class="text-8xl font-bold text-primary-200">404</p>
    <h1 class="text-2xl font-bold text-gray-900 mt-4">Page not found</h1>
    <p class="text-gray-500 mt-2">Sorry, we couldn't find the page you're looking for.</p>
    <RouterLink to="/" class="btn-primary mt-6">Back to Home</RouterLink>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
