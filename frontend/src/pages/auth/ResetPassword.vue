<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-extrabold text-gray-100 mb-1">Choose a new password</h2>
      <p class="text-gray-400 text-sm">Enter a new password for your account.</p>
    </div>

    <div
      v-if="done"
      class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-4 text-sm text-emerald-300"
    >
      Your password has been reset.
      <RouterLink to="/login" class="font-semibold underline ml-1">Sign in now</RouterLink>
    </div>

    <form v-else class="space-y-5" @submit.prevent="submit">
      <div>
        <label class="label" for="rp-email">Email</label>
        <input id="rp-email" v-model="form.email" type="email" class="input" required>
      </div>
      <div>
        <label class="label" for="rp-pass">New password</label>
        <input
          id="rp-pass"
          v-model="form.password"
          type="password"
          class="input"
          placeholder="At least 8 characters"
          required
        >
      </div>
      <div>
        <label class="label" for="rp-pass2">Confirm new password</label>
        <input
          id="rp-pass2"
          v-model="form.password_confirmation"
          type="password"
          class="input"
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
        {{ loading ? 'Resetting…' : 'Reset password' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { authApi } from '@/api/auth'

const route = useRoute()
const loading = ref(false)
const done = ref(false)
const error = ref('')
const form = ref({ token: '', email: '', password: '', password_confirmation: '' })

onMounted(() => {
  // Token + email arrive as query params from the reset email link.
  form.value.token = route.query.token ?? ''
  form.value.email = route.query.email ?? ''
})

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await authApi.resetPassword(form.value)
    done.value = true
  } catch (err) {
    error.value =
      Object.values(err.response?.data?.errors ?? {})[0]?.[0] ??
      err.response?.data?.message ??
      'Could not reset your password. The link may have expired.'
  } finally {
    loading.value = false
  }
}
</script>
