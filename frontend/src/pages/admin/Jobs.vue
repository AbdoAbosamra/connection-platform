<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold">Jobs</h1>

    <div class="flex gap-2">
      <select v-model="filters.status" class="input text-sm w-40" @change="load">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="draft">Draft</option>
        <option value="paused">Paused</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="card p-4 h-16 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="jobs.length === 0" class="card p-10 text-center text-gray-400">No jobs found.</div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="job in jobs" :key="job.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm">{{ job.title }}</p>
          <p class="text-xs text-gray-400">{{ job.employer?.company_name }} · {{ job.location_type }} · {{ job.applications_count ?? 0 }} applications</p>
        </div>
        <span v-if="job.is_featured" class="badge-yellow text-xs">Featured</span>
        <span :class="statusBadge(job.status)" class="capitalize text-xs">{{ job.status }}</span>
        <div class="flex gap-2">
          <button @click="toggleFeature(job)" class="text-xs text-gray-500 hover:underline">
            {{ job.is_featured ? 'Unfeature' : 'Feature' }}
          </button>
          <button @click="deleteJob(job)" class="text-xs text-red-500 hover:underline">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-2">
      <button
        v-for="p in meta.last_page" :key="p"
        @click="goToPage(p)"
        :class="['px-3 py-1 text-sm rounded border', p === meta.current_page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 hover:bg-gray-50']"
      >{{ p }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'

const loading = ref(true)
const jobs    = ref([])
const meta    = ref(null)
const filters = ref({ status: '', page: 1 })

function statusBadge(s) {
  return { active: 'badge-green', draft: 'badge-gray', paused: 'badge-yellow', closed: 'badge-red' }[s] ?? 'badge-gray'
}

async function load() {
  loading.value = true
  const { data } = await client.get('/admin/jobs', { params: filters.value })
  jobs.value    = data.data
  meta.value    = data.meta ?? { current_page: data.current_page, last_page: data.last_page }
  loading.value = false
}

function goToPage(p) {
  filters.value.page = p
  load()
}

async function toggleFeature(job) {
  await client.patch(`/admin/jobs/${job.id}/feature`)
  job.is_featured = !job.is_featured
}

async function deleteJob(job) {
  if (!confirm(`Delete "${job.title}"?`)) return
  await client.delete(`/admin/jobs/${job.id}`)
  jobs.value = jobs.value.filter(j => j.id !== job.id)
}

onMounted(load)
</script>
