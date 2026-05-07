<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold">Reports</h1>

    <div class="flex gap-2">
      <select v-model="filters.status" class="input text-sm w-40" @change="load">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="resolved">Resolved</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 6" :key="i" class="card p-4 h-16 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="reports.length === 0" class="card p-10 text-center text-gray-400">No reports found.</div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="report in reports" :key="report.id" class="p-4 space-y-1 hover:bg-gray-50">
        <div class="flex items-start gap-3">
          <div class="flex-1">
            <p class="font-medium text-sm capitalize">{{ report.type?.replace('_', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ report.reason }}</p>
            <p v-if="report.details" class="text-xs text-gray-400 mt-1">{{ report.details }}</p>
            <p class="text-xs text-gray-400 mt-1">
              Reported by {{ report.reporter?.name }} · {{ timeAgo(report.created_at) }}
            </p>
          </div>
          <span :class="report.status === 'resolved' ? 'badge-green' : 'badge-yellow'" class="text-xs capitalize flex-shrink-0">
            {{ report.status }}
          </span>
          <button
            v-if="report.status !== 'resolved'"
            @click="resolve(report)"
            class="btn-secondary text-xs flex-shrink-0"
            :disabled="resolving === report.id"
          >
            {{ resolving === report.id ? 'Resolving…' : 'Resolve' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'

const loading  = ref(true)
const reports  = ref([])
const filters  = ref({ status: '' })
const resolving = ref(null)

function timeAgo(d) {
  const diff = Math.floor((Date.now() - new Date(d)) / 86400000)
  return diff === 0 ? 'today' : diff === 1 ? 'yesterday' : `${diff} days ago`
}

async function load() {
  loading.value = true
  const { data } = await client.get('/admin/reports', { params: filters.value })
  reports.value = data.data
  loading.value = false
}

async function resolve(report) {
  resolving.value = report.id
  await client.patch(`/admin/reports/${report.id}/resolve`)
  report.status  = 'resolved'
  resolving.value = null
}

onMounted(load)
</script>
