<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-extrabold text-gray-100 mb-1">Welcome back</h2>
      <p class="text-gray-400 text-sm">Sign in to your RemoteArena account</p>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <div>
        <label class="label">Email address</label>
        <div class="relative">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
          <input v-model="form.email" type="email" class="input !pl-10" placeholder="you@example.com" required />
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label class="label">Password</label>
          <RouterLink to="/forgot-password" class="text-xs text-amber-400 hover:text-amber-300 mb-1.5">
            Forgot password?
          </RouterLink>
        </div>
        <div class="relative">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
          <input v-model="form.password" type="password" class="input !pl-10" placeholder="••••••••" required />
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="flex items-start gap-2.5 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700 animate-fade-in">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        {{ error }}
      </div>

      <button type="submit" class="btn-primary w-full !py-3.5 text-base mt-2" :disabled="loading">
        <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span>{{ loading ? 'Signing in…' : 'Sign in' }}</span>
      </button>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-800 text-center text-sm text-gray-500">
      Don't have an account?
      <RouterLink to="/register" class="text-amber-400 font-semibold hover:text-amber-300 ml-1">
        Create one free →
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const loading = ref(false)
const error   = ref('')
const form    = ref({ email: '', password: '' })

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(form.value)
  } catch (err) {
    const status = err.response?.status

    if (status === 422) {
      // Invalid credentials returned as validation error by Laravel
      error.value = err.response?.data?.errors?.email?.[0]
        ?? err.response?.data?.message
        ?? 'Invalid email or password.'
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (!err.response) {
      error.value = 'Cannot reach the server. Please check your connection.'
    } else {
      error.value = 'Login failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
