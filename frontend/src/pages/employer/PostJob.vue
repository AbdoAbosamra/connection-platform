<template>
  <div class="max-w-3xl animate-fade-in">
    <div class="mb-7">
      <h1 class="text-2xl font-extrabold text-gray-900">{{ isEdit ? 'Edit Job' : 'Post a New Job' }}</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ isEdit ? 'Update your job listing details' : 'Fill in the details to attract the best candidates' }}</p>
    </div>

    <!-- Stepper -->
    <div class="flex items-center gap-3 mb-7">
      <button
        v-for="s in steps" :key="s.n" type="button"
        @click="goToStep(s.n)"
        class="flex items-center gap-2.5 flex-1 text-left group"
      >
        <span
          class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 transition-colors"
          :class="step === s.n ? 'bg-primary-600 text-white' : step > s.n ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-400'"
        >
          <svg v-if="step > s.n" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
          <template v-else>{{ s.n }}</template>
        </span>
        <div class="min-w-0">
          <p class="text-[11px] uppercase tracking-wide font-semibold" :class="step >= s.n ? 'text-primary-600' : 'text-gray-400'">Step {{ s.n }}</p>
          <p class="text-sm font-semibold truncate" :class="step >= s.n ? 'text-gray-900' : 'text-gray-400'">{{ s.label }}</p>
        </div>
        <div v-if="s.n < steps.length" class="hidden sm:block flex-1 h-px bg-gray-200 mx-1" />
      </button>
    </div>

    <form @submit.prevent="onSubmit" class="space-y-5">
      <!-- ══ STEP 1 · Basic Information ══════════════════════════════ -->
      <template v-if="step === 1">
        <div class="card p-6 space-y-5">
          <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
            <span class="w-7 h-7 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
            </span>
            Basic Information
          </h2>

          <div>
            <label class="label">Job title <span class="text-rose-500">*</span></label>
            <input v-model="form.title" class="input" placeholder="Senior React Developer" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Department / Category <span class="text-rose-500">*</span></label>
              <input v-model="form.category" class="input" placeholder="Engineering" required />
            </div>
            <div>
              <label class="label">Experience level <span class="text-rose-500">*</span></label>
              <select v-model="form.experience_level" class="input bg-gray-50 cursor-pointer" required>
                <option value="entry">Entry level</option>
                <option value="mid">Mid level</option>
                <option value="senior">Senior level</option>
                <option value="lead">Lead</option>
                <option value="executive">Executive</option>
              </select>
            </div>
          </div>

          <div>
            <label class="label">Employment type <span class="text-rose-500">*</span></label>
            <select v-model="form.employment_type" class="input bg-gray-50 cursor-pointer w-full sm:w-1/2" required>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>
        </div>

        <!-- Compensation -->
        <div class="card p-6 space-y-5">
          <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
            <span class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
            Compensation
          </h2>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="label">Min salary</label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                <input v-model.number="form.salary_min" type="number" min="0" class="input !pl-7" placeholder="60,000" />
              </div>
            </div>
            <div>
              <label class="label">Max salary</label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                <input
                  v-model.number="form.salary_max"
                  type="number"
                  min="0"
                  :class="['input !pl-7', salaryRangeError ? '!border-rose-400 !ring-rose-100' : '']"
                  placeholder="100,000"
                />
              </div>
              <p v-if="salaryRangeError" class="mt-1 text-xs text-rose-600 font-medium">{{ salaryRangeError }}</p>
            </div>
            <div>
              <label class="label">Period</label>
              <select v-model="form.salary_period" class="input bg-gray-50 cursor-pointer">
                <option value="annual">Annual</option>
                <option value="monthly">Monthly</option>
                <option value="hourly">Hourly</option>
              </select>
            </div>
          </div>

          <label class="flex items-center gap-2.5 cursor-pointer group">
            <input v-model="form.salary_visible" type="checkbox" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300" />
            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show salary range to applicants</span>
          </label>
        </div>

        <!-- Job Details -->
        <div class="card p-6 space-y-5">
          <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
            <span class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
              </svg>
            </span>
            Job Details
          </h2>

          <div>
            <label class="label">Description <span class="text-rose-500">*</span></label>
            <textarea v-model="form.description" rows="8" class="input resize-none" placeholder="Describe the role, responsibilities, and what you're looking for…" required minlength="100" />
          </div>
          <div>
            <label class="label">Requirements</label>
            <textarea v-model="form.requirements" rows="5" class="input resize-none" placeholder="List required skills, qualifications, and experience…" />
          </div>
          <div>
            <label class="label">Benefits & Perks</label>
            <textarea v-model="form.benefits" rows="4" class="input resize-none" placeholder="Health insurance, equity, PTO, remote stipend…" />
          </div>
          <div>
            <label class="label">Expires at</label>
            <input v-model="form.expires_at" type="date" class="input bg-gray-50 cursor-pointer w-48" />
          </div>
        </div>
      </template>

      <!-- ══ STEP 2 · Location & Hiring Mode ═════════════════════════ -->
      <template v-else>
        <div class="card p-6 space-y-6">
          <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
            <span class="w-7 h-7 bg-violet-50 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
            </span>
            Location & Hiring Mode
          </h2>

          <!-- Hiring mode radio cards -->
          <div>
            <label class="label mb-2">How are you hiring for this role? <span class="text-rose-500">*</span></label>
            <div class="grid gap-3">
              <label
                v-for="mode in hiringModes" :key="mode.value"
                class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                :class="form.hiring_mode === mode.value ? 'border-primary-400 bg-primary-50/60 ring-1 ring-primary-200' : 'border-gray-200 hover:border-gray-300'"
              >
                <input type="radio" :value="mode.value" v-model="form.hiring_mode" class="mt-1 text-primary-600 focus:ring-primary-500" />
                <div>
                  <p class="font-semibold text-gray-900 text-sm flex items-center gap-1.5">{{ mode.emoji }} {{ mode.label }}</p>
                  <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ mode.desc }}</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Local Only → city / state / country -->
          <div v-if="form.hiring_mode === 'local'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-fade-in">
            <div>
              <label class="label">City <span class="text-rose-500">*</span></label>
              <input v-model="form.location_city" class="input" placeholder="Denver" :required="form.hiring_mode === 'local'" />
            </div>
            <div>
              <label class="label">State / Region</label>
              <input v-model="form.location_state" class="input" placeholder="CO" />
            </div>
            <div>
              <label class="label">Country</label>
              <input v-model="form.location_country" class="input" placeholder="United States" />
            </div>
          </div>

          <!-- Remote (Same Country) → country only -->
          <div v-else-if="form.hiring_mode === 'national_remote'" class="animate-fade-in">
            <label class="label">Country <span class="text-rose-500">*</span></label>
            <input v-model="form.location_country" class="input sm:w-1/2" placeholder="United States" :required="form.hiring_mode === 'national_remote'" />
            <p class="text-xs text-gray-500 mt-1.5">Candidates work remotely, but must be located in this country.</p>
          </div>

          <!-- International Remote → the full remote-international block -->
          <div v-else class="space-y-5 animate-fade-in rounded-xl bg-slate-50 border border-slate-200 p-5">
            <p class="text-xs text-slate-500 leading-relaxed">
              International candidates stay in their own country and work remotely — no visa or relocation needed.
              Tell us how you want to collaborate across borders.
            </p>

            <div>
              <label class="label">Countries accepted <span class="text-rose-500">*</span></label>
              <input v-model="form.accepted_countries_text" class="input" placeholder="Egypt, Morocco, Kenya, India" :required="form.hiring_mode === 'international_remote'" />
              <p class="text-xs text-gray-400 mt-1">Comma-separated. Where candidates may be based.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="label">Time zones</label>
                <input v-model="form.time_zones_text" class="input" placeholder="UTC+1, UTC+2" />
                <p class="text-xs text-gray-400 mt-1">Comma-separated.</p>
              </div>
              <div>
                <label class="label">Languages</label>
                <input v-model="form.languages_text" class="input" placeholder="English, French" />
                <p class="text-xs text-gray-400 mt-1">Comma-separated.</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="label">Engagement type</label>
                <select v-model="form.contract_type" class="input bg-white cursor-pointer">
                  <option value="">No preference</option>
                  <option value="contractor">Contractor</option>
                  <option value="remote_employee">Remote employee</option>
                </select>
              </div>
              <div>
                <label class="label">Working hours</label>
                <input v-model="form.working_hours" class="input" placeholder="4h overlap with EST" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="label">Currency preference</label>
                <input v-model="form.currency_preference" maxlength="3" class="input uppercase" placeholder="USD" />
              </div>
              <div>
                <label class="label">International payroll preference <span class="text-gray-400 font-normal">(optional)</span></label>
                <input v-model="form.payroll_preference" class="input" placeholder="e.g. Deel, Remote.com, direct" />
              </div>
            </div>

            <div>
              <label class="label">Cross-border collaboration preferences <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea v-model="form.collaboration_preferences" rows="3" class="input resize-none bg-white" placeholder="Async-first, weekly sync, tools you use…" />
            </div>
          </div>
        </div>
      </template>

      <!-- Errors -->
      <div v-if="errors" class="bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1 animate-fade-in">
        <p v-for="(msgs, field) in errors" :key="field" class="text-sm text-rose-700">{{ msgs[0] }}</p>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-3">
        <template v-if="step === 1">
          <button type="button" @click="goToStep(2)" class="btn-primary !px-8 !py-3">
            Next: Location
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </button>
          <RouterLink to="/employer/jobs" class="btn-ghost !px-6">Cancel</RouterLink>
        </template>
        <template v-else>
          <button type="submit" class="btn-primary !px-8 !py-3" :disabled="loading">
            <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ loading ? 'Saving…' : isEdit ? 'Update Job' : 'Publish Job' }}
          </button>
          <button type="button" @click="saveDraft" class="btn-secondary !px-6" :disabled="loading">Save as Draft</button>
          <button type="button" @click="goToStep(1)" class="btn-ghost !px-6">← Back</button>
        </template>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { jobsApi } from '@/api/jobs'

const route  = useRoute()
const router = useRouter()

const isEdit  = computed(() => !!route.params.id)
const loading = ref(false)
const errors  = ref(null)
const step    = ref(1)

const steps = [
  { n: 1, label: 'Basic Information' },
  { n: 2, label: 'Location' },
]

const hiringModes = [
  { value: 'local',                emoji: '📍', label: 'Local Only',           desc: 'Hire in a specific city — on-site or hybrid.' },
  { value: 'national_remote',      emoji: '🏠', label: 'Remote (Same Country)', desc: 'Remote work, but candidates must be in your country.' },
  { value: 'international_remote', emoji: '🌍', label: 'International Remote',   desc: 'Hire remote talent in other countries. Optional.' },
]

// Live salary range guard
const salaryRangeError = computed(() => {
  const min = Number(form.value.salary_min) || 0
  const max = Number(form.value.salary_max) || 0
  if (min > 0 && max > 0 && max < min) return `Max salary must be at least $${min.toLocaleString()}`
  return null
})

const form = ref({
  title: '', category: '', experience_level: 'mid', employment_type: 'full_time',
  hiring_mode: 'local',
  location_city: '', location_state: '', location_country: '',
  accepted_countries_text: '', time_zones_text: '', languages_text: '',
  contract_type: '', working_hours: '', currency_preference: '', payroll_preference: '', collaboration_preferences: '',
  salary_min: null, salary_max: null, salary_period: 'annual', salary_visible: true,
  description: '', requirements: '', benefits: '', expires_at: '', status: 'active',
})

function goToStep(n) {
  errors.value = null
  step.value = n
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Split a comma-separated string into a trimmed, non-empty array.
function toList(str) {
  return (str || '')
    .split(',')
    .map(s => s.trim())
    .filter(Boolean)
}

function buildPayload(status) {
  const f = form.value
  const payload = {
    title: f.title, category: f.category, experience_level: f.experience_level,
    employment_type: f.employment_type, hiring_mode: f.hiring_mode,
    salary_min: f.salary_min, salary_max: f.salary_max, salary_period: f.salary_period,
    salary_visible: f.salary_visible, description: f.description, requirements: f.requirements,
    benefits: f.benefits, expires_at: f.expires_at || null, status,
  }

  if (f.hiring_mode === 'local') {
    payload.location_city = f.location_city
    payload.location_state = f.location_state
    payload.location_country = f.location_country
  } else if (f.hiring_mode === 'national_remote') {
    payload.location_country = f.location_country
  } else if (f.hiring_mode === 'international_remote') {
    payload.accepted_countries = toList(f.accepted_countries_text)
    payload.time_zones = toList(f.time_zones_text)
    payload.languages = toList(f.languages_text)
    payload.contract_type = f.contract_type || null
    payload.working_hours = f.working_hours || null
    payload.currency_preference = f.currency_preference ? f.currency_preference.toUpperCase() : null
    payload.payroll_preference = f.payroll_preference || null
    payload.collaboration_preferences = f.collaboration_preferences || null
  }

  return payload
}

async function onSubmit() { await save('active') }
async function saveDraft() { await save('draft') }

async function save(status) {
  if (salaryRangeError.value) {
    errors.value = { salary_max: [salaryRangeError.value] }
    return
  }
  loading.value = true
  errors.value  = null
  try {
    const payload = buildPayload(status)
    if (isEdit.value) {
      await jobsApi.updateJob(route.params.id, payload)
    } else {
      await jobsApi.createJob(payload)
    }
    router.push('/employer/jobs')
  } catch (err) {
    if (err.response?.status === 403 && err.response?.data?.verification_required) {
      router.push('/employer/verification')
      return
    }
    errors.value = err.response?.data?.errors ?? null
    if (!errors.value && err.response?.data?.message) {
      errors.value = { _: [err.response.data.message] }
    }
    // Validation errors likely live on step 1 (title/description) — surface them.
    if (errors.value && (errors.value.title || errors.value.description || errors.value.category)) {
      step.value = 1
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data } = await jobsApi.getJob(route.params.id)
      const job = data.job
      Object.assign(form.value, job)
      // Arrays → comma text for the inputs
      form.value.accepted_countries_text = (job.accepted_countries ?? []).join(', ')
      form.value.time_zones_text = (job.time_zones ?? []).join(', ')
      form.value.languages_text = (job.languages ?? []).join(', ')
      form.value.hiring_mode = job.hiring_mode ?? 'local'
      form.value.skills = (job.skills ?? []).map(s => ({ id: s.id, is_required: s.pivot?.is_required ?? true }))
    } finally {
      loading.value = false
    }
  }
})
</script>
