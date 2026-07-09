<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Interviews</h1>
      <p class="text-gray-500 text-sm mt-0.5">Interviews scheduled across all your jobs</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card p-8 text-center text-gray-400 text-sm">
      <svg class="animate-spin w-5 h-5 mx-auto mb-2 text-primary-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      Loading…
    </div>

    <!-- Empty -->
    <div v-else-if="!interviews.length" class="card p-16 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <CalendarDaysIcon class="w-8 h-8 text-gray-300" />
      </div>
      <p class="font-bold text-gray-900 mb-1">No interviews scheduled</p>
      <p class="text-sm text-gray-400">Schedule interviews from a candidate's application.</p>
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div v-for="iv in interviews" :key="iv.id" class="card p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold flex-shrink-0">
          {{ iv.application?.job_seeker?.user?.name?.[0] ?? '?' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 truncate">{{ iv.application?.job_seeker?.user?.name ?? 'Candidate' }}</p>
          <p class="text-xs text-gray-400 truncate">{{ iv.application?.job?.title }}</p>
          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-gray-500">
            <span class="flex items-center gap-1">🗓️ {{ formatDate(iv.scheduled_at) }}</span>
            <span v-if="iv.duration_minutes" class="flex items-center gap-1">⏱️ {{ iv.duration_minutes }} min</span>
            <span v-if="iv.format" class="capitalize flex items-center gap-1">💻 {{ iv.format }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <span :class="statusBadge(iv.status)" class="capitalize text-xs">{{ iv.status }}</span>
          <a v-if="iv.meeting_link" :href="iv.meeting_link" target="_blank" rel="noopener"
             class="text-xs text-primary-600 hover:text-primary-700 font-semibold bg-primary-50 hover:bg-primary-100 px-2.5 py-1.5 rounded-lg transition-colors">
            Join
          </a>
          <RouterLink :to="`/employer/applications/${iv.application?.id}`"
            class="text-xs text-gray-600 hover:text-gray-800 font-semibold bg-gray-100 hover:bg-gray-200 px-2.5 py-1.5 rounded-lg transition-colors">
            View
          </RouterLink>
          <button v-if="canCancel(iv)" @click="cancel(iv)" :disabled="busyId === iv.id"
            class="text-xs text-rose-600 hover:text-rose-700 font-semibold bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg transition-colors disabled:opacity-50">
            Cancel
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 pt-2">
        <button
          v-for="p in pagination.last_page" :key="p"
          @click="load(p)"
          :class="['min-w-[36px] h-9 rounded-xl text-sm font-semibold border transition-all',
                   p === pagination.current_page ? 'bg-primary-600 text-white border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']"
        >{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { interviewsApi } from '@/api/interviews'
import { CalendarDaysIcon } from '@heroicons/vue/24/outline'

const loading    = ref(true)
const interviews = ref([])
const pagination = ref(null)
const busyId     = ref(null)

function statusBadge(s) {
  return {
    pending: 'badge-yellow', confirmed: 'badge-green',
    cancelled: 'badge-red', completed: 'badge-blue',
  }[s] ?? 'badge-gray'
}

function canCancel(iv) {
  return ['pending', 'confirmed'].includes(iv.status)
}

function formatDate(dt) {
  if (!dt) return ''
  return new Date(dt).toLocaleString(undefined, {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await interviewsApi.employerList(page)
    interviews.value = data.data ?? []
    pagination.value = data
  } finally {
    loading.value = false
  }
}

async function cancel(iv) {
  if (!confirm('Cancel this interview?')) return
  busyId.value = iv.id
  try {
    await interviewsApi.cancelByEmployer(iv.id)
    iv.status = 'cancelled'
  } finally {
    busyId.value = null
  }
}

onMounted(() => load())
</script>
