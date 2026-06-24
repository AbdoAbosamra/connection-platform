<template>
  <div class="min-h-screen bg-gray-950 text-gray-300">
    <!-- Hero banner -->
    <div class="relative bg-gradient-to-br from-gray-900 via-primary-950/60 to-gray-950 border-b border-gray-800/60 py-16">
      <div
        class="absolute inset-0 opacity-[0.04]"
        style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:40px 40px"
      />
      <div class="relative max-w-3xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 bg-primary-500/10 border border-primary-500/20 text-primary-400 text-xs font-semibold px-4 py-2 rounded-full mb-6">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
          </svg>
          Community Standards
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Community Guidelines</h1>
        <p class="text-gray-400 text-sm">
          A safe, honest, and professional marketplace for everyone.
        </p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-6 py-16 space-y-12">
      <section>
        <div class="bg-primary-500/5 border border-primary-500/15 rounded-2xl p-6 text-sm text-gray-300 leading-relaxed">
          To maintain a safe and professional marketplace, all users must comply with the
          following standards. Violations can be
          <RouterLink to="#enforcement" class="text-primary-400 hover:underline">reported and enforced</RouterLink>
          at any time.
        </div>
      </section>

      <section>
        <h2 class="section-heading">1. Professional Conduct</h2>
        <p class="mb-4 text-sm leading-relaxed">Users must interact respectfully and professionally. The following conduct is prohibited:</p>
        <ul class="grid sm:grid-cols-2 gap-2">
          <li v-for="item in conduct" :key="item" class="flex items-center gap-2 text-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0" /> {{ item }}
          </li>
        </ul>
      </section>

      <section>
        <h2 class="section-heading">2. Honest Representation</h2>
        <p class="mb-4 text-sm leading-relaxed">Users must provide accurate information regarding their identity, business, qualifications, experience, skills, and job opportunities. The following are prohibited:</p>
        <ul class="grid sm:grid-cols-2 gap-2">
          <li v-for="item in honesty" :key="item" class="flex items-center gap-2 text-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0" /> {{ item }}
          </li>
        </ul>
      </section>

      <section>
        <h2 class="section-heading">3. Fraud Prevention</h2>
        <p class="mb-4 text-sm leading-relaxed">Users may not:</p>
        <ul class="space-y-2">
          <li v-for="item in fraud" :key="item" class="flex items-start gap-2 text-sm">
            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0" /> {{ item }}
          </li>
        </ul>
      </section>

      <section>
        <h2 class="section-heading">4. Platform Safety</h2>
        <p class="mb-4 text-sm leading-relaxed">Exercise caution when communicating with other users. We recommend:</p>
        <ul class="space-y-2">
          <li v-for="item in safety" :key="item" class="flex items-start gap-2 text-sm">
            <svg class="mt-0.5 w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75" />
            </svg>
            {{ item }}
          </li>
        </ul>
      </section>

      <section id="enforcement">
        <h2 class="section-heading">5. Enforcement</h2>
        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-6 text-sm leading-relaxed">
          RemoteArena reserves the right, at its sole discretion, to remove content, suspend
          accounts, restrict access, investigate activity, or permanently terminate users who
          violate these Guidelines or create risk to the Platform or its community.
          RemoteArena's decision regarding enforcement actions shall be final.
        </div>
        <p class="mt-5 text-sm text-gray-400">
          See something that violates these standards? Use the
          <strong class="text-white">Report</strong> button on any job or profile, and our team
          will review it promptly.
        </p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const conduct = [
  'Harassment', 'Threats', 'Hate speech', 'Discrimination',
  'Intimidation', 'Defamation', 'Abusive behavior',
]
const honesty = [
  'Fake profiles', 'Identity impersonation', 'Resume fraud',
  'Credential falsification', 'Fake job listings', 'Misleading compensation claims',
]
const fraud = [
  'Request application fees from candidates',
  'Request interview fees',
  'Sell access to jobs',
  'Promote pyramid schemes or MLM programs',
  'Conduct phishing activities',
  'Engage in money laundering',
  'Solicit sensitive financial information unrelated to legitimate hiring',
]
const safety = [
  'Verify identities independently',
  'Use written agreements',
  'Maintain records of communications',
  'Report suspicious activity immediately',
]
</script>
