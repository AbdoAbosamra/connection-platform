<template>
  <div class="space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Admin Dashboard</h1>
      <p class="text-gray-500 text-sm mt-0.5">Platform overview and recent activity</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="card p-5 h-24 animate-pulse bg-gray-50" />
    </div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="(card, i) in statCards" :key="card.label" class="card p-5 flex items-center gap-4">
          <div :class="card.bg" class="stat-icon">
            <component :is="card.icon" :class="card.text" class="w-5 h-5" />
          </div>
          <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ card.value.toLocaleString() }}</p>
            <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
            <p v-if="card.sub" class="text-xs text-emerald-600 font-medium mt-0.5">{{ card.sub }}</p>
          </div>
        </div>
      </div>

      <!-- Applications by status -->
      <div class="card p-6">
        <h2 class="section-title mb-4">Applications by Status</h2>
        <div class="flex flex-wrap gap-3">
          <div
            v-for="(count, status) in stats.applications.by_status"
            :key="status"
            class="flex items-center gap-2.5 bg-gray-50 hover:bg-gray-100 rounded-xl px-4 py-2.5 text-sm transition-colors cursor-default"
          >
            <span class="font-bold text-gray-900 text-base">{{ count.toLocaleString() }}</span>
            <span class="text-gray-400 capitalize font-medium">{{ status.replace('_', ' ') }}</span>
          </div>
        </div>
      </div>

      <!-- Recent signups -->
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <h2 class="section-title">Recent Signups</h2>
        </div>
        <ul class="divide-y divide-gray-50">
          <li v-for="u in stats.recent_signups" :key="u.id" class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50/60 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center font-bold text-primary-700 text-sm flex-shrink-0">
              {{ u.name[0] }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
            <span
              :class="u.role === 'employer' ? 'badge-blue' : 'badge-green'"
              class="capitalize text-xs"
            >
              {{ u.role.replace('_', ' ') }}
            </span>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import client from '@/api/client'
import { UsersIcon, BriefcaseIcon, UserGroupIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

const loading = ref(true)
const stats   = ref({})

const statCards = computed(() => {
  if (!stats.value?.users) return []
  return [
    {
      label: 'Total Users', value: stats.value.users.total,
      sub: `+${stats.value.users.this_month} this month`,
      bg: 'bg-primary-50', text: 'text-primary-600', icon: UsersIcon,
    },
    {
      label: 'Employers', value: stats.value.users.employers,
      bg: 'bg-violet-50', text: 'text-violet-600', icon: UserGroupIcon,
    },
    {
      label: 'Job Seekers', value: stats.value.users.seekers,
      bg: 'bg-amber-50', text: 'text-amber-600', icon: UsersIcon,
    },
    {
      label: 'Active Jobs', value: stats.value.jobs.active,
      bg: 'bg-emerald-50', text: 'text-emerald-600', icon: BriefcaseIcon,
    },
  ]
})

onMounted(async () => {
  const { data } = await client.get('/admin/dashboard')
  stats.value    = data.stats
  loading.value  = false
})
</script>
