<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold">Saved Jobs</h1>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="card p-4 h-24 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="jobs.length === 0" class="card p-12 text-center text-gray-400">
      <p class="text-lg">No saved jobs yet.</p>
      <RouterLink to="/jobs" class="btn-primary mt-4 inline-block">Browse Jobs</RouterLink>
    </div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="job in jobs" :key="job.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
          <img v-if="job.employer?.logo" :src="`/storage/${job.employer.logo}`" class="w-full h-full object-contain" />
          <span v-else class="text-gray-400 font-bold text-sm">{{ job.employer?.company_name?.[0] }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <RouterLink :to="`/jobs/${job.slug}`" class="font-medium hover:text-primary-600">{{ job.title }}</RouterLink>
          <p class="text-sm text-gray-400">{{ job.employer?.company_name }} · {{ job.location_type }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span v-if="job.salary_visible && job.salary_min" class="text-xs text-gray-500">
            ${{ (job.salary_min / 1000).toFixed(0) }}k–${{ (job.salary_max / 1000).toFixed(0) }}k
          </span>
          <button @click="unsave(job)" class="text-xs text-red-500 hover:underline">Remove</button>
          <RouterLink :to="`/jobs/${job.slug}`" class="btn-primary text-xs">Apply</RouterLink>
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
  jobs.value    = data.jobs ?? data.data ?? []
  loading.value = false
})
</script>
