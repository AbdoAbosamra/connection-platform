<template>
  <div class="min-h-screen bg-gray-950 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-950 border-r border-gray-800/60 flex flex-col fixed h-full z-20">
      <!-- Logo -->
      <div class="h-16 flex items-center px-5 border-b border-gray-800/60">
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <img :src="'/logo.png'" onerror="this.onerror=null;this.src='/logo.svg'" alt="RemoteArena" class="h-12 w-12 object-contain" />
          <span class="text-lg font-bold text-white">
            RemoteArena
          </span>
        </RouterLink>
      </div>

      <!-- Nav section label -->
      <div class="px-5 pt-5 pb-1">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Navigation</p>
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
            <span class="flex-1">{{ item.label }}</span>
            <!-- Unread badge — only on Messages link -->
            <span
              v-if="item.badge && totalUnread > 0"
              class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[9px] font-bold bg-rose-500 text-white rounded-full"
            >
              {{ totalUnread > 99 ? '99+' : totalUnread }}
            </span>
          </RouterLink>
        </template>
      </nav>

      <!-- User section -->
      <div class="px-3 py-4 border-t border-gray-800/60 space-y-1">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/[0.04] mb-1">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-lg shadow-primary-900/40">
            {{ userInitial }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</p>
          </div>
        </div>
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
      <header class="h-16 bg-gray-900/80 backdrop-blur-sm border-b border-gray-800/60 flex items-center justify-between px-6 sticky top-0 z-10">
        <h1 class="text-lg font-bold text-gray-100">{{ pageTitle }}</h1>
        <div class="flex items-center gap-3">
          <slot name="header-right" />
          <NotificationBell />
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 max-w-screen-2xl">
        <!-- Verification banner for unverified employers -->
        <RouterLink
          v-if="showVerifyBanner"
          to="/employer/verification"
          class="flex items-center gap-3 mb-5 rounded-xl border border-amber-500/30 bg-amber-500/[0.08] px-4 py-3 text-sm text-amber-200 hover:bg-amber-500/[0.12] transition-colors"
        >
          <ExclamationTriangleIcon class="w-5 h-5 flex-shrink-0" />
          <span class="flex-1">Your company isn't verified yet — verify now to start posting jobs.</span>
          <span class="font-semibold">Verify →</span>
        </RouterLink>
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useMessagesStore } from '@/stores/messages'
import NotificationBell from '@/components/NotificationBell.vue'
import {
  HomeIcon, BriefcaseIcon, UserIcon, ChatBubbleLeftRightIcon,
  ClipboardDocumentListIcon, BookmarkIcon, UsersIcon,
  ChartBarIcon, FlagIcon, ArrowRightOnRectangleIcon,
  PresentationChartLineIcon, CreditCardIcon, CalendarDaysIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const auth     = useAuthStore()
const route    = useRoute()
const msgStore = useMessagesStore()

const userInitial   = computed(() => auth.user?.name?.[0]?.toUpperCase() ?? '?')
const pageTitle     = computed(() => route.meta?.title ?? '')
const totalUnread   = computed(() => msgStore.totalUnread)

// Employers whose profile exists but is not verified see a prompt to verify.
const showVerifyBanner = computed(() =>
  auth.isEmployer &&
  auth.user?.employer_profile &&
  auth.user.employer_profile.is_verified === false &&
  route.name !== 'employer.verification'
)

// Fetch unread count on layout mount for the nav badge, and keep it warm
let unreadTimer = null
onMounted(async () => {
  if (auth.isEmployer || auth.isJobSeeker) {
    await msgStore.fetchUnreadCount()
    // Lightweight poll just for the badge when not on the messages page
    unreadTimer = setInterval(msgStore.fetchUnreadCount, 30_000)
  }
})
onUnmounted(() => {
  if (unreadTimer) clearInterval(unreadTimer)
})

const employerNav = [
  { to: '/employer/dashboard',    label: 'Dashboard',       icon: HomeIcon },
  { to: '/employer/jobs',         label: 'My Jobs',         icon: BriefcaseIcon },
  { to: '/employer/applications', label: 'Applications',    icon: ClipboardDocumentListIcon },
  { to: '/employer/messages',     label: 'Messages',        icon: ChatBubbleLeftRightIcon, badge: true },
  { to: '/employer/billing',      label: 'Billing',         icon: CreditCardIcon },
  { to: '/employer/profile',      label: 'Company Profile', icon: UserIcon },
]

const seekerNav = [
  { to: '/job-seeker/dashboard',    label: 'Dashboard',    icon: HomeIcon },
  { to: '/jobs',                    label: 'Browse Jobs',  icon: BriefcaseIcon },
  { to: '/job-seeker/applications', label: 'Applications', icon: ClipboardDocumentListIcon },
  { to: '/job-seeker/saved',        label: 'Saved Jobs',   icon: BookmarkIcon },
  { to: '/job-seeker/messages',     label: 'Messages',     icon: ChatBubbleLeftRightIcon, badge: true },
  { to: '/job-seeker/interviews',   label: 'Interviews',   icon: CalendarDaysIcon },
  { to: '/job-seeker/profile',      label: 'My Profile',   icon: UserIcon },
]

const adminNav = [
  { to: '/admin/dashboard',  label: 'Dashboard',  icon: ChartBarIcon },
  { to: '/admin/users',      label: 'Users',      icon: UsersIcon },
  { to: '/admin/jobs',       label: 'Jobs',       icon: BriefcaseIcon },
  { to: '/admin/reports',    label: 'Reports',    icon: FlagIcon },
  { to: '/admin/analytics',  label: 'Analytics',  icon: PresentationChartLineIcon },
]

const navItems = computed(() => {
  if (auth.isAdmin)    return adminNav
  if (auth.isEmployer) return employerNav
  return seekerNav
})
</script>
