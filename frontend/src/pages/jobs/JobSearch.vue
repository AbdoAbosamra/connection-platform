<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page header -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-900 mb-1">Find Your Next Role</h1>
      <p class="text-gray-500">Browse remote and hybrid opportunities from top US companies</p>
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
              Filters
            </h2>
            <button @click="resetFilters" class="text-xs text-primary-600 hover:text-primary-700 font-semibold">
              Reset all
            </button>
          </div>

          <!-- Keyword -->
          <div>
            <label class="label">Keyword</label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
              </svg>
              <input v-model="filters.q" type="text" class="input !pl-9 !py-2.5" placeholder="React, Laravel…" @keydown.enter="doSearch" />
            </div>
          </div>

          <!-- Location type -->
          <div>
            <label class="label">Location type</label>
            <select v-model="filters.location_type" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="remote">🏠 Remote</option>
              <option value="hybrid">🏢 Hybrid</option>
              <option value="on_site">📍 On-site</option>
            </select>
          </div>

          <!-- Experience level -->
          <div>
            <label class="label">Experience level</label>
            <select v-model="filters.experience_level" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="entry">Entry level</option>
              <option value="mid">Mid level</option>
              <option value="senior">Senior level</option>
              <option value="lead">Lead</option>
              <option value="executive">Executive</option>
            </select>
          </div>

          <!-- Employment type -->
          <div>
            <label class="label">Employment type</label>
            <select v-model="filters.employment_type" class="input !py-2.5 bg-gray-50 cursor-pointer">
              <option value="">Any</option>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>

          <!-- Visa sponsorship -->
          <label class="flex items-center gap-3 cursor-pointer group">
            <div class="relative">
              <input v-model="filters.visa_sponsorship" type="checkbox" id="visa" class="sr-only peer" />
              <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600 shadow-inner" />
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Visa sponsorship</span>
          </label>

          <button @click="doSearch" class="btn-primary w-full !py-3">
            Apply Filters
          </button>
        </div>
      </aside>

      <!-- ── Results ── -->
      <div class="flex-1 min-w-0">
        <!-- Results count -->
        <div class="flex items-center justify-between mb-5">
          <p class="text-sm text-gray-500">
            <template v-if="!store.loading">
              <span class="font-bold text-gray-900">{{ (store.pagination?.total ?? 0).toLocaleString() }}</span>
              jobs found
            </template>
            <template v-else>
              <span class="inline-flex items-center gap-1.5">
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Searching…
              </span>
            </template>
          </p>
        </div>

        <!-- Skeleton loading -->
        <div v-if="store.loading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="card p-5 animate-pulse">
            <div class="flex gap-4">
              <div class="w-14 h-14 rounded-2xl bg-gray-100 flex-shrink-0" />
              <div class="flex-1 space-y-2.5">
                <div class="h-4 bg-gray-100 rounded-full w-3/4" />
                <div class="h-3 bg-gray-100 rounded-full w-1/2" />
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
        <div v-else-if="store.jobs.length === 0" class="card p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
          </div>
          <p class="font-bold text-gray-900 mb-1">No jobs found</p>
          <p class="text-sm text-gray-400">Try adjusting or resetting your filters</p>
          <button @click="resetFilters" class="btn-secondary mt-4 text-sm">Reset Filters</button>
        </div>

        <!-- Job list -->
        <div v-else class="space-y-4">
          <JobCard v-for="job in store.jobs" :key="job.id" :job="job" />
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1" class="mt-8 flex justify-center items-center gap-2">
          <button
            v-for="page in store.pagination.last_page"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'min-w-[36px] h-9 rounded-xl text-sm font-semibold border transition-all duration-200 active:scale-95',
              page === store.pagination.current_page
                ? 'bg-gradient-to-r from-primary-600 to-violet-600 text-white border-transparent shadow-lg shadow-primary-500/30'
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
            ]"
          >{{ page }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJobsStore } from '@/stores/jobs'
import JobCard from '@/components/jobs/JobCard.vue'

const route  = useRoute()
const router = useRouter()
const store  = useJobsStore()

const filters = reactive({
  q:                route.query.q ?? '',
  category:         route.query.category ?? '',
  location_type:    route.query.location_type ?? '',
  experience_level: route.query.experience_level ?? '',
  employment_type:  '',
  visa_sponsorship: false,
  page:             1,
})

function doSearch() {
  filters.page = 1
  router.replace({ query: { ...filters } })
  store.search({ ...filters })
}

function resetFilters() {
  Object.assign(filters, {
    q: '', category: '', location_type: '', experience_level: '',
    employment_type: '', visa_sponsorship: false, page: 1,
  })
  doSearch()
}

function goToPage(page) {
  filters.page = page
  store.search({ ...filters })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => store.search({ ...filters }))
</script>
