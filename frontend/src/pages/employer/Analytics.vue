<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Analytics</h1>
      <p class="text-gray-500 text-sm mt-0.5">An overview of your hiring performance</p>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="(s, i) in statCards" :key="s.label" class="card p-5 flex items-center gap-4">
        <div :class="s.bg" class="stat-icon">
          <component :is="s.icon" :class="s.text" class="w-5 h-5" />
        </div>
        <div>
          <p class="text-2xl font-extrabold text-gray-900">{{ (s.value ?? 0).toLocaleString() }}</p>
          <p class="text-xs text-gray-500 font-medium">{{ s.label }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Applications by status -->
      <div class="card p-6">
        <h2 class="section-title mb-4">Applications by stage</h2>
        <div v-if="loading" class="text-sm text-gray-400 py-8 text-center">Loading…</div>
        <div v-else-if="!statusRows.length" class="text-sm text-gray-400 py-8 text-center">No applications yet</div>
        <ul v-else class="space-y-3">
          <li v-for="row in statusRows" :key="row.key">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="capitalize text-gray-600">{{ row.label }}</span>
              <span class="font-semibold text-gray-900">{{ row.value }}</span>
            </div>
            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-violet-500" :style="{ width: row.pct + '%' }" />
            </div>
          </li>
        </ul>
      </div>

      <!-- Jobs by hiring mode -->
      <div class="card p-6">
        <h2 class="section-title mb-4">Jobs by hiring mode</h2>
        <div v-if="loading" class="text-sm text-gray-400 py-8 text-center">Loading…</div>
        <ul v-else class="space-y-3">
          <li v-for="mode in hiringModeRows" :key="mode.key" class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
            <span class="text-sm font-medium text-gray-700 flex items-center gap-2">{{ mode.emoji }} {{ mode.label }}</span>
            <span class="text-lg font-extrabold text-gray-900">{{ mode.value }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { employerApi } from '@/api/employer'
import {
  BriefcaseIcon, CheckBadgeIcon, ClipboardDocumentListIcon, EyeIcon,
} from '@heroicons/vue/24/outline'

const loading = ref(true)
const stats   = ref({})

const statCards = computed(() => [
  { label: 'Total Jobs',         value: stats.value.total_jobs,         bg: 'bg-primary-50', text: 'text-primary-600', icon: BriefcaseIcon },
  { label: 'Total Applications', value: stats.value.total_applications, bg: 'bg-amber-50',   text: 'text-amber-600',   icon: ClipboardDocumentListIcon },
  { label: 'Profile Views',      value: stats.value.total_views,        bg: 'bg-violet-50',  text: 'text-violet-600',  icon: EyeIcon },
  { label: 'Hired This Month',   value: stats.value.hired_this_month,   bg: 'bg-emerald-50', text: 'text-emerald-600', icon: CheckBadgeIcon },
])

const STATUS_LABELS = {
  submitted: 'Submitted', viewed: 'Viewed', shortlisted: 'Shortlisted',
  interview_scheduled: 'Interview scheduled', offer_extended: 'Offer extended',
  hired: 'Hired', rejected: 'Rejected', withdrawn: 'Withdrawn',
}

const statusRows = computed(() => {
  const by = stats.value.applications_by_status ?? {}
  const max = Math.max(1, ...Object.values(by))
  return Object.entries(by)
    .sort((a, b) => b[1] - a[1])
    .map(([key, value]) => ({ key, label: STATUS_LABELS[key] ?? key, value, pct: Math.round((value / max) * 100) }))
})

const HIRING_MODES = [
  { key: 'local',                emoji: '📍', label: 'Local' },
  { key: 'national_remote',      emoji: '🏠', label: 'Remote (same country)' },
  { key: 'international_remote', emoji: '🌍', label: 'International remote' },
]

const hiringModeRows = computed(() => {
  const by = stats.value.jobs_by_hiring_mode ?? {}
  return HIRING_MODES.map(m => ({ ...m, value: by[m.key] ?? 0 }))
})

onMounted(async () => {
  try {
    const { data } = await employerApi.stats()
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>
