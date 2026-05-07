<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold">Employer Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label" class="card p-5">
        <p class="text-sm text-gray-500">{{ stat.label }}</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stat.value }}</p>
        <p class="text-xs mt-1" :class="stat.change >= 0 ? 'text-green-600' : 'text-red-500'">
          {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }} this month
        </p>
      </div>
    </div>

    <!-- Recent applications -->
    <div class="card">
      <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold">Recent Applications</h2>
        <RouterLink to="/employer/applications" class="text-sm text-primary-600 hover:underline">View all</RouterLink>
      </div>
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <ul v-else class="divide-y divide-gray-100">
        <li v-for="app in recentApplications" :key="app.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
          <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-semibold text-sm flex-shrink-0">
            {{ app.job_seeker?.user?.name?.[0] }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate">{{ app.job_seeker?.user?.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ app.job?.title }}</p>
          </div>
          <span :class="statusBadge(app.status)">{{ app.status }}</span>
          <RouterLink :to="`/employer/applications/${app.id}`" class="text-xs text-primary-600 hover:underline">View</RouterLink>
        </li>
      </ul>
    </div>

    <!-- Active jobs -->
    <div class="card">
      <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold">Your Active Jobs</h2>
        <RouterLink to="/employer/jobs/new" class="btn-primary text-sm">+ Post Job</RouterLink>
      </div>
      <div v-if="loadingJobs" class="p-8 text-center text-gray-400">Loading…</div>
      <ul v-else class="divide-y divide-gray-100">
        <li v-for="job in activeJobs" :key="job.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
          <div class="flex-1 min-w-0">
            <p class="font-medium truncate">{{ job.title }}</p>
            <p class="text-xs text-gray-400">{{ job.applications_count }} applications</p>
          </div>
          <span :class="job.status === 'active' ? 'badge-green' : 'badge-gray'">{{ job.status }}</span>
          <RouterLink :to="`/employer/jobs/${job.id}/edit`" class="text-xs text-primary-600 hover:underline">Edit</RouterLink>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { applicationsApi } from '@/api/applications'
import { jobsApi } from '@/api/jobs'

const loading     = ref(true)
const loadingJobs = ref(true)
const recentApplications = ref([])
const activeJobs  = ref([])
const stats       = ref([
  { label: 'Active Jobs',      value: 0, change: 0 },
  { label: 'Total Applications', value: 0, change: 0 },
  { label: 'Shortlisted',      value: 0, change: 0 },
  { label: 'Hired This Month', value: 0, change: 0 },
])

function statusBadge(s) {
  return { submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue', hired: 'badge-green', rejected: 'badge-red' }[s] ?? 'badge-gray'
}

onMounted(async () => {
  const [appsRes, jobsRes] = await Promise.all([
    applicationsApi.list({ per_page: 5 }),
    jobsApi.myJobs({ status: 'active', per_page: 5 }),
  ])
  recentApplications.value = appsRes.data.data
  activeJobs.value = jobsRes.data.data
  stats.value[0].value = jobsRes.data.total
  stats.value[1].value = appsRes.data.total
  loading.value     = false
  loadingJobs.value = false
})
</script>
