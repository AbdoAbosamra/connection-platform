<template>
  <div class="max-w-3xl">
    <h2 class="text-xl font-bold text-white mb-6">My Interviews</h2>

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="h-28 rounded-xl bg-white/[0.04] animate-pulse" />
    </div>

    <div v-else-if="interviews.length === 0" class="text-center py-20 text-gray-500">
      <CalendarDaysIcon class="w-10 h-10 mx-auto mb-3 opacity-40" />
      <p>No interviews scheduled yet.</p>
    </div>

    <ul v-else class="space-y-4">
      <li
        v-for="iv in interviews"
        :key="iv.id"
        class="rounded-2xl border border-gray-800 bg-gray-900/40 p-5"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="font-semibold text-white">{{ iv.application?.job?.title ?? 'Interview' }}</p>
            <p class="text-sm text-gray-400">
              {{ iv.application?.job?.employer?.company_name ?? '' }}
            </p>
            <p class="text-sm text-gray-300 mt-2">
              <CalendarDaysIcon class="w-4 h-4 inline -mt-0.5 mr-1 text-primary-400" />
              {{ formatDate(iv.scheduled_at) }}
              · {{ iv.duration_minutes }} min · {{ formatType(iv.format) }}
            </p>
            <a
              v-if="iv.meeting_link"
              :href="iv.meeting_link"
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm text-primary-400 hover:text-primary-300 inline-block mt-1"
            >
              Join link →
            </a>
            <p v-if="iv.location" class="text-sm text-gray-400 mt-1">📍 {{ iv.location }}</p>
            <p v-if="iv.notes" class="text-sm text-gray-500 mt-2 italic">"{{ iv.notes }}"</p>
          </div>
          <span
            class="px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize flex-shrink-0"
            :class="statusClass(iv.status)"
          >
            {{ iv.status }}
          </span>
        </div>

        <div v-if="iv.status === 'pending'" class="flex gap-2 mt-4">
          <button
            class="px-4 py-2 text-sm rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 disabled:opacity-50"
            :disabled="busyId === iv.id"
            @click="confirm(iv)"
          >
            Confirm attendance
          </button>
          <button
            class="px-4 py-2 text-sm rounded-xl border border-gray-700 text-gray-300 hover:bg-white/[0.05] disabled:opacity-50"
            :disabled="busyId === iv.id"
            @click="decline(iv)"
          >
            Decline
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { CalendarDaysIcon } from '@heroicons/vue/24/outline'
import { interviewsApi } from '@/api/interviews'

const interviews = ref([])
const loading = ref(true)
const busyId = ref(null)

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) : ''
}
function formatType(t) {
  return ({ video: 'Video call', phone: 'Phone', in_person: 'In person' })[t] ?? t
}
function statusClass(s) {
  return {
    pending: 'bg-amber-500/15 text-amber-300',
    confirmed: 'bg-emerald-500/15 text-emerald-300',
    cancelled: 'bg-rose-500/15 text-rose-300',
    completed: 'bg-gray-500/15 text-gray-300',
  }[s] ?? 'bg-gray-500/15 text-gray-300'
}

async function load() {
  loading.value = true
  try {
    const { data } = await interviewsApi.mine()
    interviews.value = data.data ?? []
  } finally {
    loading.value = false
  }
}

async function confirm(iv) {
  busyId.value = iv.id
  try {
    const { data } = await interviewsApi.confirm(iv.id)
    Object.assign(iv, data.interview)
  } finally {
    busyId.value = null
  }
}

async function decline(iv) {
  if (!window.confirm('Decline this interview?')) return
  busyId.value = iv.id
  try {
    const { data } = await interviewsApi.decline(iv.id)
    Object.assign(iv, data.interview)
  } finally {
    busyId.value = null
  }
}

onMounted(load)
</script>
