<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Welcome back, {{ auth.user?.name?.split(' ')[0] }} 👋</h1>
      <RouterLink to="/jobs" class="btn-primary">Browse Jobs</RouterLink>
    </div>

    <!-- Profile completion -->
    <div v-if="profile && profile.completion < 100" class="card p-5 bg-primary-50 border-primary-200">
      <div class="flex items-center justify-between mb-2">
        <p class="font-medium text-primary-800">Complete your profile</p>
        <span class="text-primary-700 font-bold">{{ profile.completion }}%</span>
      </div>
      <div class="w-full bg-primary-200 rounded-full h-2">
        <div class="bg-primary-600 h-2 rounded-full transition-all" :style="`width:${profile.completion}%`"></div>
      </div>
      <p class="text-sm text-primary-600 mt-2">A complete profile gets 5× more views from employers.</p>
      <RouterLink to="/job-seeker/profile" class="btn-primary text-sm mt-3 inline-block">Update Profile</RouterLink>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label" class="card p-4 text-center">
        <p class="text-2xl font-bold text-primary-600">{{ stat.value }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Recommended jobs -->
    <div class="card">
      <div class="p-5 border-b flex items-center justify-between">
        <h2 class="font-semibold">Recommended for You</h2>
        <RouterLink to="/jobs" class="text-sm text-primary-600 hover:underline">Browse all →</RouterLink>
      </div>
      <div v-if="loadingJobs" class="p-8 text-center text-gray-400">Finding matches…</div>
      <div v-else-if="recommended.length === 0" class="p-8 text-center text-gray-400">
        Complete your profile to get job recommendations.
      </div>
      <div v-else class="divide-y divide-gray-100">
        <JobCard v-for="job in recommended" :key="job.id" :job="job" />
      </div>
    </div>

    <!-- Recent applications -->
    <div class="card">
      <div class="p-5 border-b flex items-center justify-between">
        <h2 class="font-semibold">Recent Applications</h2>
        <RouterLink to="/job-seeker/applications" class="text-sm text-primary-600 hover:underline">View all →</RouterLink>
      </div>
      <div v-if="loadingApps" class="p-8 text-center text-gray-400">Loading…</div>
      <ul v-else-if="recentApps.length" class="divide-y divide-gray-100">
        <li v-for="app in recentApps" :key="app.id" class="p-4 flex items-center gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate">{{ app.job?.title }}</p>
            <p class="text-xs text-gray-400">{{ app.job?.employer?.company_name }}</p>
          </div>
          <span :class="badgeClass(app.status)" class="text-xs capitalize">{{ app.status.replace('_', ' ') }}</span>
        </li>
      </ul>
      <p v-else class="p-8 text-center text-gray-400 text-sm">No applications yet. Start applying!</p>
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

const auth         = useAuthStore()
const profile      = ref(null)
const recommended  = ref([])
const recentApps   = ref([])
const loadingJobs  = ref(true)
const loadingApps  = ref(true)

const stats = ref([
  { label: 'Applications',    value: 0 },
  { label: 'Saved Jobs',      value: 0 },
  { label: 'Profile Views',   value: 0 },
  { label: 'Interviews',      value: 0 },
])

function badgeClass(s) {
  return { submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue', hired: 'badge-green', rejected: 'badge-red' }[s] ?? 'badge-gray'
}

onMounted(async () => {
  const [profileRes, recRes, appsRes] = await Promise.all([
    client.get('/job-seeker/profile'),
    jobsApi.recommended().catch(() => ({ data: { jobs: [] } })),
    applicationsApi.myApplications({ per_page: 5 }),
  ])
  profile.value     = profileRes.data.profile
  recommended.value = recRes.data.jobs ?? []
  recentApps.value  = appsRes.data.data
  stats.value[0].value = appsRes.data.total
  loadingJobs.value = false
  loadingApps.value = false
})
</script>
