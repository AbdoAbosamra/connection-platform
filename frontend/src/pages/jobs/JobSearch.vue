<template>
  <div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Filters sidebar -->
      <aside class="w-full lg:w-72 flex-shrink-0">
        <div class="card p-5 space-y-5 sticky top-20">
          <h2 class="font-semibold text-gray-900">Filters</h2>

          <div>
            <label class="label">Keyword</label>
            <input v-model="filters.q" type="text" class="input" placeholder="React, Laravel…" @keydown.enter="doSearch" />
          </div>

          <div>
            <label class="label">Location type</label>
            <select v-model="filters.location_type" class="input">
              <option value="">Any</option>
              <option value="remote">Remote</option>
              <option value="hybrid">Hybrid</option>
              <option value="on_site">On-site</option>
            </select>
          </div>

          <div>
            <label class="label">Experience level</label>
            <select v-model="filters.experience_level" class="input">
              <option value="">Any</option>
              <option value="entry">Entry</option>
              <option value="mid">Mid</option>
              <option value="senior">Senior</option>
              <option value="lead">Lead</option>
              <option value="executive">Executive</option>
            </select>
          </div>

          <div>
            <label class="label">Employment type</label>
            <select v-model="filters.employment_type" class="input">
              <option value="">Any</option>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="filters.visa_sponsorship" type="checkbox" id="visa" class="rounded text-primary-600" />
            <label for="visa" class="text-sm text-gray-700">Visa sponsorship</label>
          </div>

          <button @click="doSearch" class="btn-primary w-full">Apply filters</button>
          <button @click="resetFilters" class="btn-secondary w-full text-xs">Reset</button>
        </div>
      </aside>

      <!-- Results -->
      <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
          <p class="text-sm text-gray-500">
            <template v-if="!store.loading">
              {{ store.pagination?.total ?? 0 }} jobs found
            </template>
            <template v-else>Searching…</template>
          </p>
        </div>

        <div v-if="store.loading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="card p-5 animate-pulse h-32 bg-gray-100" />
        </div>

        <div v-else-if="store.jobs.length === 0" class="card p-10 text-center text-gray-400">
          No jobs found. Try adjusting your filters.
        </div>

        <div v-else class="space-y-4">
          <JobCard v-for="job in store.jobs" :key="job.id" :job="job" />
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1" class="mt-8 flex justify-center gap-2">
          <button
            v-for="page in store.pagination.last_page" :key="page"
            @click="goToPage(page)"
            :class="['px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors',
              page === store.pagination.current_page
                ? 'bg-primary-600 text-white border-primary-600'
                : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50']"
          >{{ page }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJobsStore } from '@/stores/jobs'
import JobCard from '@/components/jobs/JobCard.vue'

const route  = useRoute()
const router = useRouter()
const store  = useJobsStore()

const filters = reactive({
  q: route.query.q ?? '',
  category: route.query.category ?? '',
  location_type: route.query.location_type ?? '',
  experience_level: route.query.experience_level ?? '',
  employment_type: '',
  visa_sponsorship: false,
  page: 1,
})

function doSearch() {
  filters.page = 1
  router.replace({ query: { ...filters } })
  store.search({ ...filters })
}

function resetFilters() {
  Object.assign(filters, { q: '', category: '', location_type: '', experience_level: '', employment_type: '', visa_sponsorship: false, page: 1 })
  doSearch()
}

function goToPage(page) {
  filters.page = page
  store.search({ ...filters })
  window.scrollTo({ top: 0 })
}

onMounted(() => store.search({ ...filters }))
</script>
