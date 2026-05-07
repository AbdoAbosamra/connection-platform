<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Dark Sidebar -->
    <aside class="w-64 bg-gray-950 flex flex-col fixed h-full z-20">
      <!-- Logo -->
      <div class="h-16 flex items-center px-5 border-b border-white/[0.06]">
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-violet-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-900/50 group-hover:shadow-primary-500/40 transition-shadow">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </div>
          <span class="text-lg font-bold text-white">Connextion</span>
        </RouterLink>
      </div>

      <!-- Nav section label -->
      <div class="px-5 pt-5 pb-1">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500">Navigation</p>
      </div>

      <!-- Nav items -->
      <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-0.5">
        <template v-for="item in navItems" :key="item.to">
          <RouterLink
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:bg-white/[0.06] hover:text-white transition-all duration-150"
            active-class="!bg-gradient-to-r !from-primary-600/90 !to-violet-600/90 !text-white shadow-lg shadow-primary-900/50"
          >
            <component :is="item.icon" class="w-[18px] h-[18px] flex-shrink-0" />
            {{ item.label }}
          </RouterLink>
        </template>
      </nav>

      <!-- User section -->
      <div class="px-3 py-4 border-t border-white/[0.06] space-y-1">
        <!-- User info -->
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/[0.04] mb-1">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-lg shadow-primary-900/40">
            {{ userInitial }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</p>
          </div>
        </div>
        <!-- Sign out -->
        <button
          @click="auth.logout()"
          class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-gray-500 hover:text-red-400 rounded-xl hover:bg-red-500/10 transition-all duration-150"
        >
          <ArrowRightOnRectangleIcon class="w-4 h-4" />
          Sign out
        </button>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
      <!-- Top bar -->
      <header class="h-16 bg-white/80 backdrop-blur-sm border-b border-gray-200/80 flex items-center justify-between px-6 sticky top-0 z-10 shadow-sm">
        <h1 class="text-lg font-bold text-gray-900">{{ pageTitle }}</h1>
        <!-- Optional right-side slot -->
        <slot name="header-right" />
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 max-w-screen-2xl">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon, BriefcaseIcon, UserIcon, ChatBubbleLeftRightIcon,
  ClipboardDocumentListIcon, BookmarkIcon, UsersIcon,
  ChartBarIcon, FlagIcon, ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const auth  = useAuthStore()
const route = useRoute()

const userInitial = computed(() => auth.user?.name?.[0]?.toUpperCase() ?? '?')
const pageTitle   = computed(() => route.meta?.title ?? '')

const employerNav = [
  { to: '/employer/dashboard',    label: 'Dashboard',       icon: HomeIcon },
  { to: '/employer/jobs',         label: 'My Jobs',         icon: BriefcaseIcon },
  { to: '/employer/applications', label: 'Applications',    icon: ClipboardDocumentListIcon },
  { to: '/employer/messages',     label: 'Messages',        icon: ChatBubbleLeftRightIcon },
  { to: '/employer/profile',      label: 'Company Profile', icon: UserIcon },
]

const seekerNav = [
  { to: '/job-seeker/dashboard',    label: 'Dashboard',    icon: HomeIcon },
  { to: '/jobs',                    label: 'Browse Jobs',  icon: BriefcaseIcon },
  { to: '/job-seeker/applications', label: 'Applications', icon: ClipboardDocumentListIcon },
  { to: '/job-seeker/saved',        label: 'Saved Jobs',   icon: BookmarkIcon },
  { to: '/job-seeker/messages',     label: 'Messages',     icon: ChatBubbleLeftRightIcon },
  { to: '/job-seeker/profile',      label: 'My Profile',   icon: UserIcon },
]

const adminNav = [
  { to: '/admin/dashboard', label: 'Dashboard', icon: ChartBarIcon },
  { to: '/admin/users',     label: 'Users',     icon: UsersIcon },
  { to: '/admin/jobs',      label: 'Jobs',      icon: BriefcaseIcon },
  { to: '/admin/reports',   label: 'Reports',   icon: FlagIcon },
]

const navItems = computed(() => {
  if (auth.isAdmin)    return adminNav
  if (auth.isEmployer) return employerNav
  return seekerNav
})
</script>
