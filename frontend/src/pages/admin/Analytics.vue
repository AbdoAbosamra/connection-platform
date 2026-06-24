<template>
  <div class="space-y-6 animate-fade-in">

    <!-- Header -->
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Analytics</h1>
      <p class="text-gray-500 text-sm mt-0.5">Platform growth and engagement metrics</p>
    </div>

    <!-- Skeleton -->
    <template v-if="loading">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="card p-6 h-32 animate-pulse bg-gray-50" />
      </div>
      <div class="card p-6 h-64 animate-pulse bg-gray-50" />
    </template>

    <template v-else>
      <!-- Summary tiles -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-6 text-center">
          <p class="text-4xl font-extrabold text-primary-600">{{ totalGrowth.toLocaleString() }}</p>
          <p class="text-sm text-gray-500 font-medium mt-1">New Users (12 months)</p>
        </div>
        <div class="card p-6 text-center">
          <p class="text-4xl font-extrabold text-violet-600">{{ peakMonth?.total?.toLocaleString() ?? 0 }}</p>
          <p class="text-sm text-gray-500 font-medium mt-1">Peak Month: {{ peakMonth?.month ?? '—' }}</p>
        </div>
        <div class="card p-6 text-center">
          <p class="text-4xl font-extrabold text-emerald-600">{{ avgPerMonth.toLocaleString() }}</p>
          <p class="text-sm text-gray-500 font-medium mt-1">Avg Signups / Month</p>
        </div>
      </div>

      <!-- Monthly breakdown -->
      <div class="card p-6">
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-6">Monthly Signups — Last 12 Months</h2>

        <div v-if="growth.length === 0" class="text-center py-8 text-gray-400">
          <p>No growth data available yet.</p>
        </div>

        <!-- Chart + labels -->
        <div v-else class="flex items-end gap-2 h-48">
          <div
            v-for="point in growth"
            :key="point.month"
            class="flex-1 flex flex-col items-center gap-1 group"
          >
            <div class="relative w-full flex flex-col items-center">
              <span
                class="text-xs font-bold text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6 whitespace-nowrap"
              >
                {{ point.total }}
              </span>
              <div
                class="w-full rounded-t-md bg-gradient-to-t from-primary-600 to-violet-400 transition-all duration-700 hover:opacity-80 cursor-default"
                :style="{ height: barHeight(point.total) + 'px' }"
                :title="`${point.month}: ${point.total} signups`"
              />
            </div>
            <span class="text-[10px] text-gray-400 font-medium mt-1 rotate-0">{{ shortMonth(point.month) }}</span>
          </div>
        </div>
      </div>

      <!-- Monthly table -->
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Breakdown by Month</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Month</th>
              <th class="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">New Users</th>
              <th class="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide hidden md:table-cell">vs Avg</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="point in [...growth].reverse()" :key="point.month" class="hover:bg-gray-50/60 transition-colors">
              <td class="px-5 py-3 font-medium text-gray-700">{{ point.month }}</td>
              <td class="px-5 py-3 text-right">
                <span class="font-bold text-gray-900">{{ point.total.toLocaleString() }}</span>
              </td>
              <td class="px-5 py-3 text-right hidden md:table-cell">
                <span
                  :class="point.total >= avgPerMonth ? 'text-emerald-600' : 'text-red-500'"
                  class="text-xs font-semibold"
                >
                  {{ point.total >= avgPerMonth ? '+' : '' }}{{ (point.total - avgPerMonth).toFixed(0) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import client from '@/api/client'

const loading = ref(true)
const growth  = ref([])

const totalGrowth = computed(() => growth.value.reduce((s, g) => s + g.total, 0))
const peakMonth   = computed(() => growth.value.reduce((best, g) => (!best || g.total > best.total) ? g : best, null))
const avgPerMonth = computed(() => growth.value.length ? Math.round(totalGrowth.value / growth.value.length) : 0)
const maxVal      = computed(() => Math.max(...growth.value.map(g => g.total), 1))

function barHeight(val) {
  return Math.max(4, Math.round((val / maxVal.value) * 160))
}

function shortMonth(ym) {
  const [, month] = ym.split('-')
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
  return months[parseInt(month, 10) - 1] ?? ym
}

onMounted(async () => {
  const { data } = await client.get('/admin/dashboard/growth')
  growth.value   = data.growth ?? []
  loading.value  = false
})
</script>
