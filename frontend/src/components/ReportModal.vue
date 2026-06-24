<template>
  <transition
    enter-active-class="transition ease-out duration-150"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="close"
    >
      <div class="w-full max-w-md rounded-2xl bg-gray-900 border border-gray-800 p-6" role="dialog" aria-modal="true">
        <h3 class="text-lg font-bold text-white mb-1">Report this {{ type }}</h3>
        <p class="text-sm text-gray-400 mb-5">Help us keep the platform safe. Our team reviews every report.</p>

        <div v-if="done" class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-4 text-sm text-emerald-300">
          Thanks — your report has been submitted for review.
          <button class="block mt-3 text-white underline" @click="close">Close</button>
        </div>

        <form v-else class="space-y-4" @submit.prevent="submit">
          <div>
            <label class="label" for="report-reason">Reason</label>
            <select id="report-reason" v-model="reason" class="input" required>
              <optgroup label="Professional Conduct">
                <option value="harassment">Harassment or intimidation</option>
                <option value="hate_speech">Hate speech or discrimination</option>
                <option value="threats">Threats or abusive behavior</option>
              </optgroup>
              <optgroup label="Honest Representation">
                <option value="fake_profile">Fake profile / impersonation</option>
                <option value="resume_fraud">Resume or credential fraud</option>
                <option value="fake_listing">Fake job listing</option>
                <option value="misleading_compensation">Misleading compensation</option>
              </optgroup>
              <optgroup label="Fraud Prevention">
                <option value="application_fee">Requests application/interview fees</option>
                <option value="job_selling">Selling access to jobs</option>
                <option value="pyramid_scheme">Pyramid scheme or MLM</option>
                <option value="phishing">Phishing / data harvesting</option>
                <option value="money_laundering">Money laundering</option>
              </optgroup>
              <optgroup label="Other">
                <option value="spam">Spam</option>
                <option value="scam">Scam / fraud</option>
                <option value="inappropriate">Inappropriate content</option>
                <option value="other">Other</option>
              </optgroup>
            </select>
          </div>
          <div>
            <label class="label" for="report-details">Details <span class="text-gray-600">(optional)</span></label>
            <textarea
              id="report-details"
              v-model="details"
              rows="3"
              class="input"
              placeholder="Add any context that helps us review this report…"
              maxlength="2000"
            />
          </div>

          <p v-if="error" class="text-sm text-rose-400">{{ error }}</p>

          <div class="flex justify-end gap-2 pt-1">
            <button type="button" class="px-4 py-2 text-sm rounded-xl text-gray-400 hover:text-white" @click="close">
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm rounded-xl bg-rose-600 text-white hover:bg-rose-500 disabled:opacity-50"
              :disabled="submitting"
            >
              {{ submitting ? 'Submitting…' : 'Submit report' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import { reportsApi } from '@/api/reports'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  type: { type: String, required: true, validator: (v) => ['job', 'user'].includes(v) },
  id: { type: [Number, String], required: true },
})
const emit = defineEmits(['update:modelValue'])

const reason = ref('spam')
const details = ref('')
const submitting = ref(false)
const done = ref(false)
const error = ref('')

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      reason.value = 'spam'
      details.value = ''
      done.value = false
      error.value = ''
    }
  }
)

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  submitting.value = true
  error.value = ''
  try {
    await reportsApi.create({ type: props.type, id: props.id, reason: reason.value, details: details.value || undefined })
    done.value = true
  } catch (err) {
    error.value =
      Object.values(err.response?.data?.errors ?? {})[0]?.[0] ??
      err.response?.data?.message ??
      'Could not submit your report. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>
