<template>
  <!-- ── Hero ──────────────────────────────────────────────────── -->
  <section class="relative overflow-hidden min-h-[85vh] flex items-center">
    <!-- Layered background -->
    <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-primary-950 to-violet-950" />
    <div class="absolute inset-0 opacity-30"
      style="background-image:radial-gradient(circle at 20% 60%, rgba(99,102,241,0.6) 0%,transparent 55%),
                               radial-gradient(circle at 80% 20%, rgba(139,92,246,0.5) 0%,transparent 55%),
                               radial-gradient(circle at 60% 80%, rgba(79,70,229,0.4) 0%,transparent 50%)" />
    <!-- Grid texture -->
    <div class="absolute inset-0 opacity-[0.03]"
      style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:40px 40px" />
    <!-- Floating orbs -->
    <div class="absolute top-16 right-16 w-80 h-80 bg-primary-500/10 rounded-full blur-3xl animate-pulse-slow pointer-events-none" />
    <div class="absolute bottom-16 left-16 w-60 h-60 bg-violet-500/15 rounded-full blur-2xl pointer-events-none" />

    <div class="relative w-full max-w-6xl mx-auto px-4 py-32 text-center">
      <!-- Badge pill -->
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold px-4 py-2 rounded-full border border-white/20 mb-8 shadow-lg">
        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse flex-shrink-0" />
        {{ lang.t('hero.badge') }}
      </div>

      <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-[1.08] mb-6 tracking-tight">
        {{ lang.t('hero.title1') }}<br />
        <span class="bg-gradient-to-r from-primary-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">
          {{ lang.t('hero.title2') }}
        </span>
      </h1>
      <p class="text-xl text-white/60 mb-12 max-w-2xl mx-auto leading-relaxed">
        {{ lang.t('hero.desc') }}
      </p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink
          to="/jobs"
          class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-9 py-4 rounded-2xl hover:bg-primary-50 transition-all duration-200 shadow-2xl shadow-black/30 text-base active:scale-95"
        >
          {{ lang.t('hero.browseJobs') }}
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </RouterLink>
        <RouterLink
          to="/register?role=job_seeker"
          class="inline-flex items-center justify-center gap-2 border-2 border-white/30 text-white font-semibold px-9 py-4 rounded-2xl hover:bg-white/10 backdrop-blur-sm transition-all duration-200 text-base active:scale-95"
        >
          {{ lang.t('hero.createProfile') }}
        </RouterLink>
      </div>

      <!-- Trust badges -->
      <div class="mt-14 flex flex-wrap justify-center gap-x-8 gap-y-3 text-white/40 text-xs font-medium">
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> {{ lang.t('hero.freeForSeekers') }}</span>
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> {{ lang.t('hero.countries') }}</span>
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> {{ lang.t('hero.remoteRoles') }}</span>
      </div>
    </div>
  </section>

  <!-- ── Quick search ───────────────────────────────────────────── -->
  <section class="max-w-4xl mx-auto px-4 -mt-10 relative z-10">
    <div class="bg-white rounded-3xl shadow-2xl shadow-gray-400/20 border border-gray-100 p-5 flex flex-col sm:flex-row gap-3">
      <div class="flex-1 relative">
        <svg class="absolute start-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
        </svg>
        <input
          v-model="query"
          type="text"
          :placeholder="lang.t('search.placeholder')"
          class="input !ps-10 !py-3.5"
          @keydown.enter="search"
        />
      </div>
      <!-- Remote-only badge instead of location type select -->
      <div class="sm:w-48 flex items-center gap-2 px-4 py-3.5 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-700 text-sm font-semibold flex-shrink-0">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        🌐 {{ lang.t('jobs.remoteOnly') }}
      </div>
      <button @click="search" class="btn-primary !py-3.5 !px-8 text-base whitespace-nowrap">
        {{ lang.t('search.searchJobs') }}
      </button>
    </div>
  </section>

  <!-- ── How it works ─────────────────────────────────────────── -->
  <section class="max-w-7xl mx-auto px-4 py-20">
    <div class="text-center mb-12">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-500 mb-3">Simple &amp; Fast</span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">How RemoteArena works</h2>
      <p class="text-gray-500 text-lg max-w-lg mx-auto">Three steps to your next remote opportunity — or your next great hire.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
      <!-- connector line (desktop only) -->
      <div class="hidden md:block absolute top-10 left-1/3 right-1/3 h-px bg-gradient-to-r from-primary-200 via-violet-200 to-primary-200 pointer-events-none" />

      <!-- Step 1 -->
      <div class="relative bg-white rounded-2xl border border-gray-100 shadow-card p-8 flex flex-col items-center text-center group hover:shadow-lifted hover:-translate-y-1 transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-primary-50 flex items-center justify-center mb-5 ring-4 ring-white shadow-sm group-hover:bg-primary-100 transition-colors">
          <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </div>
        <div class="absolute -top-3 -right-3 w-7 h-7 rounded-full bg-primary-600 text-white text-xs font-bold flex items-center justify-center shadow-md">1</div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Build your profile</h3>
        <p class="text-sm text-gray-500 leading-relaxed">Create a standout profile showcasing your skills, experience, and the kind of remote work you're looking for.</p>
      </div>

      <!-- Step 2 -->
      <div class="relative bg-white rounded-2xl border border-gray-100 shadow-card p-8 flex flex-col items-center text-center group hover:shadow-lifted hover:-translate-y-1 transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-violet-50 flex items-center justify-center mb-5 ring-4 ring-white shadow-sm group-hover:bg-violet-100 transition-colors">
          <svg class="w-7 h-7 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
          </svg>
        </div>
        <div class="absolute -top-3 -right-3 w-7 h-7 rounded-full bg-violet-600 text-white text-xs font-bold flex items-center justify-center shadow-md">2</div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Discover opportunities</h3>
        <p class="text-sm text-gray-500 leading-relaxed">Browse hundreds of remote positions across engineering, design, marketing, and more — filtered to match your goals.</p>
      </div>

      <!-- Step 3 -->
      <div class="relative bg-white rounded-2xl border border-gray-100 shadow-card p-8 flex flex-col items-center text-center group hover:shadow-lifted hover:-translate-y-1 transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-5 ring-4 ring-white shadow-sm group-hover:bg-emerald-100 transition-colors">
          <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="absolute -top-3 -right-3 w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center shadow-md">3</div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Apply &amp; get hired</h3>
        <p class="text-sm text-gray-500 leading-relaxed">Apply in seconds with your saved profile. Employers reach out directly — no middlemen, no delays.</p>
      </div>
    </div>

    <!-- Bottom CTA row -->
    <div class="mt-10 flex flex-wrap gap-4 justify-center">
      <RouterLink to="/jobs" class="inline-flex items-center gap-2 bg-primary-600 text-white font-semibold px-7 py-3 rounded-xl hover:bg-primary-700 transition-colors text-sm shadow-sm shadow-primary-200">
        Browse remote jobs
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
        </svg>
      </RouterLink>
      <RouterLink to="/professionals" class="inline-flex items-center gap-2 border border-gray-200 text-gray-700 font-semibold px-7 py-3 rounded-xl hover:bg-gray-50 transition-colors text-sm">
        Find professionals
      </RouterLink>
    </div>
  </section>

  <!-- ── Categories ─────────────────────────────────────────────── -->
  <section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-12">
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-500 mb-3">{{ lang.t('categories.explore') }}</span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">{{ lang.t('categories.title') }}</h2>
        <p class="text-gray-500 text-lg max-w-lg mx-auto">{{ lang.t('categories.subtitle') }}</p>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.name"
          @click="searchCategory(cat.name)"
          class="group relative bg-white p-6 rounded-2xl border border-gray-100 shadow-card text-left cursor-pointer overflow-hidden transition-all duration-300 hover:shadow-lifted hover:-translate-y-1 hover:border-primary-200 active:scale-95"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-primary-50/0 to-violet-50/0 group-hover:from-primary-50/70 group-hover:to-violet-50/50 transition-all duration-300 rounded-2xl" />
          <div class="relative">
            <div class="text-3xl mb-3">{{ cat.emoji }}</div>
            <p class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors mb-0.5">{{ cat.name }}</p>
            <p class="text-xs text-gray-400 font-medium">{{ categoryLabel(cat.name) }}</p>
          </div>
          <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </div>
        </button>
      </div>
    </div>
  </section>

  <!-- ── Features strip ─────────────────────────────────────────── -->
  <section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="feat in features" :key="feat.titleKey" class="card p-6 flex gap-4">
          <div :class="feat.iconBg" class="stat-icon flex-shrink-0">
            <component :is="feat.icon" class="w-6 h-6" :class="feat.iconColor" />
          </div>
          <div>
            <p class="font-bold text-gray-900 mb-1">{{ lang.t('features.' + feat.titleKey) }}</p>
            <p class="text-sm text-gray-500 leading-relaxed">{{ lang.t('features.' + feat.descKey) }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── Employer CTA ───────────────────────────────────────────── -->
  <section id="employers" class="py-24 px-4">
    <div class="max-w-5xl mx-auto">
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-violet-700 p-12 md:p-16 text-center shadow-2xl shadow-primary-500/30">
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/[0.05] rounded-full -translate-y-40 translate-x-40 blur-2xl pointer-events-none" />
        <div class="absolute bottom-0 left-0 w-60 h-60 bg-violet-400/20 rounded-full translate-y-32 -translate-x-32 blur-2xl pointer-events-none" />
        <div class="absolute inset-0 opacity-[0.06]"
          style="background-image:radial-gradient(circle,rgba(255,255,255,1) 1px,transparent 1px);background-size:24px 24px" />

        <div class="relative">
          <span class="inline-block text-xs font-bold uppercase tracking-widest text-white/50 mb-4">{{ lang.t('employers.label') }}</span>
          <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
            {{ lang.t('employers.title') }}
          </h2>
          <p class="text-primary-100/80 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
            {{ lang.t('employers.desc') }}
          </p>
          <RouterLink
            to="/register?role=employer"
            class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-10 py-4 rounded-2xl hover:bg-primary-50 transition-all duration-200 shadow-2xl text-base active:scale-95"
          >
            {{ lang.t('employers.cta') }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </RouterLink>
          <p class="text-white/40 text-xs mt-4">{{ lang.t('employers.note') }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { GlobeAltIcon, ShieldCheckIcon, BoltIcon } from '@heroicons/vue/24/outline'
import { useLanguageStore } from '@/stores/language'
import client from '@/api/client'

const router = useRouter()
const lang   = useLanguageStore()
const query  = ref('')

const categories = [
  { name: 'Engineering',      emoji: '💻' },
  { name: 'Design',           emoji: '🎨' },
  { name: 'Marketing',        emoji: '📣' },
  { name: 'Data Science',     emoji: '📊' },
  { name: 'Finance',          emoji: '💰' },
  { name: 'Product',          emoji: '🗂️' },
  { name: 'Operations',       emoji: '⚙️' },
  { name: 'Customer Success', emoji: '🤝' },
]

// Real active-job counts per category (loaded from the API). We never show
// inflated numbers: a category with real openings shows the true count, and
// everything else shows an honest "New listings added daily".
const catCounts = ref({})

function categoryLabel(name) {
  const n = catCounts.value[name] ?? 0
  if (n > 0) return `${n} ${n === 1 ? lang.t('categories.openRole') : lang.t('categories.openRoles')}`
  return lang.t('categories.addedDaily')
}

onMounted(async () => {
  try {
    const { data } = await client.get('/job-categories')
    catCounts.value = data.counts ?? {}
  } catch {
    catCounts.value = {}
  }
})

const features = [
  {
    icon: GlobeAltIcon,
    iconBg: 'bg-primary-50',
    iconColor: 'text-primary-600',
    titleKey: 'globalReach',
    descKey: 'globalReachDesc',
  },
  {
    icon: ShieldCheckIcon,
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
    titleKey: 'verifiedProfiles',
    descKey: 'verifiedProfilesDesc',
  },
  {
    icon: BoltIcon,
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
    titleKey: 'applyInSeconds',
    descKey: 'applyInSecondsDesc',
  },
]

function search() {
  router.push({ name: 'jobs', query: { q: query.value } })
}

function searchCategory(name) {
  router.push({ name: 'jobs', query: { category: name } })
}
</script>
