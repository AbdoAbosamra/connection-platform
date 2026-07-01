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
          <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse" />
          About RemoteArena
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Direct hiring, done right</h1>
        <p class="text-gray-400 text-base max-w-xl mx-auto leading-relaxed">
          RemoteArena connects international talent with verified US &amp; Canadian companies hiring
          remotely — directly, with no recruiters and no middleman fees.
        </p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-6 py-16 space-y-14">
      <!-- Mission -->
      <section>
        <h2 class="text-2xl font-bold text-white mb-4">Our mission</h2>
        <p class="text-sm leading-relaxed mb-4">
          Great remote jobs at North-American companies are still hard to reach for talented people
          living elsewhere — usually gated behind recruiters, agencies, and layers of fees that eat
          into everyone's outcome. RemoteArena removes the middle layer entirely. Job seekers apply
          directly, and employers hire directly. No placement fees, no gatekeepers.
        </p>
        <p class="text-sm leading-relaxed">
          We focus on one thing and do it well: connecting skilled international professionals with
          <strong class="text-gray-100">verified companies in the United States and Canada</strong>
          that hire remotely.
        </p>
      </section>

      <!-- What we do -->
      <section>
        <h2 class="text-2xl font-bold text-white mb-5">What makes us different</h2>
        <div class="grid sm:grid-cols-3 gap-4">
          <div class="bg-white/[0.03] border border-gray-800/60 rounded-2xl p-5">
            <div class="text-2xl mb-2">🇺🇸🇨🇦</div>
            <p class="font-semibold text-gray-100 mb-1">US &amp; Canada focused</p>
            <p class="text-xs text-gray-400 leading-relaxed">Every employer is a company hiring from the United States or Canada.</p>
          </div>
          <div class="bg-white/[0.03] border border-gray-800/60 rounded-2xl p-5">
            <div class="text-2xl mb-2">🤝</div>
            <p class="font-semibold text-gray-100 mb-1">Direct hire</p>
            <p class="text-xs text-gray-400 leading-relaxed">You talk to the employer directly. No agencies, no placement fees, ever.</p>
          </div>
          <div class="bg-white/[0.03] border border-gray-800/60 rounded-2xl p-5">
            <div class="text-2xl mb-2">🛡️</div>
            <p class="font-semibold text-gray-100 mb-1">Verified employers</p>
            <p class="text-xs text-gray-400 leading-relaxed">Companies are verified before they can post — see how below.</p>
          </div>
        </div>
      </section>

      <!-- Verification process -->
      <section>
        <h2 class="text-2xl font-bold text-white mb-4">How we verify employers</h2>
        <p class="text-sm leading-relaxed mb-5">
          Trust is the whole product. Before a company can post a single job, it must prove it is a
          real business:
        </p>
        <ol class="space-y-4">
          <li class="flex gap-4">
            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary-500/15 text-primary-300 text-sm font-bold flex items-center justify-center">1</span>
            <div>
              <p class="font-semibold text-gray-100">Business email required</p>
              <p class="text-sm text-gray-400 leading-relaxed">Companies must register with a corporate email address on their own domain — free providers (Gmail, Yahoo, Outlook…) are rejected at sign-up.</p>
            </div>
          </li>
          <li class="flex gap-4">
            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary-500/15 text-primary-300 text-sm font-bold flex items-center justify-center">2</span>
            <div>
              <p class="font-semibold text-gray-100">Domain &amp; identity check</p>
              <p class="text-sm text-gray-400 leading-relaxed">Corporate domains are verified automatically; others confirm their identity via a business verification step before posting.</p>
            </div>
          </li>
          <li class="flex gap-4">
            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary-500/15 text-primary-300 text-sm font-bold flex items-center justify-center">3</span>
            <div>
              <p class="font-semibold text-gray-100">Ongoing moderation</p>
              <p class="text-sm text-gray-400 leading-relaxed">Every listing can be reported and reviewed. Fake postings, fee requests, and scams are removed and the accounts suspended.</p>
            </div>
          </li>
        </ol>
      </section>

      <!-- Contact -->
      <section class="bg-primary-500/5 border border-primary-500/15 rounded-2xl p-8 text-center">
        <h2 class="text-xl font-bold text-white mb-2">Get in touch</h2>
        <p class="text-sm text-gray-400 mb-5 max-w-md mx-auto leading-relaxed">
          Questions, partnerships, or want to verify who we are? We'd love to hear from you.
        </p>
        <a
          href="mailto:support@remotearena.io"
          class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-7 py-3 rounded-xl hover:bg-primary-50 transition-colors text-sm"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
          support@remotearena.io
        </a>
        <p class="mt-6 text-xs text-gray-500">
          Read our
          <RouterLink to="/faq" class="text-primary-400 hover:underline">FAQ</RouterLink>,
          <RouterLink to="/community-guidelines" class="text-primary-400 hover:underline">Community Guidelines</RouterLink>, and
          <RouterLink to="/terms" class="text-primary-400 hover:underline">Terms</RouterLink>.
        </p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
