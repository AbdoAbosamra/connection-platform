<template>
  <div class="max-w-6xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">Simple, transparent pricing</h1>
      <p class="text-gray-400 max-w-xl mx-auto">
        Choose the plan that fits your hiring needs. Switch or cancel anytime.
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
          <span v-if="p === 'annual'" class="text-[10px] text-emerald-400 ml-1">save ~17%</span>
        </button>
      </div>
    </div>

    <div v-if="loading" class="grid sm:grid-cols-3 gap-6">
      <div v-for="n in 3" :key="n" class="h-96 rounded-2xl bg-white/[0.04] animate-pulse" />
    </div>

    <div v-else class="grid sm:grid-cols-3 gap-6">
      <div
        v-for="(plan, i) in plans"
        :key="plan.id"
        class="relative rounded-2xl border p-7 flex flex-col"
        :class="i === 1 ? 'border-primary-500/60 bg-primary-500/[0.06]' : 'border-gray-800 bg-gray-900/40'"
      >
        <div
          v-if="i === 1"
          class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-[10px] font-bold uppercase tracking-wide px-3 py-1 rounded-full"
        >
          Most popular
        </div>

        <h3 class="text-lg font-bold text-white">{{ plan.name }}</h3>
        <p class="text-sm text-gray-400 mt-1 min-h-[2.5rem]">{{ plan.description }}</p>

        <div class="mt-5 mb-6">
          <span class="text-4xl font-extrabold text-white">${{ price(plan) }}</span>
          <span class="text-gray-500 text-sm">/{{ period === 'annual' ? 'yr' : 'mo' }}</span>
        </div>

        <ul class="space-y-2.5 text-sm text-gray-300 flex-1">
          <li class="flex items-center gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400" />
            {{ plan.job_posts_limit === 0 ? 'Unlimited' : plan.job_posts_limit }} job posts
          </li>
          <li v-if="plan.candidate_search" class="flex items-center gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400" /> Candidate search
          </li>
          <li v-if="plan.featured_listings" class="flex items-center gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400" /> Featured listings
          </li>
          <li v-if="plan.analytics" class="flex items-center gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400" /> Analytics dashboard
          </li>
          <li v-if="plan.priority_support" class="flex items-center gap-2">
            <CheckIcon class="w-4 h-4 text-emerald-400" /> Priority support
          </li>
        </ul>

        <RouterLink
          :to="ctaTarget"
          class="mt-7 text-center py-3 rounded-xl font-semibold transition-colors"
          :class="i === 1 ? 'bg-primary-600 text-white hover:bg-primary-500' : 'bg-white/[0.06] text-white hover:bg-white/[0.1]'"
        >
          Get started
        </RouterLink>
      </div>
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

function price(plan) {
  const cents = period.value === 'annual' ? plan.price_annual : plan.price_monthly
  return (cents / 100).toLocaleString(undefined, { maximumFractionDigits: 0 })
}

onMounted(async () => {
  try {
    const { data } = await billingApi.plans()
    plans.value = data.plans ?? []
  } finally {
    loading.value = false
  }
})
</script>
