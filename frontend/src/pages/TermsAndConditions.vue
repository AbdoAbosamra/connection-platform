<template>
  <div class="min-h-screen bg-gray-950 text-gray-300">

    <!-- Hero banner -->
    <div class="relative bg-gradient-to-br from-gray-900 via-violet-950/50 to-gray-950 border-b border-gray-800/60 py-16">
      <div class="absolute inset-0 opacity-[0.04]"
        style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:40px 40px" />
      <div class="relative max-w-3xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xs font-semibold px-4 py-2 rounded-full mb-6">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          Legal Document
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Terms of Service</h1>
        <p class="text-gray-400 text-sm">
          Effective Date: <span class="text-gray-300 font-medium">June 1, 2025</span>
          &nbsp;·&nbsp;
          Platform: <span class="text-gray-300 font-medium">RemoteArena</span>
          &nbsp;·&nbsp;
          Website: <span class="text-primary-400">remotearena.io</span>
        </p>
      </div>
    </div>

    <!-- Table of contents -->
    <div class="max-w-3xl mx-auto px-6 pt-12">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h2 class="text-white font-bold text-sm mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>
          Table of Contents
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1">
          <a v-for="item in toc" :key="item.id" :href="`#${item.id}`"
            class="text-xs text-gray-400 hover:text-violet-400 transition-colors py-1 flex items-center gap-1.5">
            <span class="text-violet-600 font-mono font-bold">{{ item.num }}</span>
            {{ item.title }}
          </a>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-6 py-12 space-y-14">

      <!-- Important Legal Notice -->
      <section>
        <div class="bg-amber-500/8 border border-amber-500/25 rounded-2xl p-6 text-sm leading-relaxed space-y-3">
          <p class="text-amber-400 font-bold text-base flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            IMPORTANT LEGAL NOTICE
          </p>
          <p class="text-gray-300">
            These Terms of Service ("<strong class="text-white">Terms</strong>") constitute a legally binding agreement between you ("<strong class="text-white">User</strong>," "<strong class="text-white">Employer</strong>," "<strong class="text-white">Job Seeker</strong>," or "<strong class="text-white">Contractor</strong>") and <strong class="text-white">RemoteArena Inc.</strong> ("<strong class="text-white">Platform</strong>," "<strong class="text-white">we</strong>," "<strong class="text-white">us</strong>," or "<strong class="text-white">our</strong>").
          </p>
          <p class="text-gray-300">
            By accessing or using the Platform, you acknowledge that you have read, understood, and agreed to be bound by these Terms.
          </p>
          <p class="bg-rose-500/10 border border-rose-500/20 rounded-xl px-4 py-3 text-rose-300 font-semibold text-center tracking-wide">
            IF YOU DO NOT AGREE TO THESE TERMS, YOU MUST NOT ACCESS OR USE THE PLATFORM.
          </p>
        </div>
      </section>

      <!-- 1 -->
      <section id="intermediary">
        <h2 class="section-heading">1. Platform Purpose &amp; Intermediary Status</h2>

        <div class="subsection-block">
          <h3 class="subsection-heading">1.1 Venue-Only Marketplace</h3>
          <p class="body-text mb-3">RemoteArena is a technology platform and intermediary marketplace that facilitates introductions and connections between businesses and independent job seekers, freelancers, contractors, consultants, and remote service providers globally.</p>
          <div class="callout-box border-violet-500/20 bg-violet-500/5">
            <p class="text-violet-300 font-bold mb-3">THE PLATFORM IS A "VENUE ONLY."</p>
            <p class="body-text mb-3">The Platform merely provides online tools, communications systems, profile hosting, search functionality, and marketplace access.</p>
            <p class="text-gray-300 text-sm font-semibold mb-2">The Platform does <span class="text-rose-400">NOT</span>:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
              <div v-for="item in venueExclusions" :key="item" class="flex items-start gap-2 text-xs text-gray-400">
                <svg class="w-3 h-3 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ item }}
              </div>
            </div>
          </div>
          <p class="body-text mt-4">All engagements, contracts, negotiations, work arrangements, compensation agreements, and professional relationships arising from use of the Platform are entered into directly and exclusively between the Company and the Job Seeker.</p>
        </div>

        <div class="subsection-block mt-6">
          <h3 class="subsection-heading">1.2 No Employment Relationship</h3>
          <p class="body-text mb-3">Nothing in these Terms shall be construed to create any of the following between the Platform and any User:</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <div v-for="rel in noRelationships" :key="rel"
              class="text-xs text-gray-400 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2">
              <span class="w-1.5 h-1.5 rounded-full bg-rose-500 flex-shrink-0" />
              {{ rel }}
            </div>
          </div>
          <p class="body-text mt-4">The Platform expressly disclaims any responsibility or liability arising from any alleged employment, labor, contractor, payroll, workplace, tax, or worker-classification relationship between Users. Users acknowledge and agree that they interact entirely at their own discretion and risk.</p>
        </div>
      </section>

      <!-- 2 -->
      <section id="remote-only">
        <h2 class="section-heading">2. Remote-Only Platform &amp; No Immigration Services</h2>

        <div class="subsection-block">
          <h3 class="subsection-heading">2.1 Remote Collaboration Platform Only</h3>
          <p class="body-text mb-3">The Platform is designed and operated <strong class="text-white">strictly</strong> for:</p>
          <ul class="list-items">
            <li>Remote work and online collaboration</li>
            <li>Freelance and independent contractor services</li>
            <li>Outsourcing relationships</li>
            <li>International remote service arrangements</li>
          </ul>
          <p class="body-text mt-4">The Platform does <strong class="text-white">not</strong> facilitate physical labor migration, office-based employment, hybrid work arrangements, or international relocation of any kind.</p>
        </div>

        <div class="subsection-block mt-6">
          <h3 class="subsection-heading">2.2 Absolute Exclusion of Immigration Services</h3>
          <div class="callout-box border-rose-500/20 bg-rose-500/5 mb-4">
            <p class="text-rose-300 font-bold text-center">THE PLATFORM DOES NOT PROVIDE IMMIGRATION SERVICES OF ANY KIND.</p>
          </div>
          <p class="body-text mb-3">Specifically, the Platform does not:</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
            <div v-for="item in immigrationExclusions" :key="item" class="flex items-start gap-2 text-xs text-gray-400">
              <svg class="w-3 h-3 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ item }}
            </div>
          </div>
          <p class="body-text mt-4">The Platform shall not be interpreted as an immigration consultant, visa sponsor, migration agent, or labor export agency.</p>
        </div>

        <div class="subsection-block mt-6">
          <h3 class="subsection-heading">2.3 User Responsibility for Legal Compliance</h3>
          <p class="body-text mb-3">Each User bears sole and exclusive responsibility for complying with all applicable laws in their respective jurisdictions, including:</p>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div v-for="law in complianceLaws" :key="law"
              class="text-xs text-gray-400 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 text-center">
              {{ law }}
            </div>
          </div>
          <p class="body-text mt-4">The Platform does not verify legal compliance by Users and disclaims all liability arising from noncompliance. Employers are solely responsible for determining whether workers may legally provide services in the applicable jurisdiction. Job Seekers are solely responsible for determining whether they may legally perform services under applicable laws.</p>
        </div>
      </section>

      <!-- 3 -->
      <section id="eligibility">
        <h2 class="section-heading">3. User Eligibility &amp; Account Registration</h2>

        <div class="subsection-block">
          <h3 class="subsection-heading">3.1 Eligibility Requirements</h3>
          <ul class="list-items">
            <li>Be at least <strong class="text-white">18 years old</strong></li>
            <li>Possess legal capacity to enter into binding contracts</li>
            <li>Provide truthful, accurate, and current information during registration</li>
            <li>Use the Platform in full compliance with applicable laws</li>
            <li>Not be prohibited from using the Platform under any applicable law</li>
          </ul>
          <p class="body-text mt-3">Business entities registering on the Platform represent that the individual accepting these Terms has full legal authority to bind the entity.</p>
        </div>

        <div class="subsection-block mt-6">
          <h3 class="subsection-heading">3.2 Identity Verification</h3>
          <p class="body-text mb-3">The Platform may request any of the following to verify User identity:</p>
          <ul class="list-items">
            <li>Government-issued identification</li>
            <li>Corporate registration documents</li>
            <li>Tax identification information</li>
            <li>Proof of business address</li>
            <li>Professional credentials or licenses</li>
          </ul>
          <p class="body-text mt-3">Failure to provide requested information may result in account suspension or termination. The Platform reserves absolute discretion regarding verification standards and approval decisions.</p>
        </div>
      </section>

      <!-- 4 -->
      <section id="prohibited">
        <h2 class="section-heading">4. Prohibited Activities</h2>
        <p class="body-text mb-5">Users shall not engage in any of the following activities:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
          <div v-for="item in prohibitedUses" :key="item" class="flex items-start gap-2.5 bg-rose-500/5 border border-rose-500/15 rounded-xl p-3 text-xs text-gray-400 leading-relaxed">
            <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            {{ item }}
          </div>
        </div>
        <div class="callout-box border-amber-500/20 bg-amber-500/5">
          <p class="text-amber-400 font-semibold text-sm mb-2">Employers are strictly prohibited from:</p>
          <ul class="list-items">
            <li>Charging Job Seekers application or interview fees</li>
            <li>Requiring unlawful recruitment payments of any kind</li>
            <li>Soliciting sensitive financial information unrelated to legitimate hiring</li>
            <li>Posting jobs that require in-person attendance, relocation, or hybrid arrangements</li>
          </ul>
        </div>
        <p class="body-text mt-4">The Platform reserves the right, in its sole discretion, to suspend, restrict, investigate, or permanently terminate accounts suspected of fraudulent, deceptive, unlawful, or harmful activity without prior notice.</p>
      </section>

      <!-- 5 -->
      <section id="content">
        <h2 class="section-heading">5. User Content &amp; Representations</h2>
        <p class="body-text mb-4">Users are solely responsible for all information submitted through the Platform, including resumes, job postings, contracts, communications, credentials, portfolio materials, and payment information.</p>
        <p class="body-text mb-4">The Platform does not independently verify the truthfulness, legality, quality, or accuracy of User-submitted content. The Platform expressly disclaims any warranty regarding:</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
          <div v-for="item in contentDisclaimers" :key="item"
            class="text-xs text-gray-400 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-gray-600 flex-shrink-0" />
            {{ item }}
          </div>
        </div>
      </section>

      <!-- 6 -->
      <section id="availability">
        <h2 class="section-heading">6. Platform Availability Disclaimer</h2>
        <div class="callout-box border-amber-500/20 bg-amber-500/5">
          <p class="text-amber-300 font-bold text-sm mb-3 tracking-wide">THE PLATFORM IS PROVIDED "AS IS," "AS AVAILABLE," AND "WITH ALL FAULTS."</p>
          <p class="body-text mb-3">To the fullest extent permitted by law, the Platform disclaims all warranties, express or implied, including merchantability, fitness for a particular purpose, non-infringement, availability, reliability, security, accuracy, and continuous operation.</p>
          <p class="text-gray-300 text-sm font-semibold mb-2">The Platform does not guarantee:</p>
          <div class="grid grid-cols-2 gap-1.5">
            <div v-for="item in noGuarantees" :key="item" class="flex items-center gap-1.5 text-xs text-gray-400">
              <span class="w-1 h-1 rounded-full bg-amber-500/60 flex-shrink-0" />
              {{ item }}
            </div>
          </div>
        </div>
        <p class="body-text mt-4">Users assume all risks associated with use of the Platform.</p>
      </section>

      <!-- 7 -->
      <section id="liability">
        <h2 class="section-heading">7. Limitation of Liability</h2>
        <div class="callout-box border-amber-500/20 bg-amber-500/5 mb-5">
          <p class="text-amber-300 font-bold text-sm mb-3 tracking-wide">TO THE MAXIMUM EXTENT PERMITTED BY LAW, THE PLATFORM SHALL NOT BE LIABLE FOR:</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <div v-for="item in liabilityExclusions" :key="item" class="flex items-start gap-1.5 text-xs text-gray-400">
              <svg class="w-3 h-3 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ item }}
            </div>
          </div>
        </div>
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-5">
          <p class="text-white text-sm font-semibold mb-2">Aggregate Liability Cap</p>
          <p class="body-text">The Platform's aggregate liability arising from any claim shall not exceed the greater of: <strong class="text-white">(a)</strong> the total fees paid by the User to the Platform during the preceding three (3) months; or <strong class="text-white">(b) USD $100</strong>. This limitation applies regardless of the legal theory of the claim.</p>
        </div>
      </section>

      <!-- 8 -->
      <section id="interactions">
        <h2 class="section-heading">8. User-to-User Interactions</h2>
        <p class="body-text mb-4">Users acknowledge that all interactions occur solely between independent parties. The Platform:</p>
        <ul class="list-items">
          <li>Does not control User conduct or supervise engagements</li>
          <li>Does not enforce contracts between Users</li>
          <li>Does not guarantee payments or completion of services</li>
          <li>Does not mediate ongoing business relationships unless expressly stated</li>
          <li>Does not verify the accuracy of representations made by either party</li>
        </ul>
        <p class="body-text mt-4">Users assume all risks associated with off-platform communications and engagements, including disputes, non-payment, fraud, and breach of contract.</p>
      </section>

      <!-- 9 -->
      <section id="indemnification">
        <h2 class="section-heading">9. Indemnification</h2>
        <p class="body-text mb-4">Users agree to defend, indemnify, and hold harmless RemoteArena Inc., its affiliates, officers, directors, employees, contractors, licensors, and agents from and against all claims, liabilities, damages, judgments, penalties, losses, costs, and expenses (including reasonable attorneys' fees) arising from:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div v-for="item in indemnificationItems" :key="item"
            class="flex items-start gap-2 text-xs text-gray-400 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2.5">
            <span class="text-violet-500 mt-0.5">→</span>
            {{ item }}
          </div>
        </div>
        <p class="body-text mt-4">This indemnification obligation survives termination of these Terms.</p>
      </section>

      <!-- 10 -->
      <section id="termination">
        <h2 class="section-heading">10. Termination &amp; Account Suspension</h2>
        <p class="body-text mb-4">The Platform reserves the absolute right to suspend accounts, restrict functionality, remove content, refuse service, or permanently terminate Users for any reason or no reason, including:</p>
        <ul class="list-items">
          <li>Suspected fraud, misrepresentation, or identity theft</li>
          <li>Violation of these Terms or applicable law</li>
          <li>Security concerns or unauthorized access attempts</li>
          <li>Posting non-remote or non-compliant job listings</li>
          <li>Reputational risk to the Platform or other Users</li>
          <li>Failure to provide requested verification documents</li>
        </ul>
        <p class="body-text mt-4">The Platform shall have no liability arising from suspension or termination decisions. Users whose accounts are terminated for violations may not re-register without express written consent.</p>
      </section>

      <!-- 11 -->
      <section id="governing-law">
        <h2 class="section-heading">11. Governing Law</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 text-sm text-gray-400 leading-relaxed">
          <p class="mb-3">These Terms shall be governed exclusively by and construed in accordance with the laws of the <strong class="text-white">United States</strong> and the laws of the <strong class="text-white">State of Delaware</strong>, without regard to conflict-of-law principles.</p>
          <p>Users expressly waive the application of foreign labor laws, employment tribunals, worker-protection statutes, or foreign jurisdictional claims against the Platform to the maximum extent permitted by applicable law.</p>
        </div>
      </section>

      <!-- 12 -->
      <section id="arbitration">
        <h2 class="section-heading">12. Mandatory Binding Arbitration</h2>
        <p class="body-text mb-4">Any dispute, controversy, or claim arising out of or relating to these Terms or the Platform shall be resolved exclusively through final and binding arbitration. Arbitration shall:</p>
        <ul class="list-items">
          <li>Be conducted in the United States, in English</li>
          <li>Be administered by a recognized arbitration provider selected by the Platform</li>
          <li>Proceed on an individual basis only</li>
          <li>Result in a decision that is final and binding on all parties</li>
        </ul>
        <p class="body-text mt-4">Users waive any right to litigate disputes in court except where prohibited by applicable law. This clause does not prevent either party from seeking emergency injunctive relief for intellectual property infringement or data breaches.</p>
      </section>

      <!-- 13 -->
      <section id="class-action">
        <h2 class="section-heading">13. Class Action Waiver</h2>
        <div class="callout-box border-amber-500/20 bg-amber-500/5">
          <p class="text-amber-300 font-bold text-sm mb-3 tracking-wide">TO THE MAXIMUM EXTENT PERMITTED BY LAW, USERS AGREE THAT:</p>
          <ul class="list-items">
            <li>Claims may only be brought on an individual basis</li>
            <li>No class action or representative proceeding is permitted against the Platform</li>
            <li>No collective or consolidated arbitration is permitted</li>
            <li>No private attorney general action is permitted</li>
          </ul>
          <p class="body-text mt-3">Users waive participation in any class, collective, consolidated, or representative proceeding against RemoteArena Inc.</p>
        </div>
      </section>

      <!-- 14 -->
      <section id="electronic">
        <h2 class="section-heading">14. Electronic Communications &amp; Consent</h2>
        <p class="body-text mb-4">Users consent to receive electronic communications, notices, disclosures, and agreements from the Platform via email or in-Platform notifications. Electronic acceptance of these Terms constitutes a legally binding agreement equivalent to a written signature under applicable electronic signature laws.</p>
        <p class="body-text">You may withdraw consent to electronic communications only by deleting your account, with the understanding that doing so terminates your access to the Platform.</p>
      </section>

      <!-- 15 -->
      <section id="ip">
        <h2 class="section-heading">15. Intellectual Property</h2>
        <p class="body-text mb-4">All Platform software, branding, content, databases, algorithms, interfaces, and technology are owned by RemoteArena Inc. or its licensors and are protected by applicable intellectual property laws.</p>
        <p class="body-text mb-3">Users may not:</p>
        <ul class="list-items">
          <li>Reverse-engineer, decompile, or disassemble any part of the Platform</li>
          <li>Scrape, harvest, or copy Platform data without express written permission</li>
          <li>Use automated bots or scripts to interact with the Platform</li>
          <li>Reproduce or redistribute RemoteArena branding without consent</li>
          <li>Create derivative works based on Platform content or technology</li>
        </ul>
        <p class="body-text mt-4">You retain ownership of all Content you submit. By submitting Content, you grant RemoteArena a non-exclusive, worldwide, royalty-free licence to store, display, and transmit that Content solely for the purpose of operating the Platform.</p>
      </section>

      <!-- 16 -->
      <section id="privacy">
        <h2 class="section-heading">16. Data Privacy</h2>
        <p class="body-text mb-4">Use of the Platform is governed by the <RouterLink to="/privacy" class="text-primary-400 hover:underline">RemoteArena Privacy Policy</RouterLink>, which is incorporated into these Terms by reference.</p>
        <p class="body-text">Users acknowledge that cross-border data transfers may occur due to the international nature of the Platform. By using the Platform you consent to such transfers in accordance with the Privacy Policy and applicable data protection laws.</p>
      </section>

      <!-- 17 -->
      <section id="force-majeure">
        <h2 class="section-heading">17. Force Majeure</h2>
        <p class="body-text mb-4">The Platform shall not be liable for delays, interruptions, or failures resulting from events beyond its reasonable control, including:</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          <div v-for="item in forceMajeureItems" :key="item"
            class="text-xs text-gray-400 bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 text-center">
            {{ item }}
          </div>
        </div>
      </section>

      <!-- 18 -->
      <section id="fees">
        <h2 class="section-heading">18. Fees &amp; Credits</h2>
        <ul class="list-items">
          <li>Job Seekers may use the Platform free of charge. We will never charge job seekers fees for browsing or applying to positions.</li>
          <li>Employers on the free tier receive a limited number of job post credits. Paid subscription tiers provide additional credits and features.</li>
          <li>Job post credits consumed by publishing a live listing are <strong class="text-white">non-refundable</strong>.</li>
          <li>All payments are processed through our payment provider. We do not store full payment card details.</li>
          <li>Subscription fees are billed in advance. Cancellations take effect at the end of the current billing period.</li>
          <li>We reserve the right to change pricing with 30 days' notice to active subscribers.</li>
        </ul>
      </section>

      <!-- 19 -->
      <section id="changes">
        <h2 class="section-heading">19. Changes to These Terms</h2>
        <p class="body-text">We may modify these Terms at any time. We will provide at least <strong class="text-white">14 days' notice</strong> of material changes via email or a prominent notice on the Platform before the updated Terms take effect. Your continued use of the Platform after the effective date constitutes acceptance. If you do not agree to the updated Terms, you must stop using the Platform and may delete your account.</p>
      </section>

      <!-- 20 -->
      <section id="miscellaneous">
        <h2 class="section-heading">20. Miscellaneous</h2>
        <ul class="list-items">
          <li><span class="text-white font-semibold">Entire Agreement:</span> These Terms and our Privacy Policy constitute the entire agreement between you and RemoteArena regarding use of the Platform, superseding all prior agreements.</li>
          <li><span class="text-white font-semibold">Severability:</span> If any provision is held invalid or unenforceable, it shall be modified to the minimum extent necessary; remaining provisions remain in full force.</li>
          <li><span class="text-white font-semibold">No Waiver:</span> Our failure to enforce any right or provision does not constitute a waiver of that right.</li>
          <li><span class="text-white font-semibold">Assignment:</span> You may not assign or transfer your rights or obligations without our prior written consent. We may assign our rights without restriction.</li>
          <li><span class="text-white font-semibold">Language:</span> These Terms are written in English. Any translations are for convenience only; the English version controls in all cases.</li>
        </ul>
      </section>

      <!-- 21 -->
      <section id="contact">
        <h2 class="section-heading">21. Contact Information</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-sm space-y-3">
          <p class="text-white font-bold text-base">RemoteArena Inc.</p>
          <div class="space-y-1.5 text-gray-400">
            <p>📧 Legal &amp; compliance: <span class="text-primary-400">legal@remotearena.io</span></p>
            <p>🔒 Security reports: <span class="text-primary-400">security@remotearena.io</span></p>
            <p>🛡️ Privacy inquiries: <span class="text-primary-400">privacy@remotearena.io</span></p>
            <p>💬 General support: <span class="text-primary-400">support@remotearena.io</span></p>
            <p>🌐 Website: <span class="text-primary-400">remotearena.io</span></p>
          </div>
        </div>
      </section>

      <!-- Footer links -->
      <div class="border-t border-gray-800/60 pt-8 flex items-center justify-between text-sm">
        <RouterLink to="/privacy" class="text-primary-400 hover:text-primary-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
          </svg>
          Read our Privacy Policy →
        </RouterLink>
        <RouterLink to="/" class="text-gray-500 hover:text-gray-300 transition-colors text-xs">← Back to RemoteArena</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const toc = [
  { num: '01', id: 'intermediary',   title: 'Platform Purpose & Intermediary Status' },
  { num: '02', id: 'remote-only',    title: 'Remote-Only & No Immigration Services' },
  { num: '03', id: 'eligibility',    title: 'User Eligibility & Registration' },
  { num: '04', id: 'prohibited',     title: 'Prohibited Activities' },
  { num: '05', id: 'content',        title: 'User Content & Representations' },
  { num: '06', id: 'availability',   title: 'Platform Availability Disclaimer' },
  { num: '07', id: 'liability',      title: 'Limitation of Liability' },
  { num: '08', id: 'interactions',   title: 'User-to-User Interactions' },
  { num: '09', id: 'indemnification',title: 'Indemnification' },
  { num: '10', id: 'termination',    title: 'Termination & Suspension' },
  { num: '11', id: 'governing-law',  title: 'Governing Law' },
  { num: '12', id: 'arbitration',    title: 'Mandatory Binding Arbitration' },
  { num: '13', id: 'class-action',   title: 'Class Action Waiver' },
  { num: '14', id: 'electronic',     title: 'Electronic Communications' },
  { num: '15', id: 'ip',             title: 'Intellectual Property' },
  { num: '16', id: 'privacy',        title: 'Data Privacy' },
  { num: '17', id: 'force-majeure',  title: 'Force Majeure' },
  { num: '18', id: 'fees',           title: 'Fees & Credits' },
  { num: '19', id: 'changes',        title: 'Changes to These Terms' },
  { num: '20', id: 'miscellaneous',  title: 'Miscellaneous' },
  { num: '21', id: 'contact',        title: 'Contact Information' },
]

const venueExclusions = [
  'Employ job seekers or contractors',
  'Recruit employees on behalf of employers',
  'Direct or supervise work',
  'Control work schedules or performance',
  'Select candidates for employers',
  'Guarantee engagements or placements',
  'Manage payroll or compensation',
  'Provide employee benefits or withhold taxes',
  'Function as an Employer of Record (EOR)',
  'Function as a PEO or staffing agency',
  'Function as a labor broker or manpower supplier',
  'Serve as an outsourcing employer',
]

const noRelationships = [
  'Employer–employee',
  'Joint-employer',
  'Co-employer',
  'Staffing relationship',
  'Agency relationship',
  'Fiduciary relationship',
  'Partnership',
  'Joint venture',
  'Labor broker',
]

const immigrationExclusions = [
  'Sponsor visas of any kind',
  'Petition for immigration benefits',
  'Arrange work permits or relocation',
  'Facilitate labor migration',
  'Provide immigration legal advice',
  'Review immigration eligibility',
  'Verify work authorization documentation',
  'Assist with consular processing',
  'Participate in H-1B, L-1, O-1, TN, or EB programs',
  'Provide international placement with physical relocation',
]

const complianceLaws = [
  'Labor laws',
  'Tax laws',
  'Immigration laws',
  'Data privacy laws',
  'Employment laws',
  'Payroll regulations',
  'Export-control laws',
  'Worker-classification laws',
]

const prohibitedUses = [
  'Identity theft or corporate impersonation',
  'Resume fraud or credential falsification',
  'Fake job postings or profile spoofing',
  'Phishing, scamming, or payment fraud',
  'Money laundering or financial crimes',
  'Human trafficking or forced labor practices',
  'Unauthorized data harvesting or scraping',
  'Malware distribution or cyberattacks',
  'Harassment or abusive conduct toward users',
  'Circumventing rate limits or security measures',
  'Using automated bots without authorization',
  'Collecting users\' personal data without consent',
]

const contentDisclaimers = [
  'Candidate qualifications',
  'Employer legitimacy',
  'Company solvency',
  'Work quality',
  'Compensation arrangements',
  'Contract enforceability',
  'User identity authenticity',
  'Background check accuracy',
]

const noGuarantees = [
  'Successful hiring or placement',
  'Candidate availability',
  'Platform uptime or continuity',
  'Data preservation',
  'Communication delivery',
  'Payment completion',
  'Background check accuracy',
  'Job listing accuracy',
]

const liabilityExclusions = [
  'Hiring decisions',
  'Employment disputes',
  'Contractor disputes',
  'Payroll disputes',
  'Tax liabilities',
  'Immigration matters',
  'Worker classification disputes',
  'Workplace injuries',
  'Breach of contract between users',
  'Fraud by users',
  'Lost profits or business opportunities',
  'Indirect or consequential damages',
  'Punitive damages',
  'Emotional distress',
  'Data loss',
  'Reputational harm',
]

const indemnificationItems = [
  'Your conduct on the Platform',
  'Breach of these Terms',
  'Violation of applicable laws',
  'Employment-related disputes',
  'Tax or payroll disputes',
  'Immigration matters',
  'Intellectual property violations',
  'Fraudulent conduct',
  'Contracts between Users',
  'Worker misclassification claims',
  'Regulatory investigations',
  'Third-party claims arising from your Content',
]

const forceMajeureItems = [
  'Internet outages',
  'Cyberattacks',
  'Government actions',
  'Natural disasters',
  'War or conflict',
  'Pandemics',
  'Infrastructure failures',
  'Labor disruptions',
]
</script>

<style scoped>
.section-heading {
  @apply text-xl font-bold text-white mb-6 pb-3 border-b border-gray-800/80 flex items-center gap-2.5 scroll-mt-8;
}
.section-heading::before {
  content: '';
  @apply w-1 h-6 bg-gradient-to-b from-violet-400 to-primary-500 rounded-full inline-block flex-shrink-0;
}
.subsection-heading {
  @apply text-base font-semibold text-gray-200 mb-3;
}
.subsection-block {
  @apply pl-0;
}
.body-text {
  @apply text-sm text-gray-400 leading-relaxed;
}
.list-items {
  @apply space-y-2.5;
}
.list-items li {
  @apply flex gap-3 text-sm text-gray-400 leading-relaxed;
}
.list-items li::before {
  content: '→';
  @apply text-violet-500 flex-shrink-0 mt-0.5;
}
.callout-box {
  @apply border rounded-xl p-5;
}
</style>
