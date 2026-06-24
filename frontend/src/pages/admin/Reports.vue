<template>
  <div class="space-y-5 animate-fade-in">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Reports</h1>
        <p class="text-gray-500 text-sm mt-0.5">Review and resolve user-submitted reports</p>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="pendingCount > 0" class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">
          {{ pendingCount }} pending
        </span>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-2">
      <select v-model="filters.status" class="input text-sm w-40" @change="load">
        <option value="">All reports</option>
        <option value="open">Open</option>
        <option value="resolved">Resolved</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="card p-5 h-24 animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="reports.length === 0" class="card p-12 text-center">
      <FlagIcon class="w-10 h-10 text-gray-300 mx-auto mb-3" />
      <p class="text-gray-500 font-medium">No reports found</p>
      <p class="text-gray-400 text-sm mt-1">{{ filters.status === 'open' ? 'All clear — no open reports!' : 'No reports match your filter.' }}</p>
    </div>

    <!-- Reports list -->
    <div v-else class="space-y-3">
      <div
        v-for="report in reports"
        :key="report.id"
        class="card p-5 hover:shadow-md transition-shadow"
        :class="report.status === 'open' ? 'border-l-4 border-l-amber-400' : 'border-l-4 border-l-emerald-400'"
      >
        <div class="flex items-start gap-4">
          <!-- Icon -->
          <div :class="report.status === 'open' ? 'bg-amber-50' : 'bg-emerald-50'"
            class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <FlagIcon :class="report.status === 'open' ? 'text-amber-500' : 'text-emerald-500'" class="w-5 h-5" />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-semibold text-sm text-gray-900 capitalize">{{ report.type?.replace('_', ' ') ?? 'Report' }}</p>
              <span :class="report.status === 'resolved' ? 'badge-green' : 'badge-yellow'" class="text-xs capitalize">
                {{ report.status }}
              </span>
              <span
                v-if="report.priority && report.priority !== 'normal'"
                class="text-xs font-bold px-2 py-0.5 rounded-full capitalize"
                :class="priorityClass(report.priority)"
              >
                {{ report.priority }}
              </span>
            </div>
            <p class="text-sm text-gray-600 font-medium">{{ reasonLabel(report.reason) }}</p>
            <p v-if="report.details" class="text-sm text-gray-400 mt-1 line-clamp-2">{{ report.details }}</p>
            <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-gray-400">
              <span v-if="report.reporter">
                Reported by <strong class="text-gray-600">{{ report.reporter.name }}</strong>
              </span>
              <span>{{ timeAgo(report.created_at) }}</span>
              <span v-if="report.resolved_at">Resolved {{ timeAgo(report.resolved_at) }}</span>
            </div>
          </div>

          <!-- Moderation actions -->
          <div v-if="report.status === 'open'" class="flex flex-wrap gap-1.5 flex-shrink-0 justify-end max-w-[14rem]">
            <button
              v-for="a in actions"
              :key="a.value"
              :disabled="acting === report.id"
              :title="a.label"
              class="text-xs px-2.5 py-1 rounded-lg border transition-colors disabled:opacity-50"
              :class="a.class"
              @click="act(report, a.value)"
            >
              {{ a.label }}
            </button>
          </div>
          <div v-else class="flex-shrink-0">
            <span class="badge-green text-xs flex items-center gap-1">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Resolved
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import client from '@/api/client'
import { FlagIcon } from '@heroicons/vue/24/outline'

const loading  = ref(true)
const reports  = ref([])
const filters  = ref({ status: '' })

const pendingCount = computed(() => reports.value.filter(r => r.status === 'open').length)

// Community-Guidelines-aligned reason labels (values match StoreReportRequest::REASONS).
const REASON_LABELS = {
  harassment: 'Harassment or intimidation',
  hate_speech: 'Hate speech or discrimination',
  threats: 'Threats or abusive behavior',
  fake_profile: 'Fake profile / impersonation',
  resume_fraud: 'Resume or credential fraud',
  fake_listing: 'Fake job listing',
  misleading_compensation: 'Misleading compensation',
  application_fee: 'Requests application/interview fees',
  job_selling: 'Selling access to jobs',
  pyramid_scheme: 'Pyramid scheme or MLM',
  phishing: 'Phishing / data harvesting',
  money_laundering: 'Money laundering',
  spam: 'Spam',
  scam: 'Scam / fraud',
  duplicate: 'Duplicate posting',
  inappropriate: 'Inappropriate content',
  other: 'Other',
}
function reasonLabel(r) {
  return REASON_LABELS[r] ?? (r ?? '').replace(/_/g, ' ')
}

function timeAgo(d) {
  if (!d) return ''
  const diff = Math.floor((Date.now() - new Date(d)) / 86400000)
  if (diff === 0) return 'today'
  if (diff === 1) return 'yesterday'
  return `${diff} days ago`
}

async function load() {
  loading.value = true
  const { data } = await client.get('/admin/reports', { params: filters.value })
  reports.value = data.data
  loading.value = false
}

// Moderation actions wired to POST /admin/reports/{id}/action.
const acting = ref(null)
const actions = [
  { value: 'dismissal', label: 'Dismiss', class: 'border-gray-300 text-gray-500 hover:bg-gray-100' },
  { value: 'warning', label: 'Warn', class: 'border-amber-300 text-amber-600 hover:bg-amber-50' },
  { value: 'content_removed', label: 'Remove', class: 'border-orange-300 text-orange-600 hover:bg-orange-50' },
  { value: 'suspension', label: 'Suspend', class: 'border-rose-300 text-rose-600 hover:bg-rose-50' },
]

const ACTION_VERB = {
  dismissal: 'dismiss this flag',
  warning: 'warn the reported user',
  content_removed: 'remove the reported job posting',
  suspension: 'suspend the reported user',
}

async function act(report, action) {
  if (!window.confirm(`Are you sure you want to ${ACTION_VERB[action] ?? action}?`)) return
  acting.value = report.id
  try {
    const notes = window.prompt('Optional note for the audit log:') || undefined
    const { data } = await client.post(`/admin/reports/${report.id}/action`, { action, notes })
    Object.assign(report, data.report)
  } finally {
    acting.value = null
  }
}

function priorityClass(p) {
  return {
    critical: 'bg-rose-600 text-white',
    high: 'bg-rose-100 text-rose-700',
    low: 'bg-gray-100 text-gray-500',
  }[p] ?? 'bg-gray-100 text-gray-500'
}

onMounted(load)
</script>
