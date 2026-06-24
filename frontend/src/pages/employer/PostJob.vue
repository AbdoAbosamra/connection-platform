<template>
  <div class="max-w-3xl animate-fade-in">
    <div class="mb-7">
      <h1 class="text-2xl font-extrabold text-gray-900">{{ isEdit ? 'Edit Job' : 'Post a New Job' }}</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ isEdit ? 'Update your job listing details' : 'Fill in the details to attract the best candidates' }}</p>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <!-- Basic Information -->
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
            <label class="label">Category <span class="text-rose-500">*</span></label>
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

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Employment type <span class="text-rose-500">*</span></label>
            <select v-model="form.employment_type" class="input bg-gray-50 cursor-pointer" required>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>
          <div>
            <label class="label">Location type</label>
            <div class="input bg-emerald-50 border-emerald-200 text-emerald-700 font-semibold flex items-center gap-2 cursor-default select-none">
              🌐 Remote Only
              <span class="text-xs font-normal text-emerald-600 ms-1">— all positions on this platform are remote</span>
            </div>
          </div>
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
            <label class="label">Min salary (USD)</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
              <input v-model.number="form.salary_min" type="number" min="0" class="input !pl-7" placeholder="60,000" />
            </div>
          </div>
          <div>
            <label class="label">Max salary (USD)</label>
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
            <p v-if="salaryRangeError" class="mt-1 text-xs text-rose-600 font-medium">
              {{ salaryRangeError }}
            </p>
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

      <!-- Errors -->
      <div v-if="errors" class="bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1 animate-fade-in">
        <p v-for="(msgs, field) in errors" :key="field" class="text-sm text-rose-700">{{ msgs[0] }}</p>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary !px-8 !py-3" :disabled="loading">
          <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          {{ loading ? 'Saving…' : isEdit ? 'Update Job' : 'Publish Job' }}
        </button>
        <button type="button" @click="saveDraft" class="btn-secondary !px-6" :disabled="loading">
          Save as Draft
        </button>
        <RouterLink to="/employer/jobs" class="btn-ghost !px-6">Cancel</RouterLink>
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

// Live salary range guard
const salaryRangeError = computed(() => {
  const min = Number(form.value.salary_min) || 0
  const max = Number(form.value.salary_max) || 0
  if (min > 0 && max > 0 && max < min) {
    return `Max salary must be at least $${min.toLocaleString()}`
  }
  return null
})

const form = ref({
  title: '', category: '', experience_level: 'mid', employment_type: 'full_time',
  location_type: 'remote', salary_min: null, salary_max: null, salary_period: 'annual',
  salary_visible: true,
  description: '', requirements: '', benefits: '', expires_at: '', status: 'active',
})

async function submit()    { await save('active') }
async function saveDraft() { await save('draft')  }

async function save(status) {
  if (salaryRangeError.value) {
    errors.value = { salary_max: [salaryRangeError.value] }
    return
  }
  loading.value = true
  errors.value  = null
  try {
    form.value.status = status
    if (isEdit.value) {
      await jobsApi.updateJob(route.params.id, form.value)
    } else {
      await jobsApi.createJob(form.value)
    }
    router.push('/employer/jobs')
  } catch (err) {
    // Unverified employers are blocked from posting — send them to verify rather
    // than showing a dead-end error.
    if (err.response?.status === 403 && err.response?.data?.verification_required) {
      router.push('/employer/verification')
      return
    }
    errors.value = err.response?.data?.errors ?? null
    // Surface non-validation errors (e.g. credits exhausted, 403/500) too.
    if (!errors.value && err.response?.data?.message) {
      errors.value = { _: [err.response.data.message] }
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data } = await jobsApi.getJob(route.params.id)
      Object.assign(form.value, data.job)
      // Normalize skills from [{ id, name, pivot: { is_required } }] to [{ id, is_required }]
      form.value.skills = (data.job.skills ?? []).map(s => ({
        id: s.id,
        is_required: s.pivot?.is_required ?? true,
      }))
    } finally {
      loading.value = false
    }
  }
})
</script>
