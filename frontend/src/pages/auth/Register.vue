<template>
  <div>
    <div class="mb-7">
      <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Create your account</h2>
      <p class="text-gray-500 text-sm">Join thousands of professionals on Connextion</p>
    </div>

    <!-- Role toggle -->
    <div class="flex rounded-2xl border border-gray-200 p-1 mb-6 bg-gray-50/80 gap-1">
      <button
        v-for="r in roles"
        :key="r.value"
        type="button"
        @click="form.role = r.value"
        :class="[
          'flex-1 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200',
          form.role === r.value
            ? 'bg-white shadow-sm text-primary-700 border border-gray-200'
            : 'text-gray-500 hover:text-gray-700 hover:bg-white/50',
        ]"
      >
        {{ r.label }}
      </button>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="label">Full name</label>
        <input v-model="form.name" type="text" class="input" placeholder="Maria Santos" required />
      </div>

      <div v-if="form.role === 'employer'" class="animate-fade-in">
        <label class="label">Company name</label>
        <input v-model="form.company_name" type="text" class="input" placeholder="Acme Corp" required />
      </div>

      <div>
        <label class="label">Email address</label>
        <input v-model="form.email" type="email" class="input" placeholder="you@example.com" required />
      </div>

      <div>
        <label class="label">Country</label>
        <input v-model="form.country" type="text" class="input" placeholder="Philippines" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Password</label>
          <input v-model="form.password" type="password" class="input" placeholder="Min. 8 chars" required minlength="8" />
        </div>
        <div>
          <label class="label">Confirm</label>
          <input v-model="form.password_confirmation" type="password" class="input" placeholder="Repeat" required />
        </div>
      </div>

      <!-- Errors -->
      <div v-if="errors" class="bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1 animate-fade-in">
        <p v-for="(msgs, field) in errors" :key="field" class="text-sm text-rose-700 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
          </svg>
          {{ msgs[0] }}
        </p>
      </div>

      <button type="submit" class="btn-primary w-full !py-3.5 text-base" :disabled="loading">
        <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ loading ? 'Creating account…' : 'Create account' }}
      </button>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-100 text-center text-sm text-gray-500">
      Already have an account?
      <RouterLink to="/login" class="text-primary-600 font-semibold hover:text-primary-700 ml-1">
        Sign in →
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
const errors  = ref(null)

const roles = [
  { value: 'job_seeker', label: '🌍 Looking for work' },
  { value: 'employer',   label: '🏢 I\'m hiring' },
]

const form = ref({
  name: '', email: '', password: '', password_confirmation: '',
  role: 'job_seeker', company_name: '', country: '',
})

async function submit() {
  loading.value = true
  errors.value  = null
  try {
    await auth.register(form.value)
  } catch (err) {
    errors.value = err.response?.data?.errors ?? null
  } finally {
    loading.value = false
  }
}
</script>
