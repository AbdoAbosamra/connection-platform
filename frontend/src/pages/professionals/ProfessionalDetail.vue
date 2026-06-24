<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Back link -->
    <RouterLink
      to="/professionals"
      class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 font-medium mb-6 transition-colors"
    >
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
      </svg>
      {{ lang.t('professionals.backToList') }}
    </RouterLink>

    <!-- Loading skeleton -->
    <div v-if="store.loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-pulse">
      <div class="lg:col-span-2 space-y-4">
        <div class="card p-6">
          <div class="flex gap-5">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex-shrink-0" />
            <div class="flex-1 space-y-3">
              <div class="h-5 bg-gray-100 rounded-full w-1/3" />
              <div class="h-4 bg-gray-100 rounded-full w-2/3" />
              <div class="flex gap-2">
                <div class="h-5 bg-gray-100 rounded-full w-20" />
                <div class="h-5 bg-gray-100 rounded-full w-24" />
              </div>
            </div>
          </div>
        </div>
        <div class="card p-6 space-y-3">
          <div class="h-4 bg-gray-100 rounded-full w-1/4" />
          <div class="h-3 bg-gray-100 rounded-full w-full" />
          <div class="h-3 bg-gray-100 rounded-full w-5/6" />
          <div class="h-3 bg-gray-100 rounded-full w-4/6" />
        </div>
      </div>
      <div class="card p-6 space-y-3">
        <div class="h-4 bg-gray-100 rounded-full w-1/2" />
        <div class="h-3 bg-gray-100 rounded-full w-3/4" />
        <div class="h-3 bg-gray-100 rounded-full w-2/3" />
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!pro" class="card p-16 text-center">
      <p class="font-bold text-gray-900 mb-2">Profile not found</p>
      <p class="text-sm text-gray-400 mb-4">This profile may be incomplete or unavailable.</p>
      <RouterLink to="/professionals" class="btn-secondary text-sm">
        {{ lang.t('professionals.backToList') }}
      </RouterLink>
    </div>

    <!-- Profile -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- ── Main column ── -->
      <div class="lg:col-span-2 space-y-5">

        <!-- Hero card -->
        <div class="card p-6">
          <div class="flex gap-5 items-start">
            <!-- Avatar -->
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center flex-shrink-0 ring-2 ring-primary-200/50 shadow">
              <span class="text-2xl font-extrabold text-primary-600 select-none">{{ initials }}</span>
            </div>

            <!-- Identity -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <h1 class="text-2xl font-extrabold text-gray-900 truncate">{{ pro.name }}</h1>
                  <p class="text-gray-500 mt-0.5">{{ pro.headline || pro.current_job_title }}</p>
                </div>
                <span v-if="pro.is_featured" class="badge-blue flex-shrink-0 gap-1">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  Featured
                </span>
              </div>

              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 mt-3">
                <span v-if="pro.experience_level" class="badge-gray capitalize">{{ pro.experience_level }}</span>
                <span v-if="pro.years_of_experience" class="badge-gray">{{ pro.years_of_experience }}yr exp</span>
                <span v-if="locationText" class="badge-gray">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                  </svg>
                  {{ locationText }}
                </span>
                <span v-if="availabilityLabel" class="badge-blue">{{ availabilityLabel }}</span>
              </div>

              <!-- External links -->
              <div v-if="pro.portfolio_url || pro.linkedin_url || pro.github_url" class="flex gap-3 mt-4">
                <a
                  v-if="pro.portfolio_url"
                  :href="pro.portfolio_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                  </svg>
                  {{ lang.t('professionals.portfolioLink') }}
                </a>
                <a
                  v-if="pro.linkedin_url"
                  :href="pro.linkedin_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                >
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
                  {{ lang.t('professionals.linkedinLink') }}
                </a>
                <a
                  v-if="pro.github_url"
                  :href="pro.github_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-gray-900 transition-colors"
                >
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                  </svg>
                  {{ lang.t('professionals.githubLink') }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- About -->
        <div v-if="pro.bio" class="card p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-3">{{ lang.t('professionals.aboutTitle') }}</h2>
          <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ pro.bio }}</p>
        </div>

        <!-- Skills -->
        <div v-if="pro.skills?.length" class="card p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">{{ lang.t('professionals.skillsTitle') }}</h2>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="skill in pro.skills"
              :key="skill.id"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200"
            >
              <span class="text-sm font-semibold text-gray-800">{{ skill.name }}</span>
              <span
                v-if="skill.proficiency"
                :class="proficiencyClass(skill.proficiency)"
                class="text-[10px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wide"
              >
                {{ skill.proficiency }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Sidebar ── -->
      <div class="space-y-5">

        <!-- CTA -->
        <div class="card p-5 text-center space-y-2">
          <template v-if="auth.isAuthenticated && auth.user?.role === 'employer'">
            <button
              @click="startConversation"
              :disabled="initiating"
              class="btn-primary w-full !py-3 flex items-center justify-center gap-2"
            >
              <svg v-if="initiating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
              </svg>
              {{ initiating ? 'Opening chat…' : lang.t('professionals.messageBtn') }}
            </button>
            <p v-if="initiateError" class="text-xs text-rose-500">{{ initiateError }}</p>
          </template>
          <template v-else-if="!auth.isAuthenticated">
            <p class="text-sm text-gray-500 mb-3">{{ lang.t('professionals.signUpToContact') }}</p>
            <RouterLink to="/register" class="btn-primary w-full !py-3 block">
              {{ lang.t('nav.getStarted') }}
            </RouterLink>
          </template>
          <template v-else>
            <p class="text-xs text-gray-400">Employer account required to contact</p>
          </template>
        </div>

        <!-- Quick facts -->
        <div class="card p-5 space-y-4">
          <!-- Experience -->
          <div v-if="pro.experience_level || pro.years_of_experience" class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ lang.t('professionals.experienceYears') }}</p>
              <p class="text-sm font-bold text-gray-900 capitalize">
                {{ pro.experience_level }}
                <span v-if="pro.years_of_experience" class="font-normal text-gray-500">
                  · {{ pro.years_of_experience }} yrs
                </span>
              </p>
            </div>
          </div>

          <!-- Current role -->
          <div v-if="pro.current_job_title" class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ lang.t('professionals.currentRole') }}</p>
              <p class="text-sm font-bold text-gray-900">{{ pro.current_job_title }}</p>
            </div>
          </div>

          <!-- Desired role -->
          <div v-if="pro.desired_job_title" class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ lang.t('professionals.desiredRole') }}</p>
              <p class="text-sm font-bold text-gray-900">{{ pro.desired_job_title }}</p>
            </div>
          </div>

          <!-- Location -->
          <div v-if="locationText" class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ lang.t('professionals.location') }}</p>
              <p class="text-sm font-bold text-gray-900">{{ locationText }}</p>
            </div>
          </div>

          <!-- Availability -->
          <div v-if="availabilityLabel" class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ lang.t('professionals.availability') }}</p>
              <p class="text-sm font-bold text-gray-900">{{ availabilityLabel }}</p>
            </div>
          </div>
        </div>

        <!-- Profile strength -->
        <div class="card p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
            {{ lang.t('professionals.profileStrength') }}
          </p>
          <div class="flex items-center gap-3">
            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
              <div
                class="h-2 rounded-full transition-all duration-700"
                :class="completionColorClass"
                :style="{ width: `${pro.completion}%` }"
              />
            </div>
            <span class="text-sm font-bold text-gray-700 w-10 text-right">{{ pro.completion }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useProfessionalsStore } from '@/stores/professionals'
import { useLanguageStore } from '@/stores/language'
import { useAuthStore } from '@/stores/auth'
import { useMessagesStore } from '@/stores/messages'
import { messagesApi } from '@/api/messages'

const route    = useRoute()
const router   = useRouter()
const store    = useProfessionalsStore()
const lang     = useLanguageStore()
const auth     = useAuthStore()
const msgStore = useMessagesStore()

// ── Initiate conversation ──────────────────────────────────────────────────
const initiating    = ref(false)
const initiateError = ref(null)

async function startConversation() {
  if (!pro.value) return
  initiating.value    = true
  initiateError.value = null
  try {
    const { data } = await messagesApi.initiate({
      job_seeker_profile_id: pro.value.id,
    })
    // Pre-seed the store so Messages.vue opens straight to this thread
    msgStore.openToConversation(data.conversation)
    router.push('/employer/messages')
  } catch (err) {
    const d = err.response?.data
    // Surface the real server error so the user (and dev) know what went wrong
    if (d?.errors) {
      // Laravel validation error — show the first field error
      initiateError.value = Object.values(d.errors)[0]?.[0] ?? d.message
    } else if (d?.message) {
      initiateError.value = d.message
    } else if (err.response?.status) {
      initiateError.value = `Server error (${err.response.status}). Check Laravel logs.`
    } else {
      initiateError.value = 'Network error — is the backend running?'
    }
    console.error('[startConversation]', err.response?.status, d ?? err.message)
  } finally {
    initiating.value = false
  }
}

const pro = computed(() => store.currentProfile)

const initials = computed(() => {
  const name = pro.value?.name ?? '?'
  return name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
})

const locationText = computed(() => {
  const parts = [pro.value?.current_city, pro.value?.current_country].filter(Boolean)
  return parts.join(', ')
})

const availabilityMap = {
  immediate: 'Available now',
  '2_weeks':  'In 2 weeks',
  '1_month':  'In 1 month',
}

const availabilityLabel = computed(() =>
  pro.value?.availability
    ? (availabilityMap[pro.value.availability] ?? pro.value.availability)
    : null
)

const completionColorClass = computed(() => {
  const c = pro.value?.completion ?? 0
  if (c >= 80) return 'bg-emerald-500'
  if (c >= 50) return 'bg-amber-400'
  return 'bg-red-400'
})

function proficiencyClass(level) {
  const map = {
    beginner:     'bg-gray-100 text-gray-500',
    intermediate: 'bg-blue-100 text-blue-700',
    advanced:     'bg-violet-100 text-violet-700',
    expert:       'bg-amber-100 text-amber-700',
  }
  return map[level] ?? 'bg-gray-100 text-gray-500'
}

onMounted(async () => {
  try {
    await store.fetchProfile(route.params.id)
  } catch {
    // 404 handled by v-else-if="!pro" in template
  }
})

onUnmounted(() => store.clearCurrentProfile())
</script>
