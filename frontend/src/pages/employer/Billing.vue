<template>
  <div class="max-w-4xl">
    <h2 class="text-xl font-bold text-white mb-6">Billing &amp; Subscription</h2>

    <!-- Current state -->
    <div class="rounded-2xl border border-gray-800 bg-gray-900/40 p-6 mb-8">
      <div v-if="loading" class="h-16 rounded-lg bg-white/[0.04] animate-pulse" />
      <template v-else>
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <p class="text-sm text-gray-400">Current plan</p>
            <p class="text-2xl font-bold text-white capitalize">{{ tier }}</p>
            <p class="text-sm text-gray-500 mt-1">
              {{ tier === 'free'
                ? `${credits} job post credit${credits === 1 ? '' : 's'} remaining`
                : 'Unlimited job posts' }}
            </p>
          </div>
          <button
            v-if="current && current.status !== 'cancelled'"
            class="px-4 py-2 text-sm rounded-xl border border-rose-500/40 text-rose-300 hover:bg-rose-500/10"
            :disabled="working"
            @click="cancel"
          >
            Cancel subscription
          </button>
        </div>
      </template>
    </div>

    <!-- Plans -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-white">Available plans</h3>
      <div class="inline-flex rounded-xl bg-gray-800/60 p-1">
        <button
          v-for="p in ['monthly', 'annual']"
          :key="p"
          class="px-3 py-1 text-xs font-medium rounded-lg capitalize"
          :class="period === p ? 'bg-primary-600 text-white' : 'text-gray-400'"
          @click="period = p"
        >
          {{ p }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="grid sm:grid-cols-3 gap-4">
      <div v-for="n in 3" :key="n" class="h-72 rounded-2xl bg-white/[0.04] animate-pulse" />
    </div>

    <div v-else class="grid sm:grid-cols-3 gap-4">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="rounded-2xl border border-gray-800 bg-gray-900/40 p-5 flex flex-col"
      >
        <h4 class="font-bold text-white">{{ plan.name }}</h4>
        <div class="my-3">
          <span class="text-3xl font-extrabold text-white">${{ price(plan) }}</span>
          <span class="text-gray-500 text-sm">/{{ period === 'annual' ? 'yr' : 'mo' }}</span>
        </div>
        <p class="text-sm text-gray-400 flex-1">
          {{ plan.job_posts_limit === 0 ? 'Unlimited' : plan.job_posts_limit }} job posts
        </p>
        <button
          class="mt-4 py-2.5 rounded-xl font-semibold text-sm bg-primary-600 text-white hover:bg-primary-500 disabled:opacity-50"
          :disabled="working || isCurrent(plan)"
          @click="subscribe(plan)"
        >
          {{ isCurrent(plan) ? 'Current plan' : 'Subscribe' }}
        </button>
      </div>
    </div>

    <p v-if="message" class="mt-5 text-sm text-emerald-400">{{ message }}</p>
    <p v-if="error" class="mt-5 text-sm text-rose-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { billingApi } from '@/api/billing'

const plans = ref([])
const current = ref(null)
const tier = ref('free')
const credits = ref(0)
const period = ref('monthly')
const loading = ref(true)
const working = ref(false)
const message = ref('')
const error = ref('')

const currentPlanId = computed(() => current.value?.subscription_plan_id ?? current.value?.plan?.id ?? null)

function isCurrent(plan) {
  return currentPlanId.value === plan.id && current.value?.status !== 'cancelled'
}

function price(plan) {
  const cents = period.value === 'annual' ? plan.price_annual : plan.price_monthly
  return (cents / 100).toLocaleString(undefined, { maximumFractionDigits: 0 })
}

async function load() {
  loading.value = true
  try {
    const [plansRes, currentRes] = await Promise.all([
      billingApi.plans().catch(() => ({ data: { plans: [] } })),
      billingApi.current().catch(() => ({ data: {} })),
    ])
    plans.value = plansRes.data.plans ?? []
    current.value = currentRes.data.subscription ?? null
    tier.value = currentRes.data.tier ?? 'free'
    credits.value = currentRes.data.job_post_credits ?? 0
  } finally {
    loading.value = false
  }
}

async function subscribe(plan) {
  working.value = true
  message.value = ''
  error.value = ''
  try {
    const { data } = await billingApi.subscribe(plan.id, period.value)
    // Stripe gateway returns a hosted checkout URL; mock gateway returns null.
    if (data.checkout_url) {
      window.location.href = data.checkout_url
      return
    }
    message.value = `You're now subscribed to ${plan.name}.`
    await load()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Subscription failed. Please try again.'
  } finally {
    working.value = false
  }
}

async function cancel() {
  if (!confirm('Cancel your subscription? You will drop back to the free tier.')) return
  working.value = true
  message.value = ''
  error.value = ''
  try {
    await billingApi.cancel()
    message.value = 'Your subscription has been cancelled.'
    await load()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Could not cancel subscription.'
  } finally {
    working.value = false
  }
}

onMounted(load)
</script>
