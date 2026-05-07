<template>
  <div class="space-y-5 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">My Jobs</h1>
        <p class="text-gray-500 text-sm mt-0.5">Manage your job postings</p>
      </div>
      <RouterLink to="/employer/jobs/new" class="btn-primary gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Post Job
      </RouterLink>
    </div>

    <!-- Filter bar -->
    <div class="card p-4 flex items-center gap-3">
      <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
      </svg>
      <select v-model="filters.status" class="input !py-2 bg-gray-50 cursor-pointer w-44" @change="load">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="draft">Draft</option>
        <option value="paused">Paused</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="card p-4 h-20 animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="jobs.length === 0" class="card p-16 text-center">
      <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
      </div>
      <p class="font-bold text-gray-900 mb-1">No jobs yet</p>
      <p class="text-sm text-gray-400 mb-5">Post your first job to start receiving applications</p>
      <RouterLink to="/employer/jobs/new" class="btn-primary">Post your first job</RouterLink>
    </div>

    <!-- Jobs list -->
    <div v-else class="card overflow-hidden">
      <div class="divide-y divide-gray-50">
        <div v-for="job in jobs" :key="job.id" class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition-colors">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900">{{ job.title }}</p>
            <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
              <span class="capitalize">{{ job.location_type?.replace('_', '-') }}</span>
              <span class="text-gray-200">·</span>
              <span>{{ job.employment_type?.replace('_', '-') }}</span>
              <span class="text-gray-200">·</span>
              <span class="font-medium text-gray-600">{{ job.applications_count ?? 0 }} applications</span>
            </p>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <span :class="statusBadge(job.status)" class="capitalize text-xs">{{ job.status }}</span>
            <RouterLink
              :to="`/employer/jobs/${job.id}/edit`"
              class="text-xs text-primary-600 hover:text-primary-700 font-semibold bg-primary-50 hover:bg-primary-100 px-2.5 py-1 rounded-lg transition-colors"
            >
              Edit
            </RouterLink>
            <button
              @click="toggleStatus(job)"
              class="text-xs text-gray-500 hover:text-gray-700 font-semibold bg-gray-50 hover:bg-gray-100 px-2.5 py-1 rounded-lg transition-colors"
            >
              {{ job.status === 'active' ? 'Pause' : 'Activate' }}
            </button>
            <button
              @click="deleteJob(job)"
              class="text-xs text-rose-500 hover:text-rose-600 font-semibold bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { jobsApi } from '@/api/jobs'

const loading = ref(true)
const jobs    = ref([])
const filters = ref({ status: '' })

function statusBadge(s) {
  return { active: 'badge-green', draft: 'badge-gray', paused: 'badge-yellow', closed: 'badge-red' }[s] ?? 'badge-gray'
}

async function load() {
  loading.value = true
  const { data } = await jobsApi.myJobs(filters.value)
  jobs.value     = data.data
  loading.value  = false
}

async function toggleStatus(job) {
  await jobsApi.toggleStatus(job.id)
  await load()
}

async function deleteJob(job) {
  if (!confirm(`Delete "${job.title}"? This cannot be undone.`)) return
  await jobsApi.deleteJob(job.id)
  jobs.value = jobs.value.filter(j => j.id !== job.id)
}

onMounted(load)
</script>
