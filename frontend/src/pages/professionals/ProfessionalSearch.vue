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
        <div class="card p-5 space-y-5 sticky top-20">
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

          <!-- Availability -->
          <div>
            <label class="label">{{ lang.t('professionals.availability') }}</label>
            <select v-model="filters.availability" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="immediate">{{ lang.t('professionals.availableNow') }}</option>
              <option value="2_weeks">{{ lang.t('professionals.in2Weeks') }}</option>
              <option value="1_month">{{ lang.t('professionals.in1Month') }}</option>
            </select>
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
import { reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProfessionalsStore } from '@/stores/professionals'
import { useLanguageStore } from '@/stores/language'
import ProfessionalCard from '@/components/professionals/ProfessionalCard.vue'

const route  = useRoute()
const router = useRouter()
const store  = useProfessionalsStore()
const lang   = useLanguageStore()

const filters = reactive({
  q:                route.query.q ?? '',
  experience_level: route.query.experience_level ?? '',
  availability:     route.query.availability ?? '',
  page:             Number(route.query.page) || 1,
})

function doSearch() {
  filters.page = 1
  router.replace({ query: { ...filters } })
  store.search({ ...filters })
}

function resetFilters() {
  Object.assign(filters, { q: '', experience_level: '', availability: '', page: 1 })
  doSearch()
}

function goToPage(page) {
  filters.page = page
  router.replace({ query: { ...filters } })
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
