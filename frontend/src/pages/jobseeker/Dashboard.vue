<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Welcome header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">
          Welcome back, {{ auth.user?.name?.split(' ')[0] }} 👋
        </h1>
        <p class="text-gray-500 text-sm mt-0.5">Here's what's happening with your job search</p>
      </div>
      <RouterLink to="/jobs" class="btn-primary gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
        </svg>
        Browse Jobs
      </RouterLink>
    </div>

    <!-- Profile completion banner -->
    <div
      v-if="profile && profile.completion < 100"
      class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-violet-600 p-5 text-white shadow-lg shadow-primary-500/30"
    >
      <div class="absolute top-0 right-0 w-40 h-40 bg-white/[0.06] rounded-full -translate-y-20 translate-x-20" />
      <div class="relative flex items-center justify-between gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between mb-2">
            <p class="font-bold">Complete your profile</p>
            <span class="font-extrabold text-lg">{{ profile.completion }}%</span>
          </div>
          <div class="w-full bg-white/20 rounded-full h-2">
            <div
              class="bg-white h-2 rounded-full transition-all duration-700"
              :style="`width:${profile.completion}%`"
            />
          </div>
          <p class="text-white/70 text-xs mt-2">A complete profile gets 5× more views from employers.</p>
        </div>
        <RouterLink to="/job-seeker/profile" class="btn bg-white text-primary-700 hover:bg-primary-50 flex-shrink-0 text-sm !px-5">
          Update
        </RouterLink>
      </div>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="(stat, i) in stats"
        :key="stat.label"
        class="card p-5 flex items-center gap-4"
      >
        <div :class="statColors[i].bg" class="stat-icon">
          <component :is="statColors[i].icon" :class="statColors[i].text" class="w-5 h-5" />
        </div>
        <div>
          <p class="text-2xl font-extrabold text-gray-900">{{ stat.value }}</p>
          <p class="text-xs text-gray-500 font-medium">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Recommended jobs -->
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="section-title">Recommended for You</h2>
        <RouterLink to="/jobs" class="text-sm text-primary-600 font-semibold hover:text-primary-700 flex items-center gap-1">
          Browse all
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </RouterLink>
      </div>

      <div v-if="loadingJobs" class="p-8 text-center text-gray-400 text-sm">
        <svg class="animate-spin w-5 h-5 mx-auto mb-2 text-primary-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        Finding your matches…
      </div>

      <div v-else-if="recommended.length === 0" class="p-10 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-900">No recommendations yet</p>
        <p class="text-xs text-gray-400 mt-1">Complete your profile to get personalized job recommendations</p>
      </div>

      <div v-else class="p-4 space-y-3">
        <JobCard v-for="job in recommended" :key="job.id" :job="job" />
      </div>
    </div>

    <!-- Recent applications -->
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="section-title">Recent Applications</h2>
        <RouterLink to="/job-seeker/applications" class="text-sm text-primary-600 font-semibold hover:text-primary-700 flex items-center gap-1">
          View all
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </RouterLink>
      </div>

      <div v-if="loadingApps" class="p-6 text-center text-gray-400 text-sm">Loading…</div>

      <ul v-else-if="recentApps.length" class="divide-y divide-gray-50">
        <li v-for="app in recentApps" :key="app.id" class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50/60 transition-colors">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-900 truncate">{{ app.job?.title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ app.job?.employer?.company_name }}</p>
          </div>
          <span :class="badgeClass(app.status)" class="text-xs capitalize flex-shrink-0">
            {{ app.status.replace('_', ' ') }}
          </span>
        </li>
      </ul>

      <p v-else class="px-5 py-10 text-center text-sm text-gray-400">
        No applications yet.
        <RouterLink to="/jobs" class="text-primary-600 font-semibold">Start applying →</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { jobsApi } from '@/api/jobs'
import { applicationsApi } from '@/api/applications'
import client from '@/api/client'
import JobCard from '@/components/jobs/JobCard.vue'
import {
  ClipboardDocumentListIcon, BookmarkIcon, EyeIcon, CalendarDaysIcon,
} from '@heroicons/vue/24/outline'

const auth        = useAuthStore()
const profile     = ref(null)
const recommended = ref([])
const recentApps  = ref([])
const loadingJobs = ref(true)
const loadingApps = ref(true)

const stats = ref([
  { label: 'Applications', value: 0 },
  { label: 'Saved Jobs',   value: 0 },
  { label: 'Profile Views', value: 0 },
  { label: 'Interviews',   value: 0 },
])

const statColors = [
  { bg: 'bg-primary-50',  text: 'text-primary-600',  icon: ClipboardDocumentListIcon },
  { bg: 'bg-amber-50',    text: 'text-amber-600',     icon: BookmarkIcon },
  { bg: 'bg-emerald-50',  text: 'text-emerald-600',   icon: EyeIcon },
  { bg: 'bg-violet-50',   text: 'text-violet-600',    icon: CalendarDaysIcon },
]

function badgeClass(s) {
  return {
    submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue',
    hired: 'badge-green', rejected: 'badge-red',
  }[s] ?? 'badge-gray'
}

onMounted(async () => {
  const [profileRes, recRes, appsRes] = await Promise.all([
    client.get('/job-seeker/profile'),
    jobsApi.recommended().catch(() => ({ data: { jobs: [] } })),
    applicationsApi.myApplications({ per_page: 5 }),
  ])
  profile.value         = profileRes.data.profile
  recommended.value     = recRes.data.jobs ?? []
  recentApps.value      = appsRes.data.data
  stats.value[0].value  = appsRes.data.total
  loadingJobs.value     = false
  loadingApps.value     = false
})
</script>
