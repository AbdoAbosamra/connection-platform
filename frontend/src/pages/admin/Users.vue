<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Users</h1>
    </div>

    <div class="flex gap-2">
      <input v-model="filters.search" @input="debouncedLoad" class="input text-sm w-56" placeholder="Search by name or email…" />
      <select v-model="filters.role" class="input text-sm w-36" @change="load">
        <option value="">All roles</option>
        <option value="employer">Employer</option>
        <option value="job_seeker">Job Seeker</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="card p-4 h-16 animate-pulse bg-gray-100" />
    </div>

    <div v-else-if="users.length === 0" class="card p-10 text-center text-gray-400">No users found.</div>

    <div v-else class="card divide-y divide-gray-100">
      <div v-for="user in users" :key="user.id" class="p-4 flex items-center gap-4 hover:bg-gray-50">
        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-sm flex-shrink-0">
          {{ user.name[0] }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm">{{ user.name }}</p>
          <p class="text-xs text-gray-400">{{ user.email }}</p>
        </div>
        <span :class="user.role === 'employer' ? 'badge-blue' : user.role === 'admin' ? 'badge-red' : 'badge-green'" class="text-xs capitalize">
          {{ user.role.replace('_', ' ') }}
        </span>
        <span :class="user.is_active ? 'badge-green' : 'badge-gray'" class="text-xs">
          {{ user.is_active ? 'Active' : 'Inactive' }}
        </span>
        <div class="flex gap-2">
          <button @click="toggleActive(user)" class="text-xs text-gray-500 hover:underline">
            {{ user.is_active ? 'Deactivate' : 'Activate' }}
          </button>
          <button @click="deleteUser(user)" class="text-xs text-red-500 hover:underline">Delete</button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-2">
      <button
        v-for="p in meta.last_page" :key="p"
        @click="goToPage(p)"
        :class="['px-3 py-1 text-sm rounded border', p === meta.current_page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 hover:bg-gray-50']"
      >{{ p }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'

const loading = ref(true)
const users   = ref([])
const meta    = ref(null)
const filters = ref({ search: '', role: '', page: 1 })
let debounceTimer = null

async function load() {
  loading.value = true
  const { data } = await client.get('/admin/users', { params: filters.value })
  users.value   = data.data
  meta.value    = data.meta ?? { current_page: data.current_page, last_page: data.last_page }
  loading.value = false
}

function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 300)
}

function goToPage(p) {
  filters.value.page = p
  load()
}

async function toggleActive(user) {
  await client.patch(`/admin/users/${user.id}/toggle-active`)
  user.is_active = !user.is_active
}

async function deleteUser(user) {
  if (!confirm(`Delete user "${user.name}"? This cannot be undone.`)) return
  await client.delete(`/admin/users/${user.id}`)
  users.value = users.value.filter(u => u.id !== user.id)
}

onMounted(load)
</script>
