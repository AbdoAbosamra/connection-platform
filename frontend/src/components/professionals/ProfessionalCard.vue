<template>
  <RouterLink
    :to="`/professionals/${pro.id}`"
    class="group bg-white rounded-2xl border border-gray-100 shadow-card p-5 flex gap-4 hover:shadow-lifted hover:-translate-y-0.5 hover:border-primary-100 transition-all duration-300 block"
  >
    <!-- Avatar -->
    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center flex-shrink-0 ring-1 ring-primary-200/60 shadow-sm">
      <span class="text-xl font-extrabold text-primary-600 select-none">
        {{ initials }}
      </span>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <!-- Title row -->
      <div class="flex items-start justify-between gap-2 mb-1">
        <div class="min-w-0">
          <h3 class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors truncate">
            {{ pro.name }}
          </h3>
          <p class="text-sm text-gray-500 mt-0.5 truncate">
            {{ pro.headline || pro.current_job_title || '—' }}
          </p>
        </div>
        <span v-if="pro.is_featured" class="badge-blue flex-shrink-0 gap-1">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          Featured
        </span>
      </div>

      <!-- Meta badges -->
      <div class="flex flex-wrap gap-1.5 mt-2.5">
        <span v-if="pro.experience_level" class="badge-gray capitalize">{{ pro.experience_level }}</span>
        <span v-if="pro.years_of_experience" class="badge-gray">{{ pro.years_of_experience }}yr exp</span>
        <span v-if="locationText" class="badge-gray">{{ locationText }}</span>
        <span v-if="availabilityLabel" class="badge-blue">{{ availabilityLabel }}</span>
      </div>

      <!-- Skills -->
      <div v-if="visibleSkills.length" class="flex flex-wrap gap-1.5 mt-2">
        <span
          v-for="skill in visibleSkills"
          :key="skill.id"
          class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-50 border border-gray-200 text-xs font-medium text-gray-600"
        >
          {{ skill.name }}
        </span>
        <span v-if="pro.skills.length > MAX_SKILLS" class="text-xs text-gray-400 self-center">
          +{{ pro.skills.length - MAX_SKILLS }} more
        </span>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  pro: { type: Object, required: true },
})

const MAX_SKILLS = 4

const initials = computed(() => {
  const name = props.pro.name ?? '?'
  return name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
})

const locationText = computed(() => {
  const parts = [props.pro.current_city, props.pro.current_country].filter(Boolean)
  return parts.join(', ')
})

const availabilityMap = {
  immediate: 'Available now',
  '2_weeks':  'In 2 weeks',
  '1_month':  'In 1 month',
}

const availabilityLabel = computed(() =>
  props.pro.availability ? (availabilityMap[props.pro.availability] ?? props.pro.availability) : null
)

const visibleSkills = computed(() => (props.pro.skills ?? []).slice(0, MAX_SKILLS))
</script>
