<template>
  <div class="max-w-3xl space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Company Profile</h1>
      <p class="text-gray-500 text-sm mt-0.5">Help candidates learn more about your company</p>
    </div>

    <form @submit.prevent="save" class="space-y-5">
      <!-- Company Info -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
          </span>
          Company Info
        </h2>
        <div>
          <label class="label">Company name <span class="text-rose-500">*</span></label>
          <input v-model="form.company_name" class="input" required />
        </div>
        <div>
          <label class="label">Description</label>
          <textarea v-model="form.description" rows="4" class="input resize-none" placeholder="Tell job seekers about your company, culture, and mission…" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Industry</label>
            <input v-model="form.industry" class="input" placeholder="Technology" />
          </div>
          <div>
            <label class="label">Company size</label>
            <select v-model="form.company_size" class="input bg-gray-50 cursor-pointer">
              <option value="1-10">1–10 employees</option>
              <option value="11-50">11–50 employees</option>
              <option value="51-200">51–200 employees</option>
              <option value="201-500">201–500 employees</option>
              <option value="501-1000">501–1000 employees</option>
              <option value="1000+">1000+ employees</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Website</label>
            <input v-model="form.website" type="url" class="input" placeholder="https://yourcompany.com" />
          </div>
          <div>
            <label class="label">Founded year</label>
            <input v-model.number="form.founded_year" type="number" min="1800" :max="new Date().getFullYear()" class="input" placeholder="2015" />
          </div>
        </div>
      </div>

      <!-- Headquarters -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
          </span>
          Headquarters
        </h2>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="label">City</label>
            <input v-model="form.headquarters_city" class="input" placeholder="San Francisco" />
          </div>
          <div>
            <label class="label">State</label>
            <input v-model="form.headquarters_state" class="input" placeholder="CA" />
          </div>
          <div>
            <label class="label">Country</label>
            <input v-model="form.headquarters_country" class="input" placeholder="USA" />
          </div>
        </div>
      </div>

      <!-- Social Links -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-violet-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
          </span>
          Social Links
        </h2>
        <div>
          <label class="label">LinkedIn</label>
          <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/company/yourcompany" />
        </div>
        <div>
          <label class="label">Twitter / X</label>
          <input v-model="form.twitter_url" type="url" class="input" placeholder="https://twitter.com/yourcompany" />
        </div>
      </div>

      <!-- Company Logo -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
          </span>
          Company Logo
        </h2>
        <div v-if="profile?.logo" class="flex items-center gap-4 bg-gray-50 rounded-xl p-4">
          <img :src="`/storage/${profile.logo}`" class="w-16 h-16 object-contain rounded-xl ring-1 ring-gray-200" />
          <div>
            <p class="text-sm font-semibold text-gray-900">Current logo</p>
            <p class="text-xs text-gray-400 mt-0.5">Upload a new image to replace it</p>
          </div>
        </div>
        <div>
          <label class="label">Upload logo <span class="text-gray-400 font-normal">(PNG/JPG, max 2MB)</span></label>
          <input
            @change="onLogo"
            type="file"
            accept="image/*"
            class="input cursor-pointer file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
          />
        </div>
      </div>

      <!-- Feedback -->
      <div v-if="success" class="flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 animate-fade-in">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        Profile updated successfully.
      </div>
      <div v-if="fieldErrors" class="bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1 animate-fade-in">
        <p v-for="(msgs, f) in fieldErrors" :key="f" class="text-sm text-rose-700">{{ msgs[0] }}</p>
      </div>
      <div v-if="generalError" class="flex items-start gap-2.5 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700 animate-fade-in">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        {{ generalError }}
      </div>

      <button type="submit" class="btn-primary !px-10 !py-3 text-base" :disabled="saving">
        <svg v-if="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ saving ? 'Saving…' : 'Save Profile' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import client from '@/api/client'

const profile      = ref(null)
const saving       = ref(false)
const success      = ref(false)
const fieldErrors  = ref(null)
const generalError = ref('')
const logo         = ref(null)

const form = ref({
  company_name: '', description: '', industry: '', company_size: '',
  website: '', founded_year: null, headquarters_city: '', headquarters_state: '',
  headquarters_country: '', linkedin_url: '', twitter_url: '',
})

function onLogo(e) { logo.value = e.target.files[0] }

async function save() {
  saving.value       = true
  success.value      = false
  fieldErrors.value  = null
  generalError.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => { if (v !== null && v !== undefined && v !== '') fd.append(k, v) })
    if (logo.value) fd.append('logo', logo.value)
    fd.append('_method', 'PUT')
    const { data } = await client.post('/employer/profile', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    profile.value  = data.profile
    success.value  = true
  } catch (err) {
    const status = err.response?.status
    if (status === 422) {
      fieldErrors.value = err.response.data?.errors ?? null
    } else if (err.response?.data?.message) {
      generalError.value = err.response.data.message
    } else if (!err.response) {
      generalError.value = 'Cannot reach the server. Please check your connection.'
    } else {
      generalError.value = 'Failed to save profile. Please try again.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const { data } = await client.get('/employer/profile')
  profile.value  = data.profile
  Object.assign(form.value, data.profile)
})
</script>
