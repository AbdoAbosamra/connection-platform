<template>
  <RouterLink
    :to="`/jobs/${job.slug}`"
    class="group bg-white rounded-2xl border border-gray-100 shadow-card p-5 flex gap-4 hover:shadow-lifted hover:-translate-y-0.5 hover:border-primary-100 transition-all duration-300 block"
  >
    <!-- Company logo -->
    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden ring-1 ring-gray-200/80 shadow-sm">
      <img
        v-if="job.employer?.logo"
        :src="`/storage/${job.employer.logo}`"
        :alt="job.employer.company_name"
        class="w-full h-full object-contain"
      />
      <span v-else class="text-xl font-extrabold text-gray-300">
        {{ job.employer?.company_name?.[0] }}
      </span>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <!-- Title row -->
      <div class="flex items-start justify-between gap-2 mb-1">
        <div class="min-w-0">
          <h3 class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors truncate">
            {{ job.title }}
          </h3>
          <p class="text-sm text-gray-500 mt-0.5">{{ job.employer?.company_name }}</p>
        </div>
        <span v-if="job.is_featured" class="badge-blue flex-shrink-0 gap-1">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          Featured
        </span>
      </div>

      <!-- Badges -->
      <div class="flex flex-wrap gap-1.5 mt-2.5">
        <span class="badge-green">🌐 Remote</span>
        <span class="badge-gray">{{ employmentLabel }}</span>
        <span class="badge-gray capitalize">{{ job.experience_level }}</span>
      </div>

      <!-- Footer row -->
      <div class="flex items-center justify-between mt-4 pt-3.5 border-t border-gray-50">
        <p v-if="job.salary_visible && job.salary_min" class="text-sm font-bold text-gray-900">
          ${{ formatSalary(job.salary_min) }}
          <span v-if="job.salary_max">– ${{ formatSalary(job.salary_max) }}</span>
          <span v-else>+</span>
          <span class="text-gray-400 font-normal text-xs ml-1">/ {{ job.salary_period }}</span>
        </p>
        <p v-else class="text-sm text-gray-400 italic">Salary not disclosed</p>

        <span class="text-xs text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full font-medium">
          {{ timeAgo(job.created_at) }}
        </span>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({ job: { type: Object, required: true } })

const employmentMap = { full_time: 'Full-time', part_time: 'Part-time', contract: 'Contract', freelance: 'Freelance', internship: 'Internship' }

const employmentLabel = computed(() => employmentMap[props.job.employment_type] ?? props.job.employment_type)

function formatSalary(v) {
  return v >= 1000 ? `${Math.round(v / 1000)}k` : v
}

function timeAgo(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 86400000)
  if (diff === 0) return 'Today'
  if (diff === 1) return 'Yesterday'
  if (diff < 30)  return `${diff}d ago`
  return `${Math.floor(diff / 30)}mo ago`
}
</script>
