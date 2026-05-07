<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Apply — {{ job.title }}</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">Cover letter <span class="text-gray-400 font-normal">(optional)</span></label>
          <textarea v-model="form.cover_letter" rows="5" class="input" placeholder="Introduce yourself and why you're a great fit…"></textarea>
        </div>

        <div>
          <label class="label">Resume <span class="text-gray-400 font-normal">(PDF / DOCX, max 5MB)</span></label>
          <input @change="onFile" type="file" accept=".pdf,.doc,.docx" class="input" />
        </div>

        <div>
          <label class="label">Expected salary (USD / year) <span class="text-gray-400 font-normal">(optional)</span></label>
          <input v-model.number="form.expected_salary" type="number" min="0" class="input" placeholder="e.g. 80000" />
        </div>

        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <p v-if="success" class="text-green-600 text-sm">✓ Application submitted successfully!</p>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="$emit('close')" class="btn-secondary">Cancel</button>
          <button type="submit" class="btn-primary" :disabled="loading || success">
            {{ loading ? 'Submitting…' : 'Submit Application' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { jobsApi } from '@/api/jobs'

const props  = defineProps({ job: { type: Object, required: true } })
const emit   = defineEmits(['close', 'applied'])

const loading = ref(false)
const error   = ref('')
const success = ref(false)
const form    = ref({ cover_letter: '', expected_salary: null })
const file    = ref(null)

function onFile(e) { file.value = e.target.files[0] }

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    const fd = new FormData()
    if (form.value.cover_letter) fd.append('cover_letter', form.value.cover_letter)
    if (form.value.expected_salary) fd.append('expected_salary', form.value.expected_salary)
    if (file.value) fd.append('resume', file.value)

    await jobsApi.apply(props.job.id, fd)
    success.value = true
    setTimeout(() => emit('applied'), 1500)
  } catch (err) {
    error.value = err.response?.data?.errors?.job_id?.[0]
      ?? err.response?.data?.message
      ?? 'Failed to submit application.'
  } finally {
    loading.value = false
  }
}
</script>
