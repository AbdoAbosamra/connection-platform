<template>
  <div class="space-y-5 animate-fade-in">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">Users</h1>
        <p class="text-gray-500 text-sm mt-0.5">Manage all platform accounts</p>
      </div>
      <div v-if="meta" class="text-sm text-gray-400 font-medium">
        {{ meta.total?.toLocaleString() ?? '—' }} total users
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="filters.search"
          @input="debouncedLoad"
          class="input text-sm !pl-9 w-60"
          placeholder="Search by name or email…"
        />
      </div>
      <select v-model="filters.role" class="input text-sm w-36" @change="load">
        <option value="">All roles</option>
        <option value="employer">Employer</option>
        <option value="job_seeker">Job Seeker</option>
        <option value="admin">Admin</option>
      </select>
      <select v-model="filters.status" class="input text-sm w-36" @change="load">
        <option value="">Any status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="card p-4 h-[72px] animate-pulse bg-gray-50" />
    </div>

    <!-- Empty -->
    <div v-else-if="users.length === 0" class="card p-12 text-center">
      <UsersIcon class="w-10 h-10 text-gray-300 mx-auto mb-3" />
      <p class="text-gray-500 font-medium">No users found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">User</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide hidden md:table-cell">Role</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Joined</th>
            <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/60 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center font-bold text-primary-700 text-sm flex-shrink-0">
                  {{ user.name[0]?.toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <p class="font-semibold text-gray-900 truncate">{{ user.name }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden md:table-cell">
              <span :class="roleBadge(user.role)" class="text-xs capitalize">
                {{ user.role.replace('_', ' ') }}
              </span>
            </td>
            <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-400">
              {{ formatDate(user.created_at) }}
            </td>
            <td class="px-4 py-3.5">
              <span :class="user.is_active ? 'badge-green' : 'badge-gray'" class="text-xs">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1">
                <RouterLink
                  :to="`/admin/users/${user.id}`"
                  class="px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                >
                  View
                </RouterLink>
                <button
                  @click="toggleActive(user)"
                  :disabled="toggling === user.id"
                  class="px-2.5 py-1.5 text-xs font-medium rounded-lg transition-colors"
                  :class="user.is_active
                    ? 'text-amber-600 hover:bg-amber-50'
                    : 'text-emerald-600 hover:bg-emerald-50'"
                >
                  {{ toggling === user.id ? '…' : user.is_active ? 'Suspend' : 'Activate' }}
                </button>
                <button
                  @click="deleteUser(user)"
                  :disabled="deleting === user.id"
                  class="px-2.5 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                >
                  {{ deleting === user.id ? '…' : 'Delete' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-1.5">
      <button
        @click="goToPage(meta.current_page - 1)"
        :disabled="meta.current_page === 1"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
      >‹</button>
      <button
        v-for="p in pageRange" :key="p"
        @click="typeof p === 'number' && goToPage(p)"
        :disabled="p === '…'"
        :class="[
          'px-3 py-1.5 text-sm rounded-lg border transition-colors',
          p === meta.current_page
            ? 'bg-primary-600 text-white border-primary-600 font-semibold'
            : p === '…' ? 'border-transparent text-gray-400 cursor-default' : 'border-gray-200 hover:bg-gray-50',
        ]"
      >{{ p }}</button>
      <button
        @click="goToPage(meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
      >›</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import client from '@/api/client'
import { UsersIcon } from '@heroicons/vue/24/outline'

const loading = ref(true)
const users   = ref([])
const meta    = ref(null)
const filters = ref({ search: '', role: '', status: '', page: 1 })
const toggling = ref(null)
const deleting = ref(null)
let debounceTimer = null

async function load() {
  loading.value = true
  const params = { ...filters.value }
  if (params.status === 'active')   params.is_active = 1
  if (params.status === 'inactive') params.is_active = 0
  delete params.status
  const { data } = await client.get('/admin/users', { params })
  users.value   = data.data
  meta.value    = data.meta ?? { current_page: data.current_page, last_page: data.last_page, total: data.total }
  loading.value = false
}

function debouncedLoad() {
  filters.value.page = 1
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 300)
}

function goToPage(p) {
  if (p < 1 || p > meta.value?.last_page) return
  filters.value.page = p
  load()
}

const pageRange = computed(() => {
  const cur  = meta.value?.current_page ?? 1
  const last = meta.value?.last_page ?? 1
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  const pages = [1]
  if (cur > 3) pages.push('…')
  for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
  if (cur < last - 2) pages.push('…')
  pages.push(last)
  return pages
})

async function toggleActive(user) {
  toggling.value = user.id
  try {
    await client.patch(`/admin/users/${user.id}/toggle-active`)
    user.is_active = !user.is_active
  } finally {
    toggling.value = null
  }
}

async function deleteUser(user) {
  if (!confirm(`Permanently delete "${user.name}"? This cannot be undone.`)) return
  deleting.value = user.id
  try {
    await client.delete(`/admin/users/${user.id}`)
    users.value = users.value.filter(u => u.id !== user.id)
    if (meta.value) meta.value.total -= 1
  } finally {
    deleting.value = null
  }
}

function roleBadge(role) {
  return { employer: 'badge-blue', admin: 'badge-red', job_seeker: 'badge-green' }[role] ?? 'badge-gray'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(load)
</script>
