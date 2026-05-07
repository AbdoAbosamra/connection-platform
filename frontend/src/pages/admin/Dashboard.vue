<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>

    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="card p-5 h-24 animate-pulse bg-gray-100" />
    </div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
          <p class="text-sm text-gray-500">Total Users</p>
          <p class="text-3xl font-bold">{{ stats.users.total.toLocaleString() }}</p>
          <p class="text-xs text-green-600 mt-1">+{{ stats.users.this_month }} this month</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-gray-500">Employers</p>
          <p class="text-3xl font-bold">{{ stats.users.employers.toLocaleString() }}</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-gray-500">Job Seekers</p>
          <p class="text-3xl font-bold">{{ stats.users.seekers.toLocaleString() }}</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-gray-500">Active Jobs</p>
          <p class="text-3xl font-bold">{{ stats.jobs.active.toLocaleString() }}</p>
        </div>
      </div>

      <!-- Application breakdown -->
      <div class="card p-6">
        <h2 class="font-semibold mb-4">Applications by Status</h2>
        <div class="flex flex-wrap gap-3">
          <div v-for="(count, status) in stats.applications.by_status" :key="status"
            class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 text-sm">
            <span class="font-semibold text-gray-800">{{ count }}</span>
            <span class="text-gray-400 capitalize">{{ status.replace('_', ' ') }}</span>
          </div>
        </div>
      </div>

      <!-- Recent signups -->
      <div class="card">
        <div class="p-5 border-b font-semibold">Recent Signups</div>
        <ul class="divide-y divide-gray-100">
          <li v-for="u in stats.recent_signups" :key="u.id" class="p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500 text-sm">
              {{ u.name[0] }}
            </div>
            <div class="flex-1">
              <p class="font-medium text-sm">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
            <span :class="u.role === 'employer' ? 'badge-blue' : 'badge-green'" class="capitalize text-xs">
              {{ u.role.replace('_', ' ') }}
            </span>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'

const loading = ref(true)
const stats   = ref({})

onMounted(async () => {
  const { data } = await client.get('/admin/dashboard')
  stats.value   = data.stats
  loading.value = false
})
</script>
