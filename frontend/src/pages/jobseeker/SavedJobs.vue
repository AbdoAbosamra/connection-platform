<template>
  <div class="space-y-5 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Saved Jobs</h1>
      <p class="text-gray-500 text-sm mt-0.5">Jobs you've bookmarked for later</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="card p-4 h-24 animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="jobs.length === 0" class="card p-16 text-center">
      <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
        </svg>
      </div>
      <p class="font-bold text-gray-900 mb-1">No saved jobs yet</p>
      <p class="text-sm text-gray-400 mb-5">Bookmark jobs to review and apply later</p>
      <RouterLink to="/jobs" class="btn-primary">Browse Jobs</RouterLink>
    </div>

    <!-- List -->
    <div v-else class="card overflow-hidden">
      <div class="divide-y divide-gray-50">
        <div v-for="job in jobs" :key="job.id" class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition-colors">
          <!-- Logo -->
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 ring-1 ring-gray-200">
            <img v-if="job.employer?.logo" :src="`/storage/${job.employer.logo}`" class="w-full h-full object-contain" />
            <span v-else class="text-gray-400 font-bold text-sm">{{ job.employer?.company_name?.[0] }}</span>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <RouterLink :to="`/jobs/${job.slug}`" class="font-semibold text-gray-900 hover:text-primary-700 transition-colors">
              {{ job.title }}
            </RouterLink>
            <p class="text-sm text-gray-400 mt-0.5">{{ job.employer?.company_name }} · {{ job.location_type }}</p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <span v-if="job.salary_visible && job.salary_min" class="text-xs font-semibold text-gray-700 bg-gray-100 px-2.5 py-1 rounded-lg">
              ${{ Math.round(job.salary_min / 1000) }}k–${{ Math.round(job.salary_max / 1000) }}k
            </span>
            <button @click="unsave(job)" class="text-xs text-rose-500 hover:text-rose-600 font-semibold bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition-colors">
              Remove
            </button>
            <RouterLink :to="`/jobs/${job.slug}`" class="btn-primary text-xs !px-4 !py-2">
              Apply
            </RouterLink>
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

async function unsave(job) {
  await jobsApi.unsave(job.id)
  jobs.value = jobs.value.filter(j => j.id !== job.id)
}

onMounted(async () => {
  const { data } = await jobsApi.saved()
  jobs.value     = data.jobs ?? data.data ?? []
  loading.value  = false
})
</script>
