<template>
  <div class="max-w-3xl">
    <h1 class="text-2xl font-bold mb-6">{{ isEdit ? 'Edit Job' : 'Post a New Job' }}</h1>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-700 border-b pb-2">Basic Information</h2>

        <div>
          <label class="label">Job title *</label>
          <input v-model="form.title" class="input" placeholder="Senior React Developer" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Category *</label>
            <input v-model="form.category" class="input" placeholder="Engineering" required />
          </div>
          <div>
            <label class="label">Experience level *</label>
            <select v-model="form.experience_level" class="input" required>
              <option value="entry">Entry</option>
              <option value="mid">Mid</option>
              <option value="senior">Senior</option>
              <option value="lead">Lead</option>
              <option value="executive">Executive</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Employment type *</label>
            <select v-model="form.employment_type" class="input" required>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>
          <div>
            <label class="label">Location type *</label>
            <select v-model="form.location_type" class="input" required>
              <option value="remote">Remote</option>
              <option value="hybrid">Hybrid</option>
              <option value="on_site">On-site</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.visa_sponsorship" type="checkbox" class="rounded text-primary-600" />
            Offer visa sponsorship
          </label>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.open_to_international" type="checkbox" class="rounded text-primary-600" />
            Open to international applicants
          </label>
        </div>
      </div>

      <!-- Salary -->
      <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-700 border-b pb-2">Compensation</h2>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="label">Min salary (USD)</label>
            <input v-model.number="form.salary_min" type="number" class="input" placeholder="60000" />
          </div>
          <div>
            <label class="label">Max salary (USD)</label>
            <input v-model.number="form.salary_max" type="number" class="input" placeholder="100000" />
          </div>
          <div>
            <label class="label">Period</label>
            <select v-model="form.salary_period" class="input">
              <option value="annual">Annual</option>
              <option value="monthly">Monthly</option>
              <option value="hourly">Hourly</option>
            </select>
          </div>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.salary_visible" type="checkbox" class="rounded text-primary-600" />
          Show salary to applicants
        </label>
      </div>

      <!-- Description -->
      <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-700 border-b pb-2">Job Details</h2>
        <div>
          <label class="label">Description *</label>
          <textarea v-model="form.description" rows="8" class="input" placeholder="Describe the role, responsibilities…" required minlength="100"></textarea>
        </div>
        <div>
          <label class="label">Requirements</label>
          <textarea v-model="form.requirements" rows="5" class="input" placeholder="List skills, qualifications…"></textarea>
        </div>
        <div>
          <label class="label">Benefits</label>
          <textarea v-model="form.benefits" rows="4" class="input" placeholder="Health insurance, equity…"></textarea>
        </div>
        <div>
          <label class="label">Expires at</label>
          <input v-model="form.expires_at" type="date" class="input" />
        </div>
      </div>

      <div v-if="errors" class="text-red-600 text-sm space-y-1">
        <p v-for="(msgs, field) in errors" :key="field">{{ msgs[0] }}</p>
      </div>

      <div class="flex gap-3">
        <button type="submit" name="status" value="active" class="btn-primary" :disabled="loading">
          {{ loading ? 'Saving…' : isEdit ? 'Update Job' : 'Publish Job' }}
        </button>
        <button type="button" @click="saveDraft" class="btn-secondary" :disabled="loading">
          Save as Draft
        </button>
        <RouterLink to="/employer/jobs" class="btn-secondary">Cancel</RouterLink>
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

const form = ref({
  title: '', category: '', experience_level: 'mid', employment_type: 'full_time',
  location_type: 'remote', salary_min: null, salary_max: null, salary_period: 'annual',
  salary_visible: true, visa_sponsorship: false, open_to_international: true,
  description: '', requirements: '', benefits: '', expires_at: '', status: 'active',
})

async function submit() {
  await save('active')
}
async function saveDraft() {
  await save('draft')
}

async function save(status) {
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
    errors.value = err.response?.data?.errors ?? null
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (isEdit.value) {
    const { data } = await jobsApi.getJob(route.params.id)
    Object.assign(form.value, data.job)
  }
})
</script>
