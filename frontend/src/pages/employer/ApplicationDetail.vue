<template>
  <div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
      <RouterLink to="/employer/applications" class="text-sm text-primary-600 hover:underline">← Applications</RouterLink>
    </div>

    <div v-if="loading" class="card p-8 text-center text-gray-400">Loading…</div>

    <template v-else-if="application">
      <!-- Applicant header -->
      <div class="card p-6 flex items-start gap-5">
        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-xl flex-shrink-0">
          {{ application.job_seeker?.user?.name?.[0] }}
        </div>
        <div class="flex-1">
          <h1 class="text-xl font-bold">{{ application.job_seeker?.user?.name }}</h1>
          <p class="text-gray-500 text-sm">{{ application.job_seeker?.headline }}</p>
          <p class="text-gray-400 text-xs mt-1">
            {{ application.job_seeker?.current_city }}, {{ application.job_seeker?.current_country }} ·
            {{ application.job_seeker?.experience_level }} level ·
            {{ application.job_seeker?.years_of_experience }}y exp
          </p>
          <div class="flex flex-wrap gap-1 mt-2">
            <span v-for="skill in application.job_seeker?.skills" :key="skill.id" class="badge-gray text-xs">
              {{ skill.name }}
            </span>
          </div>
        </div>
        <div class="flex flex-col items-end gap-2">
          <span :class="badgeClass(application.status)" class="capitalize">{{ application.status.replace('_', ' ') }}</span>
          <p class="text-xs text-gray-400">Applied {{ timeAgo(application.created_at) }}</p>
          <!-- Message applicant button -->
          <button
            @click="messageApplicant"
            :disabled="initiating"
            class="btn-secondary text-xs flex items-center gap-1.5 mt-1"
          >
            <svg v-if="initiating" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
            </svg>
            {{ initiating ? 'Opening…' : 'Message Applicant' }}
          </button>
          <p v-if="initiateError" class="text-xs text-rose-500 text-right">{{ initiateError }}</p>
        </div>
      </div>

      <!-- Job info -->
      <div class="card p-5">
        <p class="text-sm text-gray-500">Applied for</p>
        <p class="font-semibold">{{ application.job?.title }}</p>
      </div>

      <!-- Cover letter -->
      <div v-if="application.cover_letter" class="card p-6">
        <h2 class="font-semibold mb-3">Cover Letter</h2>
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ application.cover_letter }}</p>
      </div>

      <!-- Resume -->
      <div v-if="application.resume" class="card p-5 flex items-center justify-between">
        <span class="font-medium">Resume</span>
        <a :href="`/storage/${application.resume}`" target="_blank" class="btn-secondary text-sm">View / Download</a>
      </div>

      <!-- Links -->
      <div v-if="application.job_seeker?.linkedin_url || application.job_seeker?.github_url || application.job_seeker?.portfolio_url" class="card p-5 space-y-1">
        <h2 class="font-semibold mb-2">Links</h2>
        <a v-if="application.job_seeker.linkedin_url" :href="application.job_seeker.linkedin_url" target="_blank" class="block text-sm text-primary-600 hover:underline">LinkedIn</a>
        <a v-if="application.job_seeker.github_url" :href="application.job_seeker.github_url" target="_blank" class="block text-sm text-primary-600 hover:underline">GitHub</a>
        <a v-if="application.job_seeker.portfolio_url" :href="application.job_seeker.portfolio_url" target="_blank" class="block text-sm text-primary-600 hover:underline">Portfolio</a>
      </div>

      <!-- Status update -->
      <div class="card p-6 space-y-4">
        <h2 class="font-semibold">Update Status</h2>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="s in statuses" :key="s"
            @click="setStatus(s)"
            :class="[application.status === s ? 'btn-primary' : 'btn-secondary', 'text-sm capitalize']"
            :disabled="updating"
          >
            {{ s.replace('_', ' ') }}
          </button>
        </div>
        <div>
          <label class="label">Notes (internal)</label>
          <textarea v-model="notes" rows="3" class="input" placeholder="Add notes visible only to you…"></textarea>
        </div>
        <div v-if="updateSuccess" class="text-green-600 text-sm">✓ Status updated.</div>
      </div>

      <!-- Interview scheduling -->
      <div class="card p-6 space-y-4">
        <h2 class="font-semibold">Interview</h2>

        <div v-if="application.latest_interview || application.latestInterview" class="rounded-xl border border-gray-200 p-4 text-sm">
          <p class="font-medium text-gray-800">
            {{ formatInterviewDate(currentInterview.scheduled_at) }}
            · {{ currentInterview.duration_minutes }} min · {{ formatType(currentInterview.format) }}
          </p>
          <p class="text-gray-500 mt-1 capitalize">Status: {{ currentInterview.status }}</p>
          <a v-if="currentInterview.meeting_link" :href="currentInterview.meeting_link" target="_blank" class="text-primary-600 hover:underline">Meeting link</a>
        </div>

        <form class="space-y-3" @submit.prevent="scheduleInterview">
          <p class="text-sm text-gray-500">{{ currentInterview ? 'Reschedule interview' : 'Schedule an interview' }}</p>
          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="label">Date &amp; time</label>
              <input v-model="iv.scheduled_at" type="datetime-local" class="input" required>
            </div>
            <div>
              <label class="label">Format</label>
              <select v-model="iv.format" class="input">
                <option value="video">Video call</option>
                <option value="phone">Phone</option>
                <option value="in_person">In person</option>
              </select>
            </div>
          </div>
          <div v-if="iv.format === 'video'">
            <label class="label">Meeting link</label>
            <input v-model="iv.meeting_link" type="url" class="input" placeholder="https://meet.example.com/…">
          </div>
          <div v-if="iv.format === 'in_person'">
            <label class="label">Location</label>
            <input v-model="iv.location" type="text" class="input" placeholder="Office address">
          </div>
          <div>
            <label class="label">Notes (optional)</label>
            <textarea v-model="iv.notes" rows="2" class="input" placeholder="Anything the candidate should know…" />
          </div>
          <p v-if="ivError" class="text-sm text-rose-500">{{ ivError }}</p>
          <p v-if="ivSuccess" class="text-sm text-green-600">✓ Interview {{ currentInterview ? 'updated' : 'scheduled' }}. The candidate has been notified.</p>
          <button type="submit" class="btn-primary text-sm" :disabled="ivSaving">
            {{ ivSaving ? 'Saving…' : (currentInterview ? 'Reschedule' : 'Schedule interview') }}
          </button>
        </form>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { applicationsApi } from '@/api/applications'
import { messagesApi } from '@/api/messages'
import { interviewsApi } from '@/api/interviews'
import { useMessagesStore } from '@/stores/messages'

const route         = useRoute()
const router        = useRouter()
const msgStore      = useMessagesStore()
const loading       = ref(true)
const updating      = ref(false)
const updateSuccess = ref(false)
const application   = ref(null)
const notes         = ref('')
const initiating    = ref(false)
const initiateError = ref(null)

async function messageApplicant() {
  if (!application.value) return
  initiating.value    = true
  initiateError.value = null
  try {
    const { data } = await messagesApi.initiate({
      job_seeker_profile_id: application.value.job_seeker_profile_id,
      job_id:                application.value.job_id,
    })
    msgStore.openToConversation(data.conversation)
    router.push('/employer/messages')
  } catch (err) {
    const d = err.response?.data
    if (d?.errors) {
      initiateError.value = Object.values(d.errors)[0]?.[0] ?? d.message
    } else if (d?.message) {
      initiateError.value = d.message
    } else if (err.response?.status) {
      initiateError.value = `Server error (${err.response.status}). Check Laravel logs.`
    } else {
      initiateError.value = 'Network error — is the backend running?'
    }
    console.error('[messageApplicant]', err.response?.status, d ?? err.message)
  } finally {
    initiating.value = false
  }
}

// ── Interview scheduling ────────────────────────────────────────────────────
const iv = ref({ scheduled_at: '', format: 'video', meeting_link: '', location: '', notes: '' })
const ivSaving = ref(false)
const ivError = ref('')
const ivSuccess = ref(false)

const currentInterview = computed(
  () => application.value?.latest_interview ?? application.value?.latestInterview ?? null
)

function formatInterviewDate(iso) {
  return iso ? new Date(iso).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) : ''
}
function formatType(t) {
  return ({ video: 'Video call', phone: 'Phone', in_person: 'In person' })[t] ?? t
}

async function scheduleInterview() {
  ivSaving.value = true
  ivError.value = ''
  ivSuccess.value = false
  try {
    const payload = {
      scheduled_at: iv.value.scheduled_at,
      format: iv.value.format,
      meeting_link: iv.value.format === 'video' ? iv.value.meeting_link : undefined,
      location: iv.value.format === 'in_person' ? iv.value.location : undefined,
      notes: iv.value.notes || undefined,
    }
    const { data } = currentInterview.value
      ? await interviewsApi.reschedule(currentInterview.value.id, payload)
      : await interviewsApi.schedule(route.params.id, payload)
    application.value.latestInterview = data.interview
    application.value.status = 'interview_scheduled'
    ivSuccess.value = true
  } catch (err) {
    ivError.value =
      Object.values(err.response?.data?.errors ?? {})[0]?.[0] ??
      err.response?.data?.message ??
      'Could not schedule the interview.'
  } finally {
    ivSaving.value = false
  }
}

const statuses = ['viewed', 'shortlisted', 'interview_scheduled', 'offer_extended', 'hired', 'rejected']

function badgeClass(s) {
  return { submitted: 'badge-gray', viewed: 'badge-yellow', shortlisted: 'badge-blue', interview_scheduled: 'badge-blue', offer_extended: 'badge-green', hired: 'badge-green', rejected: 'badge-red' }[s] ?? 'badge-gray'
}

function timeAgo(d) {
  const diff = Math.floor((Date.now() - new Date(d)) / 86400000)
  return diff === 0 ? 'today' : diff === 1 ? 'yesterday' : `${diff} days ago`
}

async function setStatus(status) {
  updating.value      = true
  updateSuccess.value = false
  try {
    await applicationsApi.updateStatus(route.params.id, { status, notes: notes.value })
    application.value.status = status
    updateSuccess.value = true
  } finally {
    updating.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await applicationsApi.show(route.params.id)
    application.value = data.application
  } finally {
    loading.value = false
  }
})
</script>
