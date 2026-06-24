<template>
  <div class="space-y-5 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">My Applications</h1>
      <p class="text-gray-500 text-sm mt-0.5">Track the status of all your job applications</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="card p-4 h-20 animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="applications.length === 0" class="card p-16 text-center">
      <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
        </svg>
      </div>
      <p class="font-bold text-gray-900 mb-1">No applications yet</p>
      <p class="text-sm text-gray-400 mb-5">Start applying to jobs and track your progress here</p>
      <RouterLink to="/jobs" class="btn-primary">Browse Jobs</RouterLink>
    </div>

    <!-- Applications list -->
    <div v-else class="card overflow-hidden">
      <div class="divide-y divide-gray-50">
        <div v-for="app in applications" :key="app.id" class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition-colors">
          <!-- Company logo -->
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 ring-1 ring-gray-200">
            <img v-if="app.job?.employer?.logo" :src="`/storage/${app.job.employer.logo}`" class="w-full h-full object-contain" />
            <span v-else class="text-gray-400 font-bold text-sm">{{ app.job?.employer?.company_name?.[0] }}</span>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ app.job?.title }}</p>
            <p class="text-sm text-gray-400 mt-0.5">{{ app.job?.employer?.company_name }} · Applied {{ timeAgo(app.created_at) }}</p>
          </div>

          <!-- Status & actions -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <span :class="badgeClass(app.status)" class="capitalize text-xs">
              {{ app.status.replace('_', ' ') }}
            </span>
            <button
              v-if="!['hired','rejected','withdrawn'].includes(app.status)"
              @click="withdraw(app)"
              class="text-xs text-rose-500 hover:text-rose-600 font-semibold bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition-colors"
            >
              Withdraw
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
  try {
    await applicationsApi.withdraw(app.id)
    app.status = 'withdrawn'
  } catch {
    alert('Failed to withdraw application. Please try again.')
  }
}

onMounted(async () => {
  try {
    const { data }     = await applicationsApi.myApplications()
    applications.value = data.data ?? []
  } finally {
    loading.value = false
  }
})
</script>
