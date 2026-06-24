<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-extrabold text-gray-100 mb-1">Forgot your password?</h2>
      <p class="text-gray-400 text-sm">Enter your email and we'll send you a reset link.</p>
    </div>

    <div
      v-if="sent"
      class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-4 text-sm text-emerald-300"
    >
      If an account exists for <strong>{{ form.email }}</strong>, a password reset link is on its way.
      Check your inbox (and spam folder).
    </div>

    <form v-else class="space-y-5" @submit.prevent="submit">
      <div>
        <label class="label" for="fp-email">Email address</label>
        <input
          id="fp-email"
          v-model="form.email"
          type="email"
          class="input"
          placeholder="you@example.com"
          required
        >
      </div>

      <div
        v-if="error"
        class="bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3 text-sm text-rose-300"
      >
        {{ error }}
      </div>

      <button type="submit" class="btn-primary w-full !py-3.5 text-base" :disabled="loading">
        {{ loading ? 'Sending…' : 'Send reset link' }}
      </button>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-800 text-center text-sm text-gray-500">
      Remembered it?
      <RouterLink to="/login" class="text-amber-400 font-semibold hover:text-amber-300 ml-1">
        Back to sign in
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { authApi } from '@/api/auth'

const loading = ref(false)
const sent = ref(false)
const error = ref('')
const form = ref({ email: '' })

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await authApi.forgotPassword(form.value.email)
    sent.value = true
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
