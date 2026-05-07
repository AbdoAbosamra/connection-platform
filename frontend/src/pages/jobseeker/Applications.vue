<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold">My Applications</h1>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="card p-4 h-20 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="applications.length === 0" class="card p-12 text-center text-gray-400">
      <p class="text-lg">No applications yet.</p>
      <RouterLink to="/jobs" class="btn-primary mt-4 inline-block">Browse Jobs</RouterLink>
    </div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="app in applications" :key="app.id" class="p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
          <img v-if="app.job?.employer?.logo" :src="`/storage/${app.job.employer.logo}`" class="w-full h-full object-contain" />
          <span v-else class="text-gray-400 font-bold text-sm">{{ app.job?.employer?.company_name?.[0] }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium truncate">{{ app.job?.title }}</p>
          <p class="text-sm text-gray-400">{{ app.job?.employer?.company_name }} · Applied {{ timeAgo(app.created_at) }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span :class="badgeClass(app.status)" class="capitalize text-xs">{{ app.status.replace('_', ' ') }}</span>
          <button
            v-if="!['hired','rejected','withdrawn'].includes(app.status)"
            @click="withdraw(app)"
            class="text-xs text-red-500 hover:underline"
          >Withdraw</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { applicationsApi } from '@/api/applications'

const loading      = ref(true)
const applications = ref([])

function badgeClass(s) {
  return {
    submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue',
    interview_scheduled: 'badge-blue', offer_extended: 'badge-green',
    hired: 'badge-green', rejected: 'badge-red', withdrawn: 'badge-gray',
  }[s] ?? 'badge-gray'
}

function timeAgo(d) {
  const diff = Math.floor((Date.now() - new Date(d)) / 86400000)
  return diff === 0 ? 'today' : diff === 1 ? 'yesterday' : `${diff} days ago`
}

async function withdraw(app) {
  if (!confirm('Withdraw this application?')) return
  await applicationsApi.withdraw(app.id)
  app.status = 'withdrawn'
}

onMounted(async () => {
  const { data } = await applicationsApi.myApplications()
  applications.value = data.data
  loading.value = false
})
</script>
