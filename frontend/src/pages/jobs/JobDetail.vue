<template>
  <!-- Loading skeleton -->
  <div v-if="store.loading" class="max-w-5xl mx-auto px-4 py-10 animate-pulse space-y-4">
    <div class="h-10 bg-gray-200 rounded-2xl w-2/3" />
    <div class="h-4 bg-gray-200 rounded-full w-1/3" />
    <div class="h-48 bg-gray-200 rounded-2xl mt-6" />
  </div>

  <div v-else-if="job" class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- ── Main content ── -->
      <div class="flex-1 space-y-5">
        <!-- Header card -->
        <div class="card p-6 md:p-8">
          <div class="flex items-start gap-5">
            <!-- Company logo -->
            <div class="w-18 h-18 w-[72px] h-[72px] rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 ring-1 ring-gray-200 shadow-sm">
              <img v-if="job.employer?.logo" :src="`/storage/${job.employer.logo}`" class="w-full h-full object-contain" />
              <span v-else class="text-2xl font-extrabold text-gray-300">{{ job.employer?.company_name?.[0] }}</span>
            </div>

            <div class="flex-1 min-w-0">
              <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-1">{{ job.title }}</h1>
              <p class="text-gray-500 font-medium mb-4">{{ job.employer?.company_name }}</p>

              <div class="flex flex-wrap gap-2">
                <span class="badge-green">🌐 Remote</span>
                <span class="badge-gray">{{ employmentMap[job.employment_type] }}</span>
                <span class="badge-gray capitalize">{{ job.experience_level }}</span>
              </div>
            </div>
          </div>

          <!-- Meta row -->
          <div class="border-t border-gray-100 mt-6 pt-5 flex flex-wrap gap-6">
            <div v-if="job.salary_visible && job.salary_min">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Salary</p>
              <p class="font-bold text-gray-900">
                ${{ job.salary_min.toLocaleString() }}
                {{ job.salary_max ? `– $${job.salary_max.toLocaleString()}` : '+' }}
                <span class="text-gray-400 font-normal text-sm">/ {{ job.salary_period }}</span>
              </p>
            </div>
            <div v-if="job.location_city">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Location</p>
              <p class="font-bold text-gray-900">{{ job.location_city }}, {{ job.location_state }}</p>
            </div>
          </div>

          <!-- CTA buttons -->
          <div class="mt-6 flex flex-wrap gap-3">
            <button v-if="auth.isJobSeeker" @click="showApplyModal = true" class="btn-primary !px-8 !py-3 text-base">
              Apply Now
            </button>
            <RouterLink v-else-if="!auth.isAuthenticated" to="/register?role=job_seeker" class="btn-primary !px-8 !py-3 text-base">
              Sign up to Apply
            </RouterLink>
            <button v-if="auth.isJobSeeker" @click="jobsStore.toggleSave(job.id)" class="btn-secondary !px-6 !py-3">
              <svg class="w-4 h-4" :class="isSaved ? 'fill-current text-primary-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
              </svg>
              {{ isSaved ? 'Saved' : 'Save Job' }}
            </button>
            <button
              v-if="auth.isAuthenticated"
              class="btn-secondary !px-4 !py-3 text-gray-400 hover:text-rose-600"
              title="Report this job"
              @click="showReportModal = true"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" />
              </svg>
              Report
            </button>
          </div>
        </div>

        <!-- Description -->
        <div class="card p-6 md:p-8">
          <h2 class="section-title mb-5">About this role</h2>
          <div class="whitespace-pre-line text-gray-700 leading-relaxed">{{ job.description }}</div>
        </div>

        <div v-if="job.requirements" class="card p-6 md:p-8">
          <h2 class="section-title mb-5">Requirements</h2>
          <div class="whitespace-pre-line text-gray-700 leading-relaxed">{{ job.requirements }}</div>
        </div>

        <div v-if="job.benefits" class="card p-6 md:p-8">
          <h2 class="section-title mb-5">Benefits & Perks</h2>
          <div class="whitespace-pre-line text-gray-700 leading-relaxed">{{ job.benefits }}</div>
        </div>
      </div>

      <!-- ── Sidebar ── -->
      <aside class="w-full lg:w-72 space-y-4">
        <!-- Company info -->
        <div class="card p-5">
          <h3 class="font-bold text-gray-900 mb-3">About {{ job.employer?.company_name }}</h3>
          <p class="text-sm text-gray-500 leading-relaxed line-clamp-4">{{ job.employer?.description }}</p>

          <dl class="mt-4 space-y-3">
            <div v-if="job.employer?.company_size" class="flex justify-between text-sm">
              <dt class="text-gray-400 font-medium">Company size</dt>
              <dd class="font-semibold text-gray-900">{{ job.employer.company_size }} employees</dd>
            </div>
            <div v-if="job.employer?.founded_year" class="flex justify-between text-sm">
              <dt class="text-gray-400 font-medium">Founded</dt>
              <dd class="font-semibold text-gray-900">{{ job.employer.founded_year }}</dd>
            </div>
            <div v-if="job.employer?.headquarters_city" class="flex justify-between text-sm">
              <dt class="text-gray-400 font-medium">HQ</dt>
              <dd class="font-semibold text-gray-900">{{ job.employer.headquarters_city }}, {{ job.employer.headquarters_state }}</dd>
            </div>
          </dl>

          <a
            v-if="job.employer?.website"
            :href="job.employer.website"
            target="_blank"
            class="mt-5 flex items-center justify-center gap-1.5 w-full btn-secondary text-sm !py-2.5"
          >
            Visit website
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
          </a>
        </div>

        <!-- Skills -->
        <div v-if="job.skills?.length" class="card p-5">
          <h3 class="font-bold text-gray-900 mb-3">Required Skills</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="skill in job.skills"
              :key="skill.id"
              :class="skill.pivot?.is_required ? 'badge-blue' : 'badge-gray'"
            >
              {{ skill.name }}
            </span>
          </div>
        </div>
      </aside>
    </div>
  </div>

  <!-- Apply Modal -->
  <ApplyModal
    v-if="showApplyModal && job"
    :job="job"
    @close="showApplyModal = false"
    @applied="showApplyModal = false"
  />

  <!-- Report Modal -->
  <ReportModal v-if="job" v-model="showReportModal" type="job" :id="job.id" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useJobsStore } from '@/stores/jobs'
import { useAuthStore } from '@/stores/auth'
import ApplyModal from '@/components/jobs/ApplyModal.vue'
import ReportModal from '@/components/ReportModal.vue'

const route     = useRoute()
const store     = useJobsStore()
const auth      = useAuthStore()
const jobsStore = useJobsStore()

const showApplyModal = ref(false)
const showReportModal = ref(false)
const job     = computed(() => store.currentJob)
const isSaved = computed(() => store.savedIds.has(job.value?.id))

const employmentMap = { full_time: 'Full-time', part_time: 'Part-time', contract: 'Contract', freelance: 'Freelance', internship: 'Internship' }

onMounted(() => store.fetchJob(route.params.slug))
</script>
