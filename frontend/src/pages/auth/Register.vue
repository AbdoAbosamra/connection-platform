<template>
  <div>
    <div class="mb-7">
      <h2 class="text-2xl font-extrabold text-gray-100 mb-1">Create your account</h2>
      <p class="text-gray-400 text-sm">Join thousands of professionals on RemoteArena</p>
    </div>

    <!-- Role toggle -->
    <div class="flex rounded-2xl border border-gray-700 p-1 mb-6 bg-gray-800/60 gap-1">
      <button
        v-for="r in roles"
        :key="r.value"
        type="button"
        @click="form.role = r.value"
        :class="[
          'flex-1 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200',
          form.role === r.value
            ? 'bg-gray-700 shadow-sm text-amber-400 border border-gray-600'
            : 'text-gray-500 hover:text-gray-300 hover:bg-gray-700/50',
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
        <label class="label">{{ form.role === 'employer' ? 'Company email address' : 'Email address' }}</label>
        <input
          v-model="form.email"
          type="email"
          :class="['input', emailDomainError ? '!border-rose-400 !ring-rose-500/20' : '']"
          :placeholder="form.role === 'employer' ? 'you@yourcompany.com' : 'you@example.com'"
          required
        />
        <p v-if="form.role === 'employer' && !emailDomainError" class="mt-1 text-xs text-gray-500">
          Companies must register with a business email — not Gmail, Yahoo, Outlook, etc.
        </p>
        <p v-if="emailDomainError" class="mt-1 text-xs text-rose-500 font-medium">
          {{ emailDomainError }}
        </p>
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

      <!-- Legal consent checkbox — required before submitting -->
      <div class="flex items-start gap-3 bg-gray-800/40 border border-gray-700/60 rounded-xl p-4">
        <input
          id="agree"
          v-model="agreedToTerms"
          type="checkbox"
          class="mt-0.5 w-4 h-4 rounded border-gray-600 bg-gray-700 text-primary-500 focus:ring-primary-500 focus:ring-offset-gray-900 cursor-pointer flex-shrink-0"
        />
        <label for="agree" class="text-xs text-gray-400 leading-relaxed cursor-pointer">
          I have read and agree to RemoteArena's
          <RouterLink to="/terms" target="_blank" class="text-amber-400 hover:text-amber-300 underline underline-offset-2">Terms &amp; Conditions</RouterLink>
          and
          <RouterLink to="/privacy" target="_blank" class="text-amber-400 hover:text-amber-300 underline underline-offset-2">Privacy Policy</RouterLink>.
          I understand that my profile data may be visible to registered employers.
        </label>
      </div>

      <!-- Generic error (network / server errors) -->
      <div v-if="generalError" class="flex items-start gap-2.5 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 animate-fade-in">
        <svg class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        <p class="text-sm text-rose-700">{{ generalError }}</p>
      </div>

      <!-- Validation field errors (422) -->
      <div v-if="fieldErrors" class="bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1 animate-fade-in">
        <p v-for="(msgs, field) in fieldErrors" :key="field" class="text-sm text-rose-700 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
          </svg>
          {{ Array.isArray(msgs) ? msgs[0] : msgs }}
        </p>
      </div>

      <button type="submit" class="btn-primary w-full !py-3.5 text-base" :disabled="loading || !agreedToTerms || !!emailDomainError">
        <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ loading ? 'Creating account…' : 'Create account' }}
      </button>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-800 text-center text-sm text-gray-500">
      Already have an account?
      <RouterLink to="/login" class="text-amber-400 font-semibold hover:text-amber-300 ml-1">
        Sign in →
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth         = useAuthStore()
const loading      = ref(false)
const agreedToTerms = ref(false) // must be checked before the form submits
const fieldErrors  = ref(null)   // 422 validation errors (object of field → [messages])
const generalError = ref('')     // network / server / any other error

const roles = [
  { value: 'job_seeker', label: '🌍 Job Seeker' },
  { value: 'employer',   label: '🏢 Hiring company' },
]

// Free / personal / disposable providers blocked for company accounts. This
// mirrors the backend EmailDomainClassifier for instant feedback; the backend
// remains the source of truth (BusinessEmail rule + AuthService guard).
const FREE_EMAIL_DOMAINS = new Set([
  'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'yahoo.co.in', 'ymail.com',
  'rocketmail.com', 'hotmail.com', 'hotmail.co.uk', 'outlook.com', 'live.com', 'msn.com',
  'aol.com', 'icloud.com', 'me.com', 'mac.com', 'protonmail.com', 'proton.me', 'pm.me',
  'gmx.com', 'gmx.de', 'mail.com', 'yandex.com', 'yandex.ru', 'zoho.com', 'zohomail.com',
  'fastmail.com', 'hey.com', 'tutanota.com', 'qq.com', '163.com', '126.com', 'naver.com',
  'web.de', 'comcast.net', 'verizon.net', 'att.net', 'sbcglobal.net', 'cox.net', 'btinternet.com',
  'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'temp-mail.org', 'tempmail.com',
  'yopmail.com', 'trashmail.com', 'sharklasers.com', 'maildrop.cc', 'getnada.com',
])

const form = ref({
  name: '', email: '', password: '', password_confirmation: '',
  role: 'job_seeker', company_name: '', country: '',
})

// Only companies are required to use a business email. Empty/incomplete input is
// left to the native `required`/email validation rather than flagged here.
const emailDomainError = computed(() => {
  if (form.value.role !== 'employer') return ''
  const email = form.value.email.trim().toLowerCase()
  const at = email.lastIndexOf('@')
  if (at < 1 || at === email.length - 1) return ''
  const domain = email.slice(at + 1)
  if (FREE_EMAIL_DOMAINS.has(domain)) {
    return 'Please use your company email address — free or personal providers (Gmail, Yahoo, Outlook, etc.) are not accepted for company accounts.'
  }
  return ''
})

async function submit() {
  loading.value     = true
  fieldErrors.value = null
  generalError.value = ''

  // Client-side business-email guard (the backend enforces this too).
  if (emailDomainError.value) {
    loading.value = false
    return
  }

  try {
    const payload = { ...form.value }
    if (payload.role !== 'employer') delete payload.company_name
    await auth.register(payload)
  } catch (err) {
    const status = err.response?.status

    if (status === 422 && err.response?.data?.errors) {
      // Laravel validation errors — show per-field
      fieldErrors.value = err.response.data.errors
    } else if (err.response?.data?.message) {
      // Server returned a readable message (e.g. 403 suspended, 500, etc.)
      generalError.value = err.response.data.message
    } else if (!err.response) {
      // No response at all — server down or network issue
      generalError.value = 'Cannot reach the server. Please check your connection and try again.'
    } else {
      generalError.value = 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
