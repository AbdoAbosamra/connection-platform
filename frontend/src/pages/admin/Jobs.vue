<template>
  <div class="space-y-5 animate-fade-in">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Jobs</h1>
        <p class="text-gray-500 text-sm mt-0.5">Manage and moderate job listings</p>
      </div>
      <div v-if="meta" class="text-sm text-gray-400 font-medium">
        {{ meta.total?.toLocaleString() ?? '—' }} total jobs
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="filters.search"
          @input="debouncedLoad"
          class="input text-sm !pl-9 w-60"
          placeholder="Search job titles…"
        />
      </div>
      <select v-model="filters.status" class="input text-sm w-40" @change="load">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="draft">Draft</option>
        <option value="paused">Paused</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="card p-4 h-[72px] animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="jobs.length === 0" class="card p-12 text-center">
      <BriefcaseIcon class="w-10 h-10 text-gray-300 mx-auto mb-3" />
      <p class="text-gray-500 font-medium">No jobs found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Job</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide hidden md:table-cell">Company</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Apps</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="job in jobs" :key="job.id" class="hover:bg-gray-50/60 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                  <BriefcaseIcon class="w-4 h-4 text-blue-500" />
                </div>
                <div class="min-w-0">
                  <p class="font-semibold text-gray-900 truncate">{{ job.title }}</p>
                  <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ job.location_type?.replace('_', ' ') }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden md:table-cell">
              <p class="text-sm text-gray-700 truncate max-w-[140px]">{{ job.employer?.company_name ?? '—' }}</p>
            </td>
            <td class="px-4 py-3.5 hidden lg:table-cell">
              <span class="text-sm font-semibold text-gray-700">{{ job.applications_count ?? 0 }}</span>
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span :class="statusBadge(job.status)" class="capitalize text-xs">{{ job.status }}</span>
                <span v-if="job.is_featured" class="badge-yellow text-xs">Featured</span>
                <span v-if="job.deleted_at" class="badge-red text-xs">Deleted</span>
              </div>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1">
                <button
                  @click="toggleFeature(job)"
                  :disabled="featuring === job.id"
                  class="px-2.5 py-1.5 text-xs font-medium rounded-lg transition-colors"
                  :class="job.is_featured
                    ? 'text-amber-600 hover:bg-amber-50'
                    : 'text-gray-600 hover:bg-gray-100'"
                >
                  {{ featuring === job.id ? '…' : job.is_featured ? 'Unfeature' : 'Feature' }}
                </button>
                <button
                  v-if="!job.deleted_at"
                  @click="deleteJob(job)"
                  :disabled="deleting === job.id"
                  class="px-2.5 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                >
                  {{ deleting === job.id ? '…' : 'Delete' }}
                </button>
                <button
                  v-else
                  @click="restoreJob(job)"
                  :disabled="restoring === job.id"
                  class="px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                >
                  {{ restoring === job.id ? '…' : 'Restore' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-1.5">
      <button
        @click="goToPage(meta.current_page - 1)"
        :disabled="meta.current_page === 1"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 transition-colors"
      >‹</button>
      <button
        v-for="p in meta.last_page" :key="p"
        @click="goToPage(p)"
        :class="['px-3 py-1.5 text-sm rounded-lg border transition-colors',
          p === meta.current_page ? 'bg-primary-600 text-white border-primary-600 font-semibold' : 'border-gray-200 hover:bg-gray-50']"
      >{{ p }}</button>
      <button
        @click="goToPage(meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 transition-colors"
      >›</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'
import { BriefcaseIcon } from '@heroicons/vue/24/outline'

const loading   = ref(true)
const jobs      = ref([])
const meta      = ref(null)
const filters   = ref({ search: '', status: '', page: 1 })
const featuring = ref(null)
const deleting  = ref(null)
const restoring = ref(null)
let debounceTimer = null

function statusBadge(s) {
  return { active: 'badge-green', draft: 'badge-gray', paused: 'badge-yellow', closed: 'badge-red' }[s] ?? 'badge-gray'
}

async function load() {
  loading.value = true
  try {
    const { data } = await client.get('/admin/jobs', { params: filters.value })
    jobs.value = data.data
    meta.value = data.meta ?? { current_page: data.current_page, last_page: data.last_page, total: data.total }
  } finally {
    loading.value = false
  }
}

function debouncedLoad() {
  filters.value.page = 1
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 300)
}

function goToPage(p) {
  filters.value.page = p
  load()
}

async function toggleFeature(job) {
  featuring.value = job.id
  try {
    await client.patch(`/admin/jobs/${job.id}/feature`)
    job.is_featured = !job.is_featured
  } finally {
    featuring.value = null
  }
}

async function deleteJob(job) {
  if (!confirm(`Delete "${job.title}"? This will soft-delete the listing.`)) return
  deleting.value = job.id
  try {
    await client.delete(`/admin/jobs/${job.id}`)
    job.deleted_at = new Date().toISOString()
  } finally {
    deleting.value = null
  }
}

async function restoreJob(job) {
  restoring.value = job.id
  try {
    await client.post(`/admin/jobs/${job.id}/restore`)
    job.deleted_at = null
  } finally {
    restoring.value = null
  }
}

onMounted(load)
</script>
