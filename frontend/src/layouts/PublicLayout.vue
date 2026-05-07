<template>
  <div class="min-h-screen flex flex-col bg-slate-50">
    <!-- Navbar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200/60 sticky top-0 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30 group-hover:shadow-primary-500/50 transition-shadow">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </div>
          <span class="text-xl font-bold bg-gradient-to-r from-primary-600 to-violet-600 bg-clip-text text-transparent">
            Connextion
          </span>
        </RouterLink>

        <!-- Nav links -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
          <RouterLink to="/jobs" class="hover:text-primary-600 transition-colors py-1 border-b-2 border-transparent hover:border-primary-300">
            Browse Jobs
          </RouterLink>
          <a href="#employers" class="hover:text-primary-600 transition-colors py-1 border-b-2 border-transparent hover:border-primary-300">
            For Employers
          </a>
        </nav>

        <!-- Auth buttons -->
        <div class="flex items-center gap-3">
          <RouterLink to="/login" class="btn-secondary text-sm !px-4 !py-2">Log in</RouterLink>
          <RouterLink to="/register" class="btn-primary text-sm !px-4 !py-2">
            Get started
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 text-sm py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <!-- Brand -->
          <div class="flex flex-col items-center md:items-start gap-2">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-violet-600 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
              </div>
              <span class="font-bold text-white text-lg">Connextion</span>
            </div>
            <p class="text-gray-500 text-xs">Connecting global talent with US opportunities.</p>
          </div>

          <!-- Links -->
          <div class="flex items-center gap-8 text-xs">
            <RouterLink to="/jobs" class="hover:text-white transition-colors">Browse Jobs</RouterLink>
            <a href="#employers" class="hover:text-white transition-colors">For Employers</a>
            <RouterLink to="/register" class="hover:text-white transition-colors">Sign Up</RouterLink>
          </div>

          <p class="text-gray-600 text-xs">© {{ new Date().getFullYear() }} Connextion Inc. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
