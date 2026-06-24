<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Employer Dashboard</h1>
        <p class="text-gray-500 text-sm mt-0.5">Manage your hiring pipeline</p>
      </div>
      <RouterLink to="/employer/jobs/new" class="btn-primary gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Post a Job
      </RouterLink>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="(stat, i) in stats" :key="stat.label" class="card p-5 flex items-center gap-4">
        <div :class="statColors[i].bg" class="stat-icon">
          <component :is="statColors[i].icon" :class="statColors[i].text" class="w-5 h-5" />
        </div>
        <div>
          <p class="text-2xl font-extrabold text-gray-900">{{ stat.value }}</p>
          <p class="text-xs text-gray-500 font-medium">{{ stat.label }}</p>
          <p v-if="stat.change !== undefined" class="text-xs mt-0.5" :class="stat.change >= 0 ? 'text-emerald-600' : 'text-rose-500'">
            {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }} this month
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent applications -->
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="section-title">Recent Applications</h2>
          <RouterLink to="/employer/applications" class="text-sm text-primary-600 font-semibold hover:text-primary-700 flex items-center gap-1">
            View all
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </RouterLink>
        </div>

        <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">
          <svg class="animate-spin w-5 h-5 mx-auto mb-2 text-primary-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          Loading…
        </div>

        <ul v-else class="divide-y divide-gray-50">
          <li
            v-for="app in recentApplications"
            :key="app.id"
            class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50/60 transition-colors"
          >
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0">
              {{ app.job_seeker?.user?.name?.[0] }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900 truncate">{{ app.job_seeker?.user?.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ app.job?.title }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <span :class="statusBadge(app.status)" class="capitalize text-xs">{{ app.status }}</span>
              <RouterLink
                :to="`/employer/applications/${app.id}`"
                class="text-xs text-primary-600 hover:text-primary-700 font-semibold bg-primary-50 hover:bg-primary-100 px-2 py-1 rounded-lg transition-colors"
              >
                View
              </RouterLink>
            </div>
          </li>
          <li v-if="!recentApplications.length" class="px-5 py-10 text-center text-sm text-gray-400">
            No applications yet
          </li>
        </ul>
      </div>

      <!-- Active jobs -->
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="section-title">Active Jobs</h2>
          <RouterLink to="/employer/jobs" class="text-sm text-primary-600 font-semibold hover:text-primary-700 flex items-center gap-1">
            Manage
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </RouterLink>
        </div>

        <div v-if="loadingJobs" class="p-8 text-center text-gray-400 text-sm">Loading…</div>

        <ul v-else class="divide-y divide-gray-50">
          <li
            v-for="job in activeJobs"
            :key="job.id"
            class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50/60 transition-colors"
          >
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900 truncate">{{ job.title }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ job.applications_count ?? 0 }} applications</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <span :class="job.status === 'active' ? 'badge-green' : 'badge-gray'" class="capitalize text-xs">{{ job.status }}</span>
              <RouterLink
                :to="`/employer/jobs/${job.id}/edit`"
                class="text-xs text-primary-600 hover:text-primary-700 font-semibold bg-primary-50 hover:bg-primary-100 px-2 py-1 rounded-lg transition-colors"
              >
                Edit
              </RouterLink>
            </div>
          </li>
          <li v-if="!activeJobs.length" class="px-5 py-10 text-center">
            <p class="text-sm text-gray-400 mb-3">No active jobs yet</p>
            <RouterLink to="/employer/jobs/new" class="btn-primary text-sm">Post your first job</RouterLink>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { applicationsApi } from '@/api/applications'
import { jobsApi } from '@/api/jobs'
import client from '@/api/client'
import {
  BriefcaseIcon, ClipboardDocumentListIcon, UserGroupIcon, CheckBadgeIcon,
} from '@heroicons/vue/24/outline'

const loading     = ref(true)
const loadingJobs = ref(true)
const recentApplications = ref([])
const activeJobs  = ref([])

const stats = ref([
  { label: 'Active Jobs',         value: 0, change: 0 },
  { label: 'Total Applications',  value: 0, change: 0 },
  { label: 'Shortlisted',         value: 0, change: 0 },
  { label: 'Hired This Month',    value: 0, change: 0 },
])

const statColors = [
  { bg: 'bg-primary-50', text: 'text-primary-600', icon: BriefcaseIcon },
  { bg: 'bg-amber-50',   text: 'text-amber-600',   icon: ClipboardDocumentListIcon },
  { bg: 'bg-violet-50',  text: 'text-violet-600',  icon: UserGroupIcon },
  { bg: 'bg-emerald-50', text: 'text-emerald-600', icon: CheckBadgeIcon },
]

function statusBadge(s) {
  return {
    submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue',
    hired: 'badge-green', rejected: 'badge-red',
  }[s] ?? 'badge-gray'
}

onMounted(async () => {
  const [appsRes, jobsRes, statsRes] = await Promise.all([
    applicationsApi.list({ per_page: 5 }).catch(() => ({ data: { data: [], total: 0 } })),
    jobsApi.myJobs({ status: 'active', per_page: 5 }).catch(() => ({ data: { data: [], total: 0 } })),
    client.get('/employer/stats').catch(() => ({ data: {} })),
  ])
  recentApplications.value = appsRes.data.data ?? []
  activeJobs.value         = jobsRes.data.data ?? []
  const s                  = statsRes.data
  stats.value[0].value     = s.active_jobs        ?? jobsRes.data.total ?? 0
  stats.value[1].value     = s.total_applications ?? appsRes.data.total ?? 0
  stats.value[2].value     = s.shortlisted        ?? 0
  stats.value[3].value     = s.hired_this_month   ?? 0
  loading.value            = false
  loadingJobs.value        = false
})
</script>
