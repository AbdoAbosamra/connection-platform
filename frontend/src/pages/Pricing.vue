<template>
  <div class="max-w-6xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">Simple, transparent pricing</h1>
      <p class="text-gray-400 max-w-xl mx-auto">
        Pay for the hiring platform — post jobs, manage candidates, and hire. Switch or cancel anytime.
      </p>

      <!-- Billing period toggle -->
      <div class="inline-flex mt-6 rounded-xl bg-gray-800/60 p-1">
        <button
          v-for="p in ['monthly', 'annual']"
          :key="p"
          class="px-4 py-1.5 text-sm font-medium rounded-lg capitalize transition-colors"
          :class="period === p ? 'bg-primary-600 text-white' : 'text-gray-400'"
          @click="period = p"
        >
          {{ p }}
          <span v-if="p === 'annual'" class="text-[10px] text-emerald-400 ml-1">2 months free</span>
        </button>
      </div>
    </div>

    <!-- Plan cards -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div v-for="n in 4" :key="n" class="h-96 rounded-2xl bg-white/[0.04] animate-pulse" />
    </div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="relative rounded-2xl border p-6 flex flex-col"
        :class="isPopular(plan) ? 'border-primary-500/60 bg-primary-500/[0.06]' : 'border-gray-800 bg-gray-900/40'"
      >
        <div
          v-if="isPopular(plan)"
          class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-[10px] font-bold uppercase tracking-wide px-3 py-1 rounded-full"
        >
          Most popular
        </div>

        <h3 class="text-lg font-bold text-white">{{ plan.name }}</h3>
        <p class="text-sm text-gray-400 mt-1 min-h-[2.5rem]">{{ plan.description }}</p>

        <div class="mt-4 mb-5">
          <span class="text-4xl font-extrabold text-white">${{ price(plan) }}</span>
          <span class="text-gray-500 text-sm">/{{ period === 'annual' ? 'yr' : 'mo' }}</span>
        </div>

        <ul class="space-y-2 text-sm text-gray-300 flex-1">
          <li v-for="feat in features(plan)" :key="feat" class="flex items-start gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" />
            {{ feat }}
          </li>
        </ul>

        <RouterLink
          :to="ctaTarget"
          class="mt-6 text-center py-3 rounded-xl font-semibold transition-colors"
          :class="isPopular(plan) ? 'bg-primary-600 text-white hover:bg-primary-500' : 'bg-white/[0.06] text-white hover:bg-white/[0.1]'"
        >
          {{ plan.price_monthly === 0 ? 'Get started free' : 'Get started' }}
        </RouterLink>
      </div>
    </div>

    <!-- Comparison table -->
    <div v-if="!loading && plans.length" class="mt-16">
      <h2 class="text-xl font-bold text-white text-center mb-6">Compare plans</h2>
      <div class="overflow-x-auto rounded-2xl border border-gray-800">
        <table class="w-full text-sm min-w-[640px]">
          <thead>
            <tr class="border-b border-gray-800 bg-gray-900/40">
              <th class="text-left font-semibold text-gray-300 px-4 py-3">Feature</th>
              <th v-for="plan in plans" :key="plan.id" class="text-center font-semibold text-white px-4 py-3">
                {{ plan.name }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in comparison" :key="row.label" class="border-b border-gray-800/60">
              <td class="text-gray-300 px-4 py-3">
                {{ row.label }}
                <span v-if="row.soon" class="ml-1.5 text-[10px] font-semibold uppercase tracking-wide text-amber-400/90 bg-amber-400/10 px-1.5 py-0.5 rounded">Coming soon</span>
              </td>
              <td v-for="plan in plans" :key="plan.id" class="text-center px-4 py-3">
                <template v-if="typeof row.value(plan) === 'boolean'">
                  <CheckIcon v-if="row.value(plan)" class="w-4 h-4 text-emerald-400 inline" />
                  <span v-else class="text-gray-600">—</span>
                </template>
                <span v-else class="text-gray-200">{{ row.value(plan) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="text-center text-xs text-gray-500 mt-4">
        Add-ons available on any plan: employer verification, featured jobs, extra job slots, and pay-per-job posting.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { CheckIcon } from '@heroicons/vue/24/solid'
import { billingApi } from '@/api/billing'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const plans = ref([])
const loading = ref(true)
const period = ref('monthly')

const ctaTarget = computed(() =>
  auth.isEmployer ? '/employer/billing' : (auth.isAuthenticated ? '/' : '/register')
)

const isPopular = (plan) => plan.slug === 'growth'

function price(plan) {
  const cents = period.value === 'annual' ? plan.price_annual : plan.price_monthly
  return (cents / 100).toLocaleString(undefined, { maximumFractionDigits: 0 })
}

const jobsLabel = (plan) => (plan.job_posts_limit === 0 ? 'Unlimited' : plan.job_posts_limit)

// Concise per-card feature bullets — only what's included.
function features(plan) {
  const list = [
    `${jobsLabel(plan)} active job${plan.job_posts_limit === 1 ? '' : 's'}`,
    'Applicant tracking (ATS)',
    'Messaging & interviews',
  ]
  if (plan.candidate_search) list.push(plan.advanced_search ? 'Advanced candidate search' : 'Candidate search')
  if (plan.analytics) list.push('Analytics dashboard')
  if (plan.featured_listings) list.push('Featured job listings')
  if (plan.international_remote) list.push('International remote hiring')
  if (plan.verification_discount) list.push('Employer verification discount')
  if (plan.priority_support) list.push('Priority support')
  return list
}

// Full comparison matrix. `soon` marks features not built yet.
const comparison = [
  { label: 'Active jobs',                 value: (p) => jobsLabel(p) },
  { label: 'Applicant Tracking System',   value: () => true },
  { label: 'Applications management',      value: () => true },
  { label: 'Messaging',                    value: () => true },
  { label: 'Interview scheduling',         value: () => true },
  { label: 'Candidate search',            value: (p) => !!p.candidate_search },
  { label: 'Advanced candidate search',   value: (p) => !!p.advanced_search },
  { label: 'Analytics',                    value: (p) => (p.analytics ? 'Advanced' : (p.candidate_search ? 'Basic' : '—')) },
  { label: 'Featured job listings',        value: (p) => !!p.featured_listings },
  { label: 'International remote hiring',   value: (p) => !!p.international_remote },
  { label: 'Employer verification discount', value: (p) => !!p.verification_discount },
  { label: 'Priority support',             value: (p) => !!p.priority_support },
  { label: 'Multiple recruiter accounts',  value: (p) => p.slug === 'scale', soon: true },
  { label: 'API access',                   value: (p) => p.slug === 'scale', soon: true },
  { label: 'SSO',                          value: (p) => p.slug === 'scale', soon: true },
]

onMounted(async () => {
  try {
    const { data } = await billingApi.plans()
    plans.value = data.plans ?? []
  } finally {
    loading.value = false
  }
})
</script>
