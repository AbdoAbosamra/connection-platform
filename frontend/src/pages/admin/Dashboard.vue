<template>
  <div class="space-y-6 animate-fade-in">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-500 text-sm mt-0.5">Platform overview — RemoteArena</p>
      </div>
      <button @click="load" :disabled="loading" class="btn-secondary text-sm flex items-center gap-2">
        <svg :class="loading ? 'animate-spin' : ''" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
      </button>
    </div>

    <!-- Skeleton -->
    <template v-if="loading">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="card p-5 h-28 animate-pulse bg-gray-50" />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card p-6 h-48 animate-pulse bg-gray-50" />
        <div class="card p-6 h-48 animate-pulse bg-gray-50" />
      </div>
    </template>

    <template v-else>
      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="card in statCards" :key="card.label"
          class="card p-5 flex items-start gap-4 hover:shadow-md transition-shadow">
          <div :class="card.bg" class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
            <component :is="card.icon" :class="card.text" class="w-5 h-5" />
          </div>
          <div class="min-w-0">
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ card.value.toLocaleString() }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">{{ card.label }}</p>
            <p v-if="card.sub" class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              {{ card.sub }}
            </p>
          </div>
        </div>
      </div>

      <!-- Middle Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Applications by Status -->
        <div class="card p-6">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Application Pipeline</h2>
          <div class="space-y-3">
            <div v-for="(count, status) in stats.applications?.by_status" :key="status">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-600 capitalize">{{ status.replace('_', ' ') }}</span>
                <span class="text-xs font-bold text-gray-900">{{ count.toLocaleString() }}</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2">
                <div
                  :style="{ width: pct(count, stats.applications?.total) + '%' }"
                  :class="statusBarColor(status)"
                  class="h-2 rounded-full transition-all duration-500"
                />
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-400 mt-4">Total: {{ stats.applications?.total?.toLocaleString() ?? 0 }} applications</p>
        </div>

        <!-- Jobs Overview -->
        <div class="card p-6">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Jobs Overview</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                  <BriefcaseIcon class="w-4 h-4 text-emerald-600" />
                </div>
                <span class="text-sm font-semibold text-emerald-800">Active Jobs</span>
              </div>
              <span class="text-xl font-extrabold text-emerald-700">{{ stats.jobs?.active ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                  <BriefcaseIcon class="w-4 h-4 text-gray-500" />
                </div>
                <span class="text-sm font-semibold text-gray-700">Closed / Expired</span>
              </div>
              <span class="text-xl font-extrabold text-gray-700">{{ stats.jobs?.closed ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                  <BriefcaseIcon class="w-4 h-4 text-blue-600" />
                </div>
                <span class="text-sm font-semibold text-blue-800">Total Jobs</span>
              </div>
              <span class="text-xl font-extrabold text-blue-700">{{ stats.jobs?.total ?? 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- User Growth Chart -->
      <div v-if="growth.length" class="card p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">User Growth (Last 12 Months)</h2>
          <span class="text-xs text-gray-400">{{ growth[growth.length - 1]?.month }}</span>
        </div>
        <div class="flex items-end gap-1.5 h-32">
          <div
            v-for="point in growth"
            :key="point.month"
            class="flex-1 flex flex-col items-center gap-1 group"
          >
            <span class="text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity font-semibold">
              {{ point.total }}
            </span>
            <div
              class="w-full bg-gradient-to-t from-primary-600 to-violet-500 rounded-t-sm transition-all duration-500 hover:opacity-80 cursor-default"
              :style="{ height: barHeight(point.total) + 'px' }"
              :title="`${point.month}: ${point.total} users`"
            />
            <span class="text-[9px] text-gray-400 leading-none">{{ point.month.slice(5) }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Signups -->
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Recent Signups</h2>
          <RouterLink to="/admin/users" class="text-xs text-primary-600 font-semibold hover:text-primary-700">
            View all →
          </RouterLink>
        </div>
        <ul class="divide-y divide-gray-50">
          <li v-for="u in stats.recent_signups" :key="u.id"
            class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50/60 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center font-bold text-primary-700 text-sm flex-shrink-0">
              {{ u.name[0]?.toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
            <span :class="roleBadge(u.role)" class="capitalize text-xs">
              {{ u.role.replace('_', ' ') }}
            </span>
            <RouterLink :to="`/admin/users/${u.id}`" class="text-xs text-gray-400 hover:text-primary-600 transition-colors">
              View →
            </RouterLink>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import client from '@/api/client'
import { UsersIcon, BriefcaseIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

const loading = ref(true)
const stats   = ref({})
const growth  = ref([])

const statCards = computed(() => {
  if (!stats.value?.users) return []
  return [
    {
      label: 'Total Users',
      value: stats.value.users.total,
      sub: `+${stats.value.users.this_month} this month`,
      bg: 'bg-primary-50', text: 'text-primary-600', icon: UsersIcon,
    },
    {
      label: 'Employers',
      value: stats.value.users.employers,
      bg: 'bg-violet-50', text: 'text-violet-600', icon: UserGroupIcon,
    },
    {
      label: 'Job Seekers',
      value: stats.value.users.seekers,
      bg: 'bg-amber-50', text: 'text-amber-600', icon: UsersIcon,
    },
    {
      label: 'Active Jobs',
      value: stats.value.jobs?.active ?? 0,
      bg: 'bg-emerald-50', text: 'text-emerald-600', icon: BriefcaseIcon,
    },
  ]
})

function pct(val, total) {
  if (!total) return 0
  return Math.round((val / total) * 100)
}

function statusBarColor(status) {
  const map = {
    submitted:          'bg-yellow-400',
    viewed:             'bg-blue-300',
    shortlisted:        'bg-violet-500',
    interview_scheduled:'bg-blue-500',
    offer_extended:     'bg-amber-400',
    hired:              'bg-emerald-500',
    rejected:           'bg-red-400',
    withdrawn:          'bg-gray-400',
  }
  return map[status] ?? 'bg-gray-300'
}

function roleBadge(role) {
  return role === 'employer' ? 'badge-blue' : role === 'admin' ? 'badge-red' : 'badge-green'
}

const maxGrowth = computed(() => Math.max(...growth.value.map(g => g.total), 1))
function barHeight(val) {
  return Math.max(4, Math.round((val / maxGrowth.value) * 100))
}

async function load() {
  loading.value = true
  const [dashRes, growthRes] = await Promise.all([
    client.get('/admin/dashboard'),
    client.get('/admin/dashboard/growth'),
  ])
  stats.value  = dashRes.data.stats
  growth.value = growthRes.data.growth ?? []
  loading.value = false
}

onMounted(load)
</script>
