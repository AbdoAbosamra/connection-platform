<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
    <!-- Modal -->
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up">
      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Apply for position</h2>
          <p class="text-sm text-primary-600 font-medium mt-0.5">{{ job.title }}</p>
        </div>
        <button
          @click="$emit('close')"
          class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-all"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <form @submit.prevent="submit" class="p-6 space-y-5">
        <!-- Cover letter -->
        <div>
          <label class="label">
            Cover letter
            <span class="text-gray-400 font-normal ml-1">(optional)</span>
          </label>
          <textarea
            v-model="form.cover_letter"
            rows="5"
            class="input resize-none"
            placeholder="Introduce yourself and explain why you're a great fit for this role…"
          />
        </div>

        <!-- Resume -->
        <div>
          <label class="label">
            Resume
            <span class="text-gray-400 font-normal ml-1">(PDF / DOCX, max 5MB)</span>
          </label>
          <div class="relative">
            <input
              @change="onFile"
              type="file"
              accept=".pdf,.doc,.docx"
              class="input cursor-pointer file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
            />
          </div>
        </div>

        <!-- Expected salary -->
        <div>
          <label class="label">
            Expected salary (USD / year)
            <span class="text-gray-400 font-normal ml-1">(optional)</span>
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">$</span>
            <input
              v-model.number="form.expected_salary"
              type="number"
              min="0"
              class="input !pl-7"
              placeholder="80,000"
            />
          </div>
        </div>

        <!-- Feedback messages -->
        <div v-if="error" class="flex items-center gap-2.5 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700 animate-fade-in">
          <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
          </svg>
          {{ error }}
        </div>

        <div v-if="success" class="flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 animate-fade-in">
          <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
          </svg>
          Application submitted successfully!
        </div>

        <!-- Actions -->
        <div class="flex gap-3 justify-end pt-1">
          <button type="button" @click="$emit('close')" class="btn-secondary !px-6">
            Cancel
          </button>
          <button type="submit" class="btn-primary !px-6" :disabled="loading || success">
            <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
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

const props = defineProps({ job: { type: Object, required: true } })
const emit  = defineEmits(['close', 'applied'])

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
    if (form.value.cover_letter)   fd.append('cover_letter',    form.value.cover_letter)
    if (form.value.expected_salary) fd.append('expected_salary', form.value.expected_salary)
    if (file.value)                 fd.append('resume',          file.value)
    await jobsApi.apply(props.job.id, fd)
    success.value = true
    setTimeout(() => emit('applied'), 1500)
  } catch (err) {
    error.value = Object.values(err.response?.data?.errors ?? {})[0]?.[0]
      ?? err.response?.data?.message
      ?? 'Failed to submit application.'
  } finally {
    loading.value = false
  }
}
</script>
