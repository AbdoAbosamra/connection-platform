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
        Over 2,400 active opportunities worldwide
      </div>

      <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-[1.08] mb-6 tracking-tight">
        Your Skills,<br />
        <span class="bg-gradient-to-r from-primary-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">
          US Opportunities
        </span>
      </h1>
      <p class="text-xl text-white/60 mb-12 max-w-2xl mx-auto leading-relaxed">
        Connextion bridges world-class international talent with top US companies hiring remotely — no borders, no limits.
      </p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink
          to="/jobs"
          class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-9 py-4 rounded-2xl hover:bg-primary-50 transition-all duration-200 shadow-2xl shadow-black/30 text-base active:scale-95"
        >
          Browse Jobs
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </RouterLink>
        <RouterLink
          to="/register?role=job_seeker"
          class="inline-flex items-center justify-center gap-2 border-2 border-white/30 text-white font-semibold px-9 py-4 rounded-2xl hover:bg-white/10 backdrop-blur-sm transition-all duration-200 text-base active:scale-95"
        >
          Create Free Profile
        </RouterLink>
      </div>

      <!-- Trust badges -->
      <div class="mt-14 flex flex-wrap justify-center gap-x-8 gap-y-3 text-white/40 text-xs font-medium">
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Free for job seekers</span>
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> 50+ countries</span>
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Visa sponsorship jobs</span>
        <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Remote-first roles</span>
      </div>
    </div>
  </section>

  <!-- ── Quick search ───────────────────────────────────────────── -->
  <section class="max-w-4xl mx-auto px-4 -mt-10 relative z-10">
    <div class="bg-white rounded-3xl shadow-2xl shadow-gray-400/20 border border-gray-100 p-5 flex flex-col sm:flex-row gap-3">
      <div class="flex-1 relative">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
        </svg>
        <input
          v-model="query"
          type="text"
          placeholder="Job title, skill, or keyword…"
          class="input !pl-10 !py-3.5"
          @keydown.enter="search"
        />
      </div>
      <select v-model="locationType" class="input sm:w-48 !py-3.5 bg-gray-50 cursor-pointer">
        <option value="">🌍 All locations</option>
        <option value="remote">🏠 Remote</option>
        <option value="hybrid">🏢 Hybrid</option>
        <option value="on_site">📍 On-site</option>
      </select>
      <button @click="search" class="btn-primary !py-3.5 !px-8 text-base whitespace-nowrap">
        Search Jobs
      </button>
    </div>
  </section>

  <!-- ── Stats ──────────────────────────────────────────────────── -->
  <section class="max-w-7xl mx-auto px-4 py-20">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <div v-for="stat in stats" :key="stat.label" class="text-center group">
        <p class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-primary-600 to-violet-600 bg-clip-text text-transparent mb-2">
          {{ stat.value }}
        </p>
        <p class="text-sm text-gray-500 font-medium">{{ stat.label }}</p>
      </div>
    </div>
  </section>

  <!-- ── Categories ─────────────────────────────────────────────── -->
  <section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-12">
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-500 mb-3">Explore</span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">Browse by Category</h2>
        <p class="text-gray-500 text-lg max-w-lg mx-auto">Find the perfect role in any industry</p>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.name"
          @click="searchCategory(cat.name)"
          class="group relative bg-white p-6 rounded-2xl border border-gray-100 shadow-card text-left cursor-pointer overflow-hidden transition-all duration-300 hover:shadow-lifted hover:-translate-y-1 hover:border-primary-200 active:scale-95"
        >
          <!-- Hover gradient overlay -->
          <div class="absolute inset-0 bg-gradient-to-br from-primary-50/0 to-violet-50/0 group-hover:from-primary-50/70 group-hover:to-violet-50/50 transition-all duration-300 rounded-2xl" />
          <div class="relative">
            <div class="text-3xl mb-3">{{ cat.emoji }}</div>
            <p class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors mb-0.5">{{ cat.name }}</p>
            <p class="text-xs text-gray-400 font-medium">{{ cat.count.toLocaleString() }}+ jobs</p>
          </div>
          <!-- Arrow indicator -->
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
        <div v-for="feat in features" :key="feat.title" class="card p-6 flex gap-4">
          <div :class="feat.iconBg" class="stat-icon flex-shrink-0">
            <component :is="feat.icon" class="w-6 h-6" :class="feat.iconColor" />
          </div>
          <div>
            <p class="font-bold text-gray-900 mb-1">{{ feat.title }}</p>
            <p class="text-sm text-gray-500 leading-relaxed">{{ feat.desc }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── Employer CTA ───────────────────────────────────────────── -->
  <section id="employers" class="py-24 px-4">
    <div class="max-w-5xl mx-auto">
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-violet-700 p-12 md:p-16 text-center shadow-2xl shadow-primary-500/30">
        <!-- Decorations -->
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/[0.05] rounded-full -translate-y-40 translate-x-40 blur-2xl pointer-events-none" />
        <div class="absolute bottom-0 left-0 w-60 h-60 bg-violet-400/20 rounded-full translate-y-32 -translate-x-32 blur-2xl pointer-events-none" />
        <!-- Dot grid -->
        <div class="absolute inset-0 opacity-[0.06]"
          style="background-image:radial-gradient(circle,rgba(255,255,255,1) 1px,transparent 1px);background-size:24px 24px" />

        <div class="relative">
          <span class="inline-block text-xs font-bold uppercase tracking-widest text-white/50 mb-4">For Employers</span>
          <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
            Hire globally, grow faster
          </h2>
          <p class="text-primary-100/80 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
            Access a pre-vetted pool of skilled professionals from 50+ countries. Post a job in minutes and start receiving qualified applications today.
          </p>
          <RouterLink
            to="/register?role=employer"
            class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-10 py-4 rounded-2xl hover:bg-primary-50 transition-all duration-200 shadow-2xl text-base active:scale-95"
          >
            Post a Job Free
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </RouterLink>
          <p class="text-white/40 text-xs mt-4">No credit card required · Free to post</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import {
  GlobeAltIcon, ShieldCheckIcon, BoltIcon,
} from '@heroicons/vue/24/outline'

const router       = useRouter()
const query        = ref('')
const locationType = ref('')

const stats = [
  { value: '2,400+',  label: 'Active Jobs' },
  { value: '18,000+', label: 'Registered Talent' },
  { value: '340+',    label: 'Hiring Companies' },
  { value: '50+',     label: 'Countries Represented' },
]

const categories = [
  { name: 'Engineering',      emoji: '💻', count: 820 },
  { name: 'Design',           emoji: '🎨', count: 210 },
  { name: 'Marketing',        emoji: '📣', count: 180 },
  { name: 'Data Science',     emoji: '📊', count: 150 },
  { name: 'Finance',          emoji: '💰', count: 95  },
  { name: 'Product',          emoji: '🗂️', count: 130 },
  { name: 'Operations',       emoji: '⚙️', count: 75  },
  { name: 'Customer Success', emoji: '🤝', count: 90  },
]

const features = [
  {
    icon: GlobeAltIcon,
    iconBg: 'bg-primary-50',
    iconColor: 'text-primary-600',
    title: 'Global Reach',
    desc: 'Connect with talent from 50+ countries and companies across the United States.',
  },
  {
    icon: ShieldCheckIcon,
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
    title: 'Verified Profiles',
    desc: 'Every profile is reviewed to ensure quality and authenticity for both sides.',
  },
  {
    icon: BoltIcon,
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
    title: 'Apply in Seconds',
    desc: 'One-click applications with your saved profile. No repetitive form filling.',
  },
]

function search() {
  router.push({ name: 'jobs', query: { q: query.value, location_type: locationType.value } })
}

function searchCategory(name) {
  router.push({ name: 'jobs', query: { category: name } })
}
</script>
