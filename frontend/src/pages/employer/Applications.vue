<template>
  <div class="space-y-5 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Applications</h1>
        <p class="text-gray-500 text-sm mt-0.5">Review and manage candidate applications</p>
      </div>
      <select v-model="filters.status" class="input !py-2 bg-white w-44 cursor-pointer shadow-sm" @change="load">
        <option value="">All statuses</option>
        <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
      </select>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="card p-4 h-20 animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="applications.length === 0" class="card p-16 text-center">
      <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
        </svg>
      </div>
      <p class="font-bold text-gray-900 mb-1">No applications yet</p>
      <p class="text-sm text-gray-400">Applications will appear here once candidates apply to your jobs</p>
    </div>

    <!-- Applications list -->
    <div v-else class="card overflow-hidden">
      <div class="divide-y divide-gray-50">
        <div
          v-for="app in applications"
          :key="app.id"
          class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition-colors cursor-pointer"
          @click="router.push(`/employer/applications/${app.id}`)"
        >
          <!-- Avatar -->
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0 shadow-sm">
            {{ app.job_seeker?.user?.name?.[0] }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-900">{{ app.job_seeker?.user?.name }}</p>
            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ app.job?.title }} · {{ app.job_seeker?.current_country }}</p>
          </div>

          <!-- Skills & meta -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <div class="hidden sm:flex gap-1.5">
              <span
                v-for="skill in app.job_seeker?.skills?.slice(0, 3)"
                :key="skill.id"
                class="badge-gray text-xs"
              >
                {{ skill.name }}
              </span>
            </div>
            <span :class="badgeClass(app.status)" class="capitalize text-xs">{{ app.status.replace('_', ' ') }}</span>
            <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-lg font-medium whitespace-nowrap">
              {{ timeAgo(app.created_at) }}
            </span>
            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { applicationsApi } from '@/api/applications'

const router      = useRouter()
const loading     = ref(true)
const applications = ref([])
const filters     = ref({ status: '' })
const statuses    = ['submitted', 'viewed', 'shortlisted', 'interview_scheduled', 'offer_extended', 'hired', 'rejected']

function badgeClass(s) {
  return {
    submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue',
    hired: 'badge-green', rejected: 'badge-red',
  }[s] ?? 'badge-gray'
}

function timeAgo(d) {
  const diff = Math.floor((Date.now() - new Date(d)) / 86400000)
  return diff === 0 ? 'Today' : diff === 1 ? 'Yesterday' : `${diff}d ago`
}

async function load() {
  loading.value = true
  const { data }  = await applicationsApi.list(filters.value)
  applications.value = data.data
  loading.value   = false
}

onMounted(load)
</script>
