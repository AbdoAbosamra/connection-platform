<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page header -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-900 mb-1">{{ lang.t('professionals.pageTitle') }}</h1>
      <p class="text-gray-500">{{ lang.t('professionals.pageSubtitle') }}</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
      <!-- ── Filters sidebar ── -->
      <aside class="w-full lg:w-72 flex-shrink-0">
        <div class="card p-5 space-y-5 sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto">
          <!-- Header -->
          <div class="flex items-center justify-between">
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
              <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
              </svg>
              {{ lang.t('professionals.filtersTitle') }}
            </h2>
            <button @click="resetFilters" class="text-xs text-primary-600 hover:text-primary-700 font-semibold">
              {{ lang.t('professionals.resetAll') }}
            </button>
          </div>

          <!-- Keyword -->
          <div>
            <label class="label">{{ lang.t('professionals.keyword') }}</label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
              </svg>
              <input
                v-model="filters.q"
                type="text"
                class="input !pl-9 !py-2.5"
                :placeholder="lang.t('professionals.keywordPlaceholder')"
                @keydown.enter="doSearch"
              />
            </div>
          </div>

          <!-- ══ BASIC FILTERS ══ -->
          <!-- Skills typeahead -->
          <div>
            <label class="label">Skills</label>
            <div class="relative">
              <input
                v-model="skillQuery"
                type="text"
                class="input !py-2.5"
                placeholder="React, Figma, SQL…"
                @input="onSkillInput"
                @focus="onSkillInput"
              />
              <ul v-if="skillOptions.length" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto py-1">
                <li
                  v-for="opt in skillOptions" :key="opt.id"
                  @click="addSkill(opt)"
                  class="px-3 py-1.5 text-sm text-gray-700 hover:bg-primary-50 cursor-pointer"
                >{{ opt.name }}</li>
              </ul>
            </div>
            <div v-if="selectedSkills.length" class="flex flex-wrap gap-1.5 mt-2">
              <span v-for="s in selectedSkills" :key="s.id" class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-semibold px-2 py-1 rounded-lg">
                {{ s.name }}
                <button @click="removeSkill(s.id)" class="text-primary-400 hover:text-primary-700">&times;</button>
              </span>
            </div>
          </div>

          <!-- Experience level -->
          <div>
            <label class="label">{{ lang.t('professionals.experienceLevel') }}</label>
            <select v-model="filters.experience_level" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="entry">Entry level</option>
              <option value="mid">Mid level</option>
              <option value="senior">Senior level</option>
              <option value="lead">Lead</option>
              <option value="executive">Executive</option>
            </select>
          </div>

          <!-- Industry -->
          <div>
            <label class="label">Industry</label>
            <input v-model="filters.industry" class="input !py-2.5" placeholder="Fintech, Healthcare…" @keydown.enter="doSearch" />
          </div>

          <!-- Education -->
          <div>
            <label class="label">Education</label>
            <select v-model="filters.education_level" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="high_school">High school</option>
              <option value="associate">Associate</option>
              <option value="bachelor">Bachelor's</option>
              <option value="master">Master's</option>
              <option value="doctorate">Doctorate</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Salary range -->
          <div>
            <label class="label">Salary range (USD)</label>
            <div class="grid grid-cols-2 gap-2">
              <input v-model.number="filters.salary_min" type="number" min="0" class="input !py-2.5" placeholder="Min" />
              <input v-model.number="filters.salary_max" type="number" min="0" class="input !py-2.5" placeholder="Max" />
            </div>
          </div>

          <!-- Availability -->
          <div>
            <label class="label">{{ lang.t('professionals.availability') }}</label>
            <select v-model="filters.availability" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="immediately">{{ lang.t('professionals.availableNow') }}</option>
              <option value="two_weeks">{{ lang.t('professionals.in2Weeks') }}</option>
              <option value="one_month">{{ lang.t('professionals.in1Month') }}</option>
            </select>
          </div>

          <!-- Remote experience -->
          <div>
            <label class="label">Min. remote experience (years)</label>
            <input v-model.number="filters.remote_experience_min" type="number" min="0" max="50" class="input !py-2.5" placeholder="Any" />
          </div>

          <!-- ══ ADVANCED / INTERNATIONAL ══ -->
          <div class="pt-1 border-t border-gray-100">
            <label class="flex items-center justify-between gap-2 cursor-pointer py-2 group">
              <span class="flex items-center gap-1.5 text-sm font-semibold text-gray-800">
                🌍 International Remote hiring
              </span>
              <span class="relative inline-block w-9 h-5 flex-shrink-0">
                <input v-model="intlHiring" type="checkbox" class="sr-only peer" @change="onToggleIntl" />
                <span class="absolute inset-0 rounded-full bg-gray-200 peer-checked:bg-primary-500 transition-colors" />
                <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4" />
              </span>
            </label>
            <p class="text-xs text-gray-400 -mt-1 mb-1">Enable to filter international candidates.</p>
          </div>

          <div v-if="intlHiring" class="space-y-4 animate-fade-in rounded-xl bg-slate-50 border border-slate-200 p-4">
            <p class="text-[11px] uppercase tracking-wide font-semibold text-slate-500">Advanced filters</p>

            <!-- Country -->
            <div>
              <label class="label">Country</label>
              <input v-model="filters.country" class="input !py-2.5 bg-white" placeholder="Egypt, Brazil…" @keydown.enter="doSearch" />
            </div>

            <!-- Time zone -->
            <div>
              <label class="label">Time zone</label>
              <input v-model="filters.time_zone" class="input !py-2.5 bg-white" placeholder="UTC+2" @keydown.enter="doSearch" />
            </div>

            <!-- Languages -->
            <div>
              <label class="label">Languages</label>
              <input v-model="filters.languages" class="input !py-2.5 bg-white" placeholder="English, French" @keydown.enter="doSearch" />
              <p class="text-xs text-gray-400 mt-1">Comma-separated. Candidate must speak all.</p>
            </div>

            <!-- Contract type -->
            <div>
              <label class="label">Contract type</label>
              <select v-model="filters.contract_type" class="input !py-2.5 bg-white cursor-pointer">
                <option value="">Any</option>
                <option value="contractor">Contractor</option>
                <option value="remote_employee">Remote employee</option>
              </select>
            </div>

            <!-- Boolean toggles -->
            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-gray-700">
              <input v-model="filters.has_portfolio" type="checkbox" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300" />
              Has portfolio
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-gray-700">
              <input v-model="filters.has_certifications" type="checkbox" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300" />
              Has certifications
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-gray-700">
              <input v-model="filters.has_security_clearance" type="checkbox" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300" />
              Security clearance <span class="text-gray-400">(optional)</span>
            </label>
          </div>

          <button @click="doSearch" class="btn-primary w-full !py-3">
            {{ lang.t('professionals.applyFilters') }}
          </button>
        </div>
      </aside>

      <!-- ── Results ── -->
      <div class="flex-1 min-w-0">
        <!-- Results count -->
        <div class="flex items-center justify-between mb-5">
          <p class="text-sm text-gray-500">
            <template v-if="!store.loading">
              <span class="font-bold text-gray-900">
                {{ (store.pagination?.total ?? 0).toLocaleString() }}
              </span>
              {{ lang.t('professionals.found') }}
            </template>
            <template v-else>
              <span class="inline-flex items-center gap-1.5">
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ lang.t('professionals.searching') }}
              </span>
            </template>
          </p>
        </div>

        <!-- Skeleton loading -->
        <div v-if="store.loading" class="space-y-4">
          <div v-for="i in 6" :key="i" class="card p-5 animate-pulse">
            <div class="flex gap-4">
              <div class="w-14 h-14 rounded-2xl bg-gray-100 flex-shrink-0" />
              <div class="flex-1 space-y-2.5">
                <div class="h-4 bg-gray-100 rounded-full w-1/2" />
                <div class="h-3 bg-gray-100 rounded-full w-3/4" />
                <div class="flex gap-2 mt-3">
                  <div class="h-5 bg-gray-100 rounded-full w-16" />
                  <div class="h-5 bg-gray-100 rounded-full w-20" />
                  <div class="h-5 bg-gray-100 rounded-full w-14" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="store.professionals.length === 0" class="card p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
          </div>
          <p class="font-bold text-gray-900 mb-1">{{ lang.t('professionals.notFound') }}</p>
          <p class="text-sm text-gray-400">{{ lang.t('professionals.notFoundHint') }}</p>
          <button @click="resetFilters" class="btn-secondary mt-4 text-sm">
            {{ lang.t('professionals.resetFilters') }}
          </button>
        </div>

        <!-- Professionals list -->
        <div v-else class="space-y-4">
          <ProfessionalCard
            v-for="pro in store.professionals"
            :key="pro.id"
            :pro="pro"
          />
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1" class="mt-8 flex justify-center items-center gap-2">
          <button
            v-for="page in pageRange"
            :key="page"
            @click="page !== '…' && goToPage(page)"
            :disabled="page === '…'"
            :class="[
              'min-w-[36px] h-9 rounded-xl text-sm font-semibold border transition-all duration-200 active:scale-95',
              page === store.pagination.current_page
                ? 'bg-gradient-to-r from-primary-600 to-violet-600 text-white border-transparent shadow-lg shadow-primary-500/30'
                : page === '…'
                  ? 'bg-transparent border-transparent text-gray-400 cursor-default'
                  : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
            ]"
          >{{ page }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProfessionalsStore } from '@/stores/professionals'
import { useLanguageStore } from '@/stores/language'
import { professionalsApi } from '@/api/professionals'
import ProfessionalCard from '@/components/professionals/ProfessionalCard.vue'

const route  = useRoute()
const router = useRouter()
const store  = useProfessionalsStore()
const lang   = useLanguageStore()

const emptyFilters = () => ({
  q: '', experience_level: '', availability: '', industry: '', education_level: '',
  salary_min: null, salary_max: null, remote_experience_min: null, skills: '',
  // advanced (international)
  country: '', time_zone: '', languages: '', contract_type: '',
  has_portfolio: false, has_certifications: false, has_security_clearance: false,
  page: 1,
})

const filters = reactive({
  ...emptyFilters(),
  q:                route.query.q ?? '',
  experience_level: route.query.experience_level ?? '',
  availability:     route.query.availability ?? '',
  page:             Number(route.query.page) || 1,
})

// ── International toggle ──────────────────────────────────────────────
const intlHiring = ref(false)

// Clear the advanced filters when international hiring is switched off, so
// they never silently apply while hidden.
function onToggleIntl() {
  if (!intlHiring.value) {
    Object.assign(filters, {
      country: '', time_zone: '', languages: '', contract_type: '',
      has_portfolio: false, has_certifications: false, has_security_clearance: false,
    })
  }
}

// ── Skills typeahead ──────────────────────────────────────────────────
const skillQuery     = ref('')
const skillOptions   = ref([])
const selectedSkills = ref([])
let skillTimer = null

function onSkillInput() {
  clearTimeout(skillTimer)
  const term = skillQuery.value.trim()
  skillTimer = setTimeout(async () => {
    try {
      const { data } = await professionalsApi.skills(term)
      const chosen = new Set(selectedSkills.value.map(s => s.id))
      skillOptions.value = (data.skills ?? []).filter(s => !chosen.has(s.id)).slice(0, 8)
    } catch {
      skillOptions.value = []
    }
  }, 200)
}

function addSkill(opt) {
  selectedSkills.value.push(opt)
  filters.skills = selectedSkills.value.map(s => s.id).join(',')
  skillQuery.value = ''
  skillOptions.value = []
}

function removeSkill(id) {
  selectedSkills.value = selectedSkills.value.filter(s => s.id !== id)
  filters.skills = selectedSkills.value.map(s => s.id).join(',')
}

function doSearch() {
  filters.page = 1
  router.replace({ query: cleanQuery() })
  store.search({ ...filters })
}

// Only reflect the non-empty filters in the URL to keep it readable.
function cleanQuery() {
  return Object.fromEntries(
    Object.entries(filters).filter(([, v]) => v !== '' && v !== false && v !== null && v !== undefined)
  )
}

function resetFilters() {
  Object.assign(filters, emptyFilters())
  selectedSkills.value = []
  skillQuery.value = ''
  intlHiring.value = false
  doSearch()
}

function goToPage(page) {
  filters.page = page
  router.replace({ query: cleanQuery() })
  store.search({ ...filters })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const pageRange = computed(() => {
  const cur  = store.pagination?.current_page ?? 1
  const last = store.pagination?.last_page ?? 1
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  const pages = [1]
  if (cur > 3) pages.push('…')
  for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
  if (cur < last - 2) pages.push('…')
  pages.push(last)
  return pages
})

onMounted(() => store.search({ ...filters }))
</script>
