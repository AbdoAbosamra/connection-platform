<template>
  <div class="max-w-3xl space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-gray-900">My Profile</h1>
        <p class="text-gray-500 text-sm mt-0.5">Keep your profile up to date to attract employers</p>
      </div>
      <div v-if="profile" class="flex items-center gap-3">
        <div class="text-right">
          <p class="text-xs text-gray-400 font-medium">Profile strength</p>
          <p class="text-sm font-bold" :class="profile.completion >= 80 ? 'text-emerald-600' : profile.completion >= 50 ? 'text-amber-600' : 'text-rose-500'">
            {{ profile.completion }}% Complete
          </p>
        </div>
        <div class="w-10 h-10 relative">
          <svg class="w-10 h-10 -rotate-90" viewBox="0 0 36 36">
            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="3"/>
            <circle cx="18" cy="18" r="15.9" fill="none"
              :stroke="profile.completion >= 80 ? '#10b981' : profile.completion >= 50 ? '#f59e0b' : '#f43f5e'"
              stroke-width="3" stroke-linecap="round"
              :stroke-dasharray="`${profile.completion} 100`"
              style="transition:stroke-dasharray 0.5s ease"/>
          </svg>
        </div>
      </div>
    </div>

    <form @submit.prevent="save" class="space-y-5">
      <!-- Basic Info -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </span>
          Basic Info
        </h2>
        <div>
          <label class="label">Headline</label>
          <input v-model="form.headline" class="input" placeholder="Senior Laravel Developer" />
        </div>
        <div>
          <label class="label">Bio</label>
          <textarea v-model="form.bio" rows="4" class="input resize-none" placeholder="Tell employers about yourself…" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">City</label>
            <input v-model="form.current_city" class="input" placeholder="Manila" />
          </div>
          <div>
            <label class="label">Country</label>
            <input v-model="form.current_country" class="input" placeholder="Philippines" />
          </div>
        </div>
      </div>

      <!-- Experience -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
          </span>
          Experience
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Current title</label>
            <input v-model="form.current_job_title" class="input" placeholder="Software Engineer" />
          </div>
          <div>
            <label class="label">Desired title</label>
            <input v-model="form.desired_job_title" class="input" placeholder="Senior Engineer" />
          </div>
          <div>
            <label class="label">Experience level</label>
            <select v-model="form.experience_level" class="input bg-gray-50 cursor-pointer">
              <option value="entry">Entry level</option>
              <option value="mid">Mid level</option>
              <option value="senior">Senior level</option>
              <option value="lead">Lead</option>
              <option value="executive">Executive</option>
            </select>
          </div>
          <div>
            <label class="label">Years of experience</label>
            <input v-model.number="form.years_of_experience" type="number" min="0" class="input" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Desired salary min (USD)</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
              <input v-model.number="form.desired_salary_min" type="number" min="0" class="input !pl-7" placeholder="60,000" />
            </div>
          </div>
          <div>
            <label class="label">Desired salary max (USD)</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
              <input
                v-model.number="form.desired_salary_max"
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
        </div>

        <div>
          <label class="label">Availability</label>
          <select v-model="form.availability" class="input bg-gray-50 cursor-pointer">
            <option value="immediately">Immediately</option>
            <option value="two_weeks">2 weeks notice</option>
            <option value="one_month">1 month notice</option>
            <option value="negotiable">Negotiable</option>
          </select>
        </div>

      </div>

      <!-- Skills -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .586 2.698-.75 3.098C14.988 20.297 13.5 20.625 12 20.625c-1.5 0-2.988-.328-4.452-.825-1.336-.4-1.75-2.098-.75-3.098L8.2 15.3" />
            </svg>
          </span>
          Skills
        </h2>

        <!-- Selected skills tags -->
        <div v-if="selectedSkills.length" class="flex flex-wrap gap-2">
          <div
            v-for="skill in selectedSkills"
            :key="skill.id"
            class="flex items-center gap-1.5 bg-primary-50 border border-primary-200 rounded-xl px-3 py-1.5"
          >
            <span class="text-sm font-medium text-primary-800">{{ skill.name }}</span>
            <select
              v-model="skill.proficiency"
              class="text-xs border-0 bg-transparent text-primary-600 font-medium focus:ring-0 cursor-pointer pr-5 pl-0 py-0"
            >
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
              <option value="expert">Expert</option>
            </select>
            <button
              type="button"
              @click="removeSkill(skill.id)"
              class="ml-0.5 text-primary-400 hover:text-rose-500 transition-colors"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400">No skills added yet. Search below to add skills.</p>

        <!-- Search input -->
        <div class="relative">
          <input
            v-model="skillSearch"
            @input="onSkillInput"
            @focus="showSuggestions = true"
            @blur="onSkillBlur"
            type="text"
            class="input"
            placeholder="Search skills (e.g. Vue.js, Laravel, Python…)"
            autocomplete="off"
          />
          <!-- Suggestions dropdown -->
          <ul
            v-if="showSuggestions && skillSuggestions.length"
            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-52 overflow-y-auto"
          >
            <li
              v-for="s in skillSuggestions"
              :key="s.id"
              @mousedown.prevent="addSkill(s)"
              class="px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 cursor-pointer flex items-center justify-between"
            >
              <span>{{ s.name }}</span>
              <span v-if="s.category" class="text-xs text-gray-400">{{ s.category }}</span>
            </li>
          </ul>
          <p v-if="showSuggestions && skillSearch && !skillSuggestions.length && !loadingSkills" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 text-sm text-gray-400">
            No skills found for "{{ skillSearch }}"
          </p>
        </div>
      </div>

      <!-- Links -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-violet-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
          </span>
          Links & Portfolio
        </h2>
        <div>
          <label class="label">LinkedIn</label>
          <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/in/yourname" />
        </div>
        <div>
          <label class="label">GitHub</label>
          <input v-model="form.github_url" type="url" class="input" placeholder="https://github.com/yourname" />
        </div>
        <div>
          <label class="label">Portfolio</label>
          <input v-model="form.portfolio_url" type="url" class="input" placeholder="https://yourportfolio.com" />
        </div>
      </div>

      <!-- Resume -->
      <div class="card p-6 space-y-4">
        <h2 class="font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
          <span class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
          </span>
          Resume
        </h2>
        <div v-if="profile?.resume" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
          <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-emerald-700 font-medium">Resume uploaded ·
            <a :href="`/storage/${profile.resume}`" target="_blank" class="underline hover:no-underline">View current</a>
          </p>
        </div>
        <div>
          <label class="label">Upload new resume <span class="text-gray-400 font-normal">(PDF/DOCX, max 10 MB)</span></label>
          <input @change="onResume" type="file" accept=".pdf,.doc,.docx"
            class="input cursor-pointer file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import client from '@/api/client'

const profile      = ref(null)
const saving       = ref(false)
const success      = ref(false)
const fieldErrors  = ref(null)
const generalError = ref('')
const resume       = ref(null)

// Skills state
const selectedSkills   = ref([])   // [{ id, name, proficiency }]
const skillSearch      = ref('')
const skillSuggestions = ref([])
const showSuggestions  = ref(false)
const loadingSkills    = ref(false)
let   skillSearchTimer = null

const form = ref({
  headline: '', bio: '', current_city: '', current_country: '', nationality: '',
  current_job_title: '', desired_job_title: '', experience_level: 'mid',
  years_of_experience: 0, desired_salary_min: null, desired_salary_max: null,
  availability: 'negotiable',
  linkedin_url: '', github_url: '', portfolio_url: '',
})

// Live salary range guard — shown inline, also blocks submit
const salaryRangeError = computed(() => {
  const min = Number(form.value.desired_salary_min) || 0
  const max = Number(form.value.desired_salary_max) || 0
  if (min > 0 && max > 0 && max < min) {
    return `Max salary must be at least $${min.toLocaleString()}`
  }
  return null
})

const MAX_RESUME_MB   = 10
const MAX_RESUME_BYTES = MAX_RESUME_MB * 1024 * 1024
const ALLOWED_TYPES   = ['application/pdf', 'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document']

function onResume(e) {
  const file = e.target.files[0]
  if (!file) return

  // Type guard — catch files the OS labelled incorrectly
  const ext = file.name.split('.').pop().toLowerCase()
  if (!['pdf', 'doc', 'docx'].includes(ext) && !ALLOWED_TYPES.includes(file.type)) {
    generalError.value = 'Only PDF and Word documents are accepted.'
    e.target.value = ''
    return
  }

  // Size guard — block before the request even starts
  if (file.size > MAX_RESUME_BYTES) {
    generalError.value = `Resume must be under ${MAX_RESUME_MB} MB. Your file is ${(file.size / 1024 / 1024).toFixed(1)} MB.`
    e.target.value = ''
    resume.value = null
    return
  }

  generalError.value = ''
  resume.value = file
}

// ── Skills helpers ─────────────────────────────────────────────────────────

function onSkillInput() {
  clearTimeout(skillSearchTimer)
  if (!skillSearch.value.trim()) { skillSuggestions.value = []; return }
  skillSearchTimer = setTimeout(fetchSkills, 300)
}

async function fetchSkills() {
  loadingSkills.value = true
  try {
    const { data } = await client.get('/skills', { params: { search: skillSearch.value } })
    const alreadyAdded = new Set(selectedSkills.value.map(s => s.id))
    skillSuggestions.value = data.skills.filter(s => !alreadyAdded.has(s.id))
  } catch {
    skillSuggestions.value = []
  } finally {
    loadingSkills.value = false
  }
}

function addSkill(skill) {
  selectedSkills.value.push({ id: skill.id, name: skill.name, proficiency: 'intermediate' })
  skillSearch.value      = ''
  skillSuggestions.value = []
  showSuggestions.value  = false
}

function removeSkill(id) {
  selectedSkills.value = selectedSkills.value.filter(s => s.id !== id)
}

function onSkillBlur() {
  // Short delay so mousedown on suggestion fires first
  setTimeout(() => { showSuggestions.value = false }, 150)
}

// ── Save ───────────────────────────────────────────────────────────────────

// Salary fields where 0 and "" both mean "not provided" — never send them to the backend
const SALARY_FIELDS = new Set(['desired_salary_min', 'desired_salary_max'])

function isSalaryEmpty(v) {
  return v === null || v === undefined || v === '' || v === 0
}

async function save() {
  // Block submit if the salary range is logically invalid
  if (salaryRangeError.value) {
    fieldErrors.value = { desired_salary_max: [salaryRangeError.value] }
    return
  }

  saving.value      = true
  success.value     = false
  fieldErrors.value = null
  generalError.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
      // Salary fields: treat 0 / "" / null as "absent" so the backend nullable rule
      // skips the gte comparison instead of comparing 0 against the min.
      if (SALARY_FIELDS.has(k)) {
        if (!isSalaryEmpty(v)) fd.append(k, v)
        return
      }
      if (v !== null && v !== undefined) fd.append(k, v)
    })
    if (resume.value) fd.append('resume', resume.value)
    // Append skills as skills[0][id], skills[0][proficiency], ...
    selectedSkills.value.forEach((skill, i) => {
      fd.append(`skills[${i}][id]`, skill.id)
      fd.append(`skills[${i}][proficiency]`, skill.proficiency)
    })
    fd.append('_method', 'PUT')
    const { data } = await client.post('/job-seeker/profile', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    profile.value = data.profile
    success.value = true
    // Sync selected skills from saved profile
    if (data.profile.skills) {
      selectedSkills.value = data.profile.skills.map(s => ({
        id: s.id, name: s.name, proficiency: s.pivot?.proficiency ?? 'intermediate',
      }))
    }
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

// ── Load ───────────────────────────────────────────────────────────────────

onMounted(async () => {
  try {
    const { data } = await client.get('/job-seeker/profile')
    profile.value  = data.profile
    Object.assign(form.value, data.profile)
    if (data.profile.skills) {
      selectedSkills.value = data.profile.skills.map(s => ({
        id: s.id, name: s.name, proficiency: s.pivot?.proficiency ?? 'intermediate',
      }))
    }
  } catch {
    generalError.value = 'Failed to load profile. Please refresh the page.'
  }
})

onUnmounted(() => {
  clearTimeout(skillSearchTimer)
})
</script>
