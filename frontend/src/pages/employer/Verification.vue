<template>
  <div class="max-w-2xl">
    <h2 class="text-xl font-bold text-white mb-2">Company Verification</h2>
    <p class="text-gray-400 text-sm mb-6">
      To keep RemoteArena safe for job seekers, employers must verify their company
      before posting jobs. Companies with a corporate email are verified automatically.
    </p>

    <!-- Verified state -->
    <div
      v-if="status.is_verified"
      class="rounded-2xl border border-emerald-500/30 bg-emerald-500/[0.07] p-6 flex items-center gap-4"
    >
      <CheckBadgeIcon class="w-10 h-10 text-emerald-400 flex-shrink-0" />
      <div>
        <p class="font-semibold text-white">Your company is verified</p>
        <p class="text-sm text-gray-400">
          Verified via {{ methodLabel(status.method) }}. You can post jobs freely.
        </p>
      </div>
    </div>

    <!-- Unverified state -->
    <template v-else>
      <div class="rounded-2xl border border-amber-500/30 bg-amber-500/[0.07] p-4 mb-6 flex items-start gap-3">
        <ExclamationTriangleIcon class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" />
        <p class="text-sm text-amber-200">
          Your account uses a personal email, so it isn't verified yet. Choose one of the
          options below — it only takes a moment.
        </p>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <!-- LinkedIn -->
        <div class="rounded-2xl border border-gray-800 bg-gray-900/40 p-6 flex flex-col">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-6 h-6 text-[#0A66C2]" fill="currentColor" viewBox="0 0 24 24"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.07 2.07 0 110-4.13 2.07 2.07 0 010 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z" /></svg>
            <h3 class="font-semibold text-white">Verify with LinkedIn</h3>
          </div>
          <p class="text-sm text-gray-400 flex-1">Confirm your professional identity in seconds.</p>
          <button
            class="mt-4 py-2.5 rounded-xl font-semibold text-sm bg-[#0A66C2] text-white hover:bg-[#0958a8] disabled:opacity-50"
            :disabled="!status.methods_available?.linkedin || working"
            @click="verifyLinkedIn"
          >
            {{ status.methods_available?.linkedin ? 'Continue with LinkedIn' : 'LinkedIn unavailable' }}
          </button>
        </div>

        <!-- Payment -->
        <div class="rounded-2xl border border-gray-800 bg-gray-900/40 p-6 flex flex-col">
          <div class="flex items-center gap-2 mb-2">
            <CreditCardIcon class="w-6 h-6 text-primary-400" />
            <h3 class="font-semibold text-white">Verify with a card</h3>
          </div>
          <p class="text-sm text-gray-400 flex-1">A small authorization confirms a valid payment method.</p>
          <button
            class="mt-4 py-2.5 rounded-xl font-semibold text-sm bg-primary-600 text-white hover:bg-primary-500 disabled:opacity-50"
            :disabled="working"
            @click="verifyPayment"
          >
            {{ working ? 'Processing…' : 'Verify with payment' }}
          </button>
        </div>
      </div>
    </template>

    <p v-if="message" class="mt-5 text-sm text-emerald-400">{{ message }}</p>
    <p v-if="error" class="mt-5 text-sm text-rose-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { CheckBadgeIcon, ExclamationTriangleIcon, CreditCardIcon } from '@heroicons/vue/24/outline'
import { verificationApi } from '@/api/verification'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()
const status = ref({ is_verified: false, methods_available: {} })
const working = ref(false)
const message = ref('')
const error = ref('')

function methodLabel(m) {
  return { domain: 'a corporate email', linkedin: 'LinkedIn', payment: 'a payment authorization' }[m] ?? m
}

async function load() {
  try {
    const { data } = await verificationApi.status()
    status.value = data
    // Keep the cached auth user in sync so banners/badges update immediately.
    if (auth.user?.employer_profile) auth.user.employer_profile.is_verified = data.is_verified
  } catch {
    /* ignore */
  }
}

async function verifyLinkedIn() {
  working.value = true
  error.value = ''
  try {
    const { data } = await verificationApi.linkedInUrl()
    window.location.href = data.url
  } catch (err) {
    error.value = err.response?.data?.message ?? 'LinkedIn verification is unavailable right now.'
    working.value = false
  }
}

async function verifyPayment() {
  working.value = true
  error.value = ''
  message.value = ''
  try {
    const { data } = await verificationApi.payment()
    if (data.checkout_url) {
      window.location.href = data.checkout_url
      return
    }
    message.value = data.message ?? 'Your company is now verified.'
    await load()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Verification failed. Please try again.'
  } finally {
    working.value = false
  }
}

onMounted(async () => {
  await load()
  // Handle return from LinkedIn/Stripe redirect (?status=verified|failed|cancelled).
  const s = route.query.status
  if (s === 'verified') message.value = 'Your company has been verified!'
  else if (s === 'failed') error.value = 'Verification could not be completed. Please try again.'
  else if (s === 'cancelled') error.value = 'Verification was cancelled.'
})
</script>
