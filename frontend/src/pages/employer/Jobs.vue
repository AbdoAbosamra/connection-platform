<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">My Jobs</h1>
      <RouterLink to="/employer/jobs/new" class="btn-primary">+ Post Job</RouterLink>
    </div>

    <div class="flex gap-2">
      <select v-model="filters.status" class="input text-sm w-40" @change="load">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="draft">Draft</option>
        <option value="paused">Paused</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="card p-4 h-20 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="jobs.length === 0" class="card p-12 text-center text-gray-400">
      <p class="text-lg">No jobs yet.</p>
      <RouterLink to="/employer/jobs/new" class="btn-primary mt-4 inline-block">Post your first job</RouterLink>
    </div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="job in jobs" :key="job.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
        <div class="flex-1 min-w-0">
          <p class="font-medium">{{ job.title }}</p>
          <p class="text-xs text-gray-400 mt-0.5">
            {{ job.location_type }} · {{ job.employment_type?.replace('_', '-') }} ·
            {{ job.applications_count ?? 0 }} applications
          </p>
        </div>
        <span :class="statusBadge(job.status)" class="capitalize text-xs">{{ job.status }}</span>
        <div class="flex items-center gap-2 flex-shrink-0">
          <RouterLink :to="`/employer/jobs/${job.id}/edit`" class="text-xs text-primary-600 hover:underline">Edit</RouterLink>
          <button @click="toggleStatus(job)" class="text-xs text-gray-500 hover:underline">
            {{ job.status === 'active' ? 'Pause' : 'Activate' }}
          </button>
          <button @click="deleteJob(job)" class="text-xs text-red-500 hover:underline">Delete</button>
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
  jobs.value    = data.data
  loading.value = false
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
