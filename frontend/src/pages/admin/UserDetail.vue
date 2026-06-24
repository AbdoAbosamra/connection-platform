<template>
  <div class="space-y-5 animate-fade-in">

    <!-- Back -->
    <RouterLink to="/admin/users" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 transition-colors font-medium">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
      </svg>
      Back to Users
    </RouterLink>

    <!-- Skeleton -->
    <template v-if="loading">
      <div class="card p-6 h-40 animate-pulse bg-gray-50" />
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card p-6 h-48 animate-pulse bg-gray-50" />
        <div class="card p-6 h-48 animate-pulse bg-gray-50" />
      </div>
    </template>

    <!-- Error -->
    <div v-else-if="error" class="card p-10 text-center">
      <p class="text-red-500 font-medium">{{ error }}</p>
      <button @click="load" class="btn-secondary mt-4 text-sm">Try Again</button>
    </div>

    <template v-else-if="user">
      <!-- Profile header -->
      <div class="card p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
          <!-- Avatar -->
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-violet-500 flex items-center justify-center text-white font-extrabold text-2xl shadow-lg shadow-primary-500/30 flex-shrink-0">
            {{ user.name[0]?.toUpperCase() }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="text-xl font-extrabold text-gray-900">{{ user.name }}</h1>
              <span :class="roleBadge(user.role)" class="text-xs capitalize">{{ user.role.replace('_', ' ') }}</span>
              <span :class="user.is_active ? 'badge-green' : 'badge-red'" class="text-xs">
                {{ user.is_active ? 'Active' : 'Suspended' }}
              </span>
            </div>
            <p class="text-gray-500 text-sm">{{ user.email }}</p>
            <div class="flex flex-wrap gap-4 mt-2 text-xs text-gray-400">
              <span v-if="user.country">🌍 {{ user.country }}</span>
              <span>Joined {{ formatDate(user.created_at) }}</span>
              <span v-if="user.last_seen_at">Last seen {{ formatDate(user.last_seen_at) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="toggleActive"
              :disabled="toggling"
              class="btn-secondary text-sm flex items-center gap-2"
              :class="user.is_active ? 'hover:text-amber-600 hover:border-amber-300' : 'hover:text-emerald-600 hover:border-emerald-300'"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
              {{ toggling ? '…' : user.is_active ? 'Suspend Account' : 'Activate Account' }}
            </button>
            <button
              @click="deleteUser"
              :disabled="deleting"
              class="btn-secondary text-sm text-red-500 hover:text-red-600 hover:border-red-300 flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
              {{ deleting ? '…' : 'Delete User' }}
            </button>
            <button
              v-if="user.role !== 'admin'"
              :disabled="erasing"
              class="btn-secondary text-sm text-rose-600 hover:text-white hover:bg-rose-600 hover:border-rose-600 flex items-center gap-2"
              title="GDPR Right to be Forgotten — irreversibly anonymise this user's data"
              @click="eraseUser"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
              </svg>
              {{ erasing ? '…' : 'Erase Data (GDPR)' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Profile details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Employer profile -->
        <div v-if="user.role === 'employer' && user.employer_profile" class="card p-6">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Employer Profile</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-xs text-gray-400 font-medium">Company Name</dt>
              <dd class="text-sm font-semibold text-gray-800 mt-0.5">{{ user.employer_profile.company_name || '—' }}</dd>
            </div>
            <div v-if="user.employer_profile.website">
              <dt class="text-xs text-gray-400 font-medium">Website</dt>
              <dd class="text-sm text-primary-600 mt-0.5">{{ user.employer_profile.website }}</dd>
            </div>
            <div v-if="user.employer_profile.industry">
              <dt class="text-xs text-gray-400 font-medium">Industry</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.employer_profile.industry }}</dd>
            </div>
            <div v-if="user.employer_profile.company_size">
              <dt class="text-xs text-gray-400 font-medium">Company Size</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.employer_profile.company_size }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Credits</dt>
              <dd class="text-sm font-semibold text-gray-800 mt-0.5">{{ user.employer_profile.job_post_credits ?? 0 }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Subscription</dt>
              <dd class="mt-0.5">
                <span class="badge-blue text-xs capitalize">{{ user.employer_profile.subscription_tier ?? 'free' }}</span>
              </dd>
            </div>
          </dl>
        </div>

        <!-- Job seeker profile -->
        <div v-if="user.role === 'job_seeker' && user.job_seeker_profile" class="card p-6">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Job Seeker Profile</h2>
          <dl class="space-y-3">
            <div v-if="user.job_seeker_profile.headline">
              <dt class="text-xs text-gray-400 font-medium">Headline</dt>
              <dd class="text-sm font-semibold text-gray-800 mt-0.5">{{ user.job_seeker_profile.headline }}</dd>
            </div>
            <div v-if="user.job_seeker_profile.current_city || user.job_seeker_profile.current_country">
              <dt class="text-xs text-gray-400 font-medium">Location</dt>
              <dd class="text-sm text-gray-700 mt-0.5">
                {{ [user.job_seeker_profile.current_city, user.job_seeker_profile.current_country].filter(Boolean).join(', ') }}
              </dd>
            </div>
            <div v-if="user.job_seeker_profile.years_of_experience != null">
              <dt class="text-xs text-gray-400 font-medium">Experience</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.job_seeker_profile.years_of_experience }} years</dd>
            </div>
            <div v-if="user.job_seeker_profile.availability">
              <dt class="text-xs text-gray-400 font-medium">Availability</dt>
              <dd class="text-sm text-gray-700 mt-0.5 capitalize">{{ user.job_seeker_profile.availability.replace('_', ' ') }}</dd>
            </div>
            <div v-if="user.job_seeker_profile.skills?.length">
              <dt class="text-xs text-gray-400 font-medium mb-1.5">Skills</dt>
              <dd class="flex flex-wrap gap-1.5">
                <span v-for="skill in user.job_seeker_profile.skills" :key="skill.id"
                  class="px-2 py-0.5 bg-primary-50 text-primary-700 text-xs font-medium rounded-lg">
                  {{ skill.name }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Work Preference</dt>
              <dd class="mt-0.5">
                <span class="badge-green text-xs">Remote</span>
              </dd>
            </div>
          </dl>
        </div>

        <!-- Admin placeholder -->
        <div v-if="user.role === 'admin'" class="card p-6 flex items-center justify-center">
          <div class="text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <p class="text-sm font-medium">Platform Administrator</p>
          </div>
        </div>

        <!-- Account details -->
        <div class="card p-6">
          <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Account Details</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-xs text-gray-400 font-medium">User ID</dt>
              <dd class="text-sm text-gray-700 font-mono mt-0.5">#{{ user.id }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Email</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.email }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Country</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.country || '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Account Status</dt>
              <dd class="mt-0.5 flex items-center gap-2">
                <span :class="user.is_active ? 'badge-green' : 'badge-red'" class="text-xs">
                  {{ user.is_active ? 'Active' : 'Suspended' }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Registered</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ formatDate(user.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-xs text-gray-400 font-medium">Last Seen</dt>
              <dd class="text-sm text-gray-700 mt-0.5">{{ user.last_seen_at ? formatDate(user.last_seen_at) : 'Never' }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import client from '@/api/client'

const route  = useRoute()
const router = useRouter()

const loading  = ref(true)
const error    = ref('')
const user     = ref(null)
const toggling = ref(false)
const deleting = ref(false)
const erasing  = ref(false)

async function load() {
  loading.value = true
  error.value   = ''
  try {
    const { data } = await client.get(`/admin/users/${route.params.id}`)
    user.value = data.user
  } catch {
    error.value = 'Failed to load user. They may have been deleted.'
  } finally {
    loading.value = false
  }
}

async function toggleActive() {
  toggling.value = true
  try {
    await client.patch(`/admin/users/${user.value.id}/toggle-active`)
    user.value.is_active = !user.value.is_active
  } finally {
    toggling.value = false
  }
}

async function deleteUser() {
  if (!confirm(`Permanently delete "${user.value.name}"? This cannot be undone.`)) return
  deleting.value = true
  try {
    await client.delete(`/admin/users/${user.value.id}`)
    router.push('/admin/users')
  } finally {
    deleting.value = false
  }
}

async function eraseUser() {
  if (!confirm(
    `GDPR ERASURE for "${user.value.name}".\n\n` +
    'This permanently anonymises their personal data (name, email, profile, ' +
    'resume, messages and uploaded files) across the whole platform. ' +
    'It CANNOT be undone. Continue?'
  )) return
  erasing.value = true
  try {
    await client.delete(`/admin/users/${user.value.id}/forget`)
    router.push('/admin/users')
  } finally {
    erasing.value = false
  }
}

function roleBadge(role) {
  return { employer: 'badge-blue', admin: 'badge-red', job_seeker: 'badge-green' }[role] ?? 'badge-gray'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}

onMounted(load)
</script>
