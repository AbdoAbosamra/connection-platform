<template>
  <div class="min-h-screen flex flex-col bg-gray-950">
    <!-- Navbar -->
    <header class="bg-gray-900/80 backdrop-blur-md border-b border-gray-800/60 sticky top-0 z-30 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <img :src="'/logo.png'" onerror="this.onerror=null;this.src='/logo.svg'" alt="RemoteArena" class="h-16 w-16 object-contain" />
          <span class="text-xl font-bold text-white">
            RemoteArena
          </span>
        </RouterLink>

        <!-- Nav links -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-400">
          <RouterLink
            to="/jobs"
            class="hover:text-amber-400 transition-colors py-1 border-b-2 border-transparent hover:border-amber-400/50"
            :class="{ 'text-amber-400 !border-amber-400/70': $route.path.startsWith('/jobs') }"
          >
            {{ lang.t('nav.opportunities') }}
          </RouterLink>
          <RouterLink
            to="/professionals"
            class="hover:text-amber-400 transition-colors py-1 border-b-2 border-transparent hover:border-amber-400/50"
            :class="{ 'text-amber-400 !border-amber-400/70': $route.path.startsWith('/professionals') }"
          >
            {{ lang.t('nav.findProfessionals') }}
          </RouterLink>
          <RouterLink
            to="/about"
            class="hover:text-amber-400 transition-colors py-1 border-b-2 border-transparent hover:border-amber-400/50"
            :class="{ 'text-amber-400 !border-amber-400/70': $route.path === '/about' }"
          >
            {{ lang.t('nav.about') }}
          </RouterLink>
          <RouterLink
            to="/faq"
            class="hover:text-amber-400 transition-colors py-1 border-b-2 border-transparent hover:border-amber-400/50"
            :class="{ 'text-amber-400 !border-amber-400/70': $route.path === '/faq' }"
          >
            {{ lang.t('nav.faq') }}
          </RouterLink>
        </nav>

        <!-- Right side -->
        <div class="flex items-center gap-2 sm:gap-3">
          <template v-if="!auth.isAuthenticated">
            <RouterLink to="/login" class="btn-secondary text-sm !px-4 !py-2">{{ lang.t('nav.login') }}</RouterLink>
            <RouterLink to="/register" class="btn-primary text-sm !px-4 !py-2">
              {{ lang.t('nav.getStarted') }}
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </RouterLink>
          </template>
          <RouterLink v-else :to="auth.dashboardPath()" class="btn-primary text-sm !px-4 !py-2">
            Dashboard →
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800/60 text-gray-400 text-sm py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <!-- Brand -->
          <div class="flex flex-col items-center md:items-start gap-2">
            <div class="flex items-center gap-2.5">
              <img :src="'/logo.png'" onerror="this.onerror=null;this.src='/logo.svg'" alt="RemoteArena" class="h-10 w-10 object-contain" />
              <span class="font-bold text-white text-lg">RemoteArena</span>
            </div>
            <p class="text-gray-500 text-xs">{{ lang.t('footer.tagline') }}</p>
          </div>

          <!-- Links -->
          <div class="flex flex-wrap items-center justify-center gap-6 text-xs">
            <RouterLink to="/jobs" class="hover:text-white transition-colors">{{ lang.t('nav.opportunities') }}</RouterLink>
            <RouterLink to="/professionals" class="hover:text-white transition-colors">{{ lang.t('nav.findProfessionals') }}</RouterLink>
            <RouterLink to="/about" class="hover:text-white transition-colors">{{ lang.t('footer.about') }}</RouterLink>
            <RouterLink to="/faq" class="hover:text-white transition-colors">{{ lang.t('footer.faq') }}</RouterLink>
            <RouterLink to="/register" class="hover:text-white transition-colors">{{ lang.t('footer.signUp') }}</RouterLink>
            <span class="w-px h-3 bg-gray-700 hidden sm:block" />
            <RouterLink to="/privacy" class="hover:text-white transition-colors text-gray-500">Privacy Policy</RouterLink>
            <RouterLink to="/terms" class="hover:text-white transition-colors text-gray-500">Terms &amp; Conditions</RouterLink>
            <RouterLink to="/community-guidelines" class="hover:text-white transition-colors text-gray-500">Community Guidelines</RouterLink>
          </div>

          <p class="text-gray-600 text-xs">© {{ new Date().getFullYear() }} RemoteArena Inc. {{ lang.t('footer.rights') }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useLanguageStore } from '@/stores/language'
import { useAuthStore } from '@/stores/auth'

const lang = useLanguageStore()
const auth = useAuthStore()
</script>
