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
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
          </svg>
          Frequently Asked Questions
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Questions &amp; answers</h1>
        <p class="text-gray-400 text-sm">Everything you need to know about how RemoteArena works.</p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-6 py-16 space-y-12">
      <section v-for="group in faqs" :key="group.title">
        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
          <span class="text-xl">{{ group.emoji }}</span> {{ group.title }}
        </h2>
        <div class="space-y-3">
          <details
            v-for="(item, i) in group.items"
            :key="i"
            class="group bg-white/[0.03] border border-gray-800/60 rounded-2xl px-5 open:bg-white/[0.05] transition-colors"
          >
            <summary class="flex items-center justify-between gap-4 cursor-pointer py-4 font-semibold text-gray-100 list-none">
              {{ item.q }}
              <svg class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </summary>
            <p class="text-sm text-gray-400 leading-relaxed pb-5 -mt-1">{{ item.a }}</p>
          </details>
        </div>
      </section>

      <!-- Still have questions -->
      <section class="bg-primary-500/5 border border-primary-500/15 rounded-2xl p-8 text-center">
        <h2 class="text-xl font-bold text-white mb-2">Still have questions?</h2>
        <p class="text-sm text-gray-400 mb-5">Reach the team directly — we usually reply within one business day.</p>
        <a
          href="mailto:support@remotearena.io"
          class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-7 py-3 rounded-xl hover:bg-primary-50 transition-colors text-sm"
        >
          support@remotearena.io
        </a>
      </section>
    </div>
  </div>
</template>

<script setup>
const faqs = [
  {
    emoji: '🌍',
    title: 'For job seekers',
    items: [
      { q: 'Is RemoteArena free for job seekers?', a: 'Yes — creating a profile, browsing jobs, and applying are completely free. We never charge candidates any fee, at any point.' },
      { q: 'Who are the companies hiring here?', a: 'Every employer on RemoteArena is a verified company hiring from the United States or Canada, for fully remote roles open to international talent.' },
      { q: 'Do I need to be in a specific country to apply?', a: 'No. RemoteArena is built for international talent. As long as you can work remotely and meet the role requirements, you can apply.' },
      { q: 'How do I apply to a job?', a: 'Build your profile once, then apply to any role in a couple of clicks. The employer receives your application and contacts you directly.' },
      { q: 'Will recruiters or agencies contact me?', a: 'No. RemoteArena is direct hire only — you deal with the actual employer, not a middleman.' },
    ],
  },
  {
    emoji: '🏢',
    title: 'For employers',
    items: [
      { q: 'How do I post a job?', a: 'Register with your company (business) email, complete verification, and post your role. Your first credits are included so you can start immediately.' },
      { q: 'Why do I need a business email to register?', a: 'To keep the marketplace trustworthy for candidates, company accounts must use a corporate email on your own domain. Free providers like Gmail or Outlook are not accepted for employer accounts.' },
      { q: 'How does employer verification work?', a: 'Companies on a recognized corporate domain are verified automatically. Others complete a quick business-verification step before their listings go live.' },
      { q: 'Are there placement or middleman fees?', a: 'No placement fees. You hire the candidate directly. Paid plans only cover job-posting capacity and premium features — never a cut of a hire.' },
    ],
  },
  {
    emoji: '🛡️',
    title: 'Trust & safety',
    items: [
      { q: 'How do you prevent fake job listings?', a: 'Employers are verified before posting, every listing can be reported, and our moderation team removes fraudulent postings and suspends the accounts behind them.' },
      { q: 'What should I do if a listing asks me for money?', a: 'Legitimate employers never ask candidates to pay to apply or interview. Report the listing immediately using the report button — we investigate every report.' },
      { q: 'How is my data handled?', a: 'Your data is only shared with employers when you choose to apply. See our Privacy Policy for full details on how information is stored and used.' },
    ],
  },
]
</script>
