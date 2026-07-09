<template>
  <div class="max-w-3xl space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900 flex items-center gap-2">
        <ShieldCheckIcon class="w-6 h-6 text-primary-600" />
        Compliance
      </h1>
      <p class="text-gray-500 text-sm mt-0.5">Guidance for hiring international remote talent</p>
    </div>

    <div class="rounded-xl bg-primary-50 border border-primary-100 p-4 text-sm text-primary-800">
      You're seeing this because you have at least one <strong>International Remote</strong> job.
      These pointers help you engage cross-border talent cleanly. International candidates stay in
      their own country and work remotely — no visa sponsorship or relocation is involved.
    </div>

    <div v-for="section in sections" :key="section.title" class="card p-6">
      <h2 class="font-bold text-gray-900 flex items-center gap-2 mb-2">
        <span class="text-lg">{{ section.emoji }}</span> {{ section.title }}
      </h2>
      <p class="text-sm text-gray-600 leading-relaxed">{{ section.body }}</p>
    </div>

    <p class="text-xs text-gray-400 leading-relaxed">
      This is general guidance, not legal or tax advice. Rules vary by country — confirm classification
      and payroll obligations with a qualified advisor or an Employer of Record before hiring. Questions?
      <a href="mailto:support@remotearena.io" class="text-primary-600 hover:underline">support@remotearena.io</a>.
    </p>
  </div>
</template>

<script setup>
import { ShieldCheckIcon } from '@heroicons/vue/24/outline'

const sections = [
  {
    emoji: '📄',
    title: 'Contractor vs. remote employee',
    body: 'Decide how you engage each hire. Independent contractors invoice you and handle their own '
      + 'taxes; remote employees usually require an Employer of Record (EOR) in their country. '
      + 'Misclassifying an employee as a contractor is the most common compliance risk — set the '
      + 'engagement type on the job so expectations are clear from the start.',
  },
  {
    emoji: '💸',
    title: 'International payroll & payments',
    body: 'Choose how you will pay across borders — a global payroll/EOR platform (e.g. Deel, '
      + 'Remote.com), direct bank transfer, or a contractor-payments tool. Agree the currency up '
      + 'front; you can set a currency preference on each international job.',
  },
  {
    emoji: '🕔',
    title: 'Working hours & time zones',
    body: 'Be explicit about expected overlap with your team. Requiring a few hours of overlap is '
      + 'reasonable; requiring a full shift in a distant time zone narrows your pool. Capture this in '
      + 'the job’s working-hours field so candidates self-select accurately.',
  },
  {
    emoji: '🔒',
    title: 'Data protection',
    body: 'If candidates or hires handle personal or customer data, make sure your agreements cover '
      + 'confidentiality and applicable data-protection rules (such as GDPR for EU-based talent). '
      + 'Use least-privilege access and revoke it promptly when an engagement ends.',
  },
]
</script>
