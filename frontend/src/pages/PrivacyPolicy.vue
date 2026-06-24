<template>
  <div class="min-h-screen bg-gray-950 text-gray-300">

    <!-- Hero banner -->
    <div class="relative bg-gradient-to-br from-gray-900 via-primary-950/60 to-gray-950 border-b border-gray-800/60 py-16">
      <div class="absolute inset-0 opacity-[0.04]"
        style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:40px 40px" />
      <div class="relative max-w-3xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 bg-primary-500/10 border border-primary-500/20 text-primary-400 text-xs font-semibold px-4 py-2 rounded-full mb-6">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
          </svg>
          Legal Document
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Privacy Policy</h1>
        <p class="text-gray-400 text-sm">
          Last updated: <span class="text-gray-300 font-medium">June 1, 2025</span>
          &nbsp;·&nbsp;
          Effective: <span class="text-gray-300 font-medium">June 1, 2025</span>
        </p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-6 py-16 space-y-12">

      <!-- Intro -->
      <section>
        <div class="bg-primary-500/5 border border-primary-500/15 rounded-2xl p-6 text-sm text-gray-300 leading-relaxed">
          RemoteArena Inc. ("<strong class="text-white">RemoteArena</strong>", "<strong class="text-white">we</strong>", "<strong class="text-white">us</strong>", or "<strong class="text-white">our</strong>") operates the RemoteArena platform at <span class="text-primary-400">remotearena.io</span> (the "<strong class="text-white">Platform</strong>"). This Privacy Policy explains what personal data we collect, why we collect it, how we use and protect it, and your rights regarding that data. By using our Platform you agree to the practices described here.
        </div>
      </section>

      <!-- 1 -->
      <section>
        <h2 class="section-heading">1. Information We Collect</h2>
        <div class="space-y-6">

          <div>
            <h3 class="subsection-heading">1.1 Account & Registration Data</h3>
            <p class="body-text">When you register you provide: full name, email address, password (hashed — never stored in plain text), and account role (Employer or Job Seeker). Employers additionally provide a company name.</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.2 Job Seeker Profile Data</h3>
            <p class="body-text">To make your profile visible to employers you may provide: professional headline, biography, current city and country, nationality, years of experience, experience level, desired job title, desired salary range, skills, LinkedIn URL, GitHub URL, portfolio URL, and a resume file (PDF or Word document).</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.3 Employer Profile Data</h3>
            <p class="body-text">Employers may provide: company description, industry, company size, headquarters location, company website, LinkedIn URL, Twitter/X URL, year founded, and a company logo image.</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.4 Job Postings</h3>
            <p class="body-text">Information contained in job listings you publish, including job title, description, salary range, required skills, and employment type.</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.5 Application Data</h3>
            <p class="body-text">When a job seeker applies for a position we collect the cover letter, expected salary, and a resume snapshot (a copy of the resume at the time of application, separate from the profile resume).</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.6 Messaging Data</h3>
            <p class="body-text">Messages exchanged between employers and job seekers through our platform, including message body text, timestamps, and any file attachments you upload (PDFs, images, documents up to 10 MB).</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.7 Usage & Technical Data</h3>
            <p class="body-text">We automatically collect: IP address, browser type and version, device type, operating system, pages visited, timestamps of access, and error logs. We use this data to maintain platform security and improve the service.</p>
          </div>

          <div>
            <h3 class="subsection-heading">1.8 Authentication Tokens</h3>
            <p class="body-text">We use bearer tokens (via Laravel Sanctum) to authenticate your requests. These tokens are stored in your browser's local storage. We do not use tracking cookies for advertising purposes.</p>
          </div>
        </div>
      </section>

      <!-- 2 -->
      <section>
        <h2 class="section-heading">2. How We Use Your Information</h2>
        <ul class="list-items">
          <li><span class="text-primary-400 font-semibold">Platform operation:</span> To create and manage your account, display your profile, publish job listings, process applications, and facilitate messaging between employers and job seekers.</li>
          <li><span class="text-primary-400 font-semibold">Matching & discovery:</span> Job seeker profiles (when marked complete) are shown to employers searching for candidates. Job listings are shown to job seekers based on their preferences and search queries.</li>
          <li><span class="text-primary-400 font-semibold">Communications:</span> To send transactional notifications (e.g., "you received a new application", "your application status changed"). We do not send unsolicited marketing emails without your consent.</li>
          <li><span class="text-primary-400 font-semibold">Security & fraud prevention:</span> To detect, investigate, and prevent abusive, fraudulent, or illegal activity.</li>
          <li><span class="text-primary-400 font-semibold">Platform improvement:</span> Aggregated, anonymised analytics to understand how users interact with the Platform and improve features.</li>
          <li><span class="text-primary-400 font-semibold">Legal compliance:</span> To comply with applicable laws, regulations, or valid legal requests.</li>
        </ul>
      </section>

      <!-- 3 -->
      <section>
        <h2 class="section-heading">3. How We Share Your Information</h2>
        <p class="body-text mb-4">We do <strong class="text-white">not</strong> sell your personal data. We share it only in the following circumstances:</p>
        <ul class="list-items">
          <li><span class="text-primary-400 font-semibold">Employers and Job Seekers:</span> Public job seeker profiles (those with <code class="code-inline">profile_complete = true</code>) are visible to registered employers. Employer profiles and job listings are visible to all users. Application data (cover letter, resume, profile) is shared with the employer who posted the job.</li>
          <li><span class="text-primary-400 font-semibold">Service providers:</span> We may share data with trusted third-party providers who help us operate the Platform (e.g., email delivery, file storage, analytics). These providers are contractually required to protect your data and may only use it to provide services to us.</li>
          <li><span class="text-primary-400 font-semibold">Legal requirements:</span> We may disclose your data if required by law, court order, or governmental authority, or to protect the rights, property, or safety of RemoteArena, our users, or the public.</li>
          <li><span class="text-primary-400 font-semibold">Business transfers:</span> In the event of a merger, acquisition, or sale of assets, your data may be transferred. We will notify you before your data is subject to a different privacy policy.</li>
        </ul>
      </section>

      <!-- 4 -->
      <section>
        <h2 class="section-heading">4. Data Retention</h2>
        <p class="body-text">We retain your personal data for as long as your account is active or as needed to provide services. Specifically:</p>
        <ul class="list-items">
          <li>Account data is retained until you delete your account.</li>
          <li>Application records are retained for 3 years after the application date to comply with employment record obligations.</li>
          <li>Message data is retained for 2 years.</li>
          <li>Uploaded files (resumes, attachments) are deleted when you remove them from your profile or when your account is deleted.</li>
          <li>Server logs and usage data are retained for 90 days.</li>
        </ul>
      </section>

      <!-- 5 -->
      <section>
        <h2 class="section-heading">5. Data Security</h2>
        <p class="body-text">We implement industry-standard technical and organisational measures to protect your data, including:</p>
        <ul class="list-items">
          <li>All passwords are hashed using bcrypt before storage — we cannot recover your plain-text password.</li>
          <li>All data in transit is encrypted via TLS (HTTPS).</li>
          <li>Authentication tokens are bearer tokens; our API does not use session cookies, reducing CSRF risk.</li>
          <li>File uploads are validated for type, size, and content before storage.</li>
          <li>Access to production data is restricted to authorised personnel only.</li>
        </ul>
        <p class="body-text mt-4">No system is 100% secure. If you believe your account has been compromised, please contact us immediately at <span class="text-primary-400">security@remotearena.io</span>.</p>
      </section>

      <!-- 6 -->
      <section>
        <h2 class="section-heading">6. Your Rights</h2>
        <p class="body-text mb-4">Depending on your jurisdiction (including the EU/EEA under GDPR, and California under CCPA), you may have the following rights:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="right in rights" :key="right.title" class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <h4 class="text-white text-sm font-semibold mb-1">{{ right.title }}</h4>
            <p class="text-gray-400 text-xs leading-relaxed">{{ right.desc }}</p>
          </div>
        </div>
        <p class="body-text mt-6">To exercise any of these rights, email <span class="text-primary-400">privacy@remotearena.io</span>. We will respond within 30 days. We may need to verify your identity before processing your request.</p>
      </section>

      <!-- 7 -->
      <section>
        <h2 class="section-heading">7. Cookies & Tracking Technologies</h2>
        <p class="body-text">We use minimal browser storage:</p>
        <ul class="list-items">
          <li><span class="text-primary-400 font-semibold">localStorage:</span> We store your authentication bearer token in <code class="code-inline">localStorage</code> to keep you logged in between sessions. This is cleared when you log out.</li>
          <li><span class="text-primary-400 font-semibold">No advertising cookies:</span> We do not use third-party advertising or tracking cookies.</li>
          <li><span class="text-primary-400 font-semibold">Analytics:</span> If we use analytics tools, they are configured to anonymise IP addresses and not share data with advertising networks.</li>
        </ul>
      </section>

      <!-- 8 -->
      <section>
        <h2 class="section-heading">8. International Data Transfers</h2>
        <p class="body-text">RemoteArena is a remote-work platform serving users globally. Your data may be processed in countries other than your own. Where we transfer data from the EEA, UK, or Switzerland, we rely on appropriate safeguards such as Standard Contractual Clauses or adequacy decisions.</p>
      </section>

      <!-- 9 -->
      <section>
        <h2 class="section-heading">9. Children's Privacy</h2>
        <p class="body-text">The Platform is not directed to individuals under 16 years of age. We do not knowingly collect personal data from children. If you believe a child has provided us with personal data, contact us at <span class="text-primary-400">privacy@remotearena.io</span> and we will delete it.</p>
      </section>

      <!-- 10 -->
      <section>
        <h2 class="section-heading">10. Changes to This Policy</h2>
        <p class="body-text">We may update this Privacy Policy from time to time. We will notify you of material changes by email or by a prominent notice on the Platform at least 14 days before the change takes effect. Continued use of the Platform after the effective date constitutes acceptance of the updated policy.</p>
      </section>

      <!-- 11 -->
      <section>
        <h2 class="section-heading">11. Contact Us</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-sm space-y-2">
          <p class="text-white font-semibold">RemoteArena Inc. — Data Controller</p>
          <p class="text-gray-400">📧 Privacy inquiries: <span class="text-primary-400">privacy@remotearena.io</span></p>
          <p class="text-gray-400">🔒 Security reports: <span class="text-primary-400">security@remotearena.io</span></p>
          <p class="text-gray-400">📮 General support: <span class="text-primary-400">support@remotearena.io</span></p>
        </div>
      </section>

      <!-- Footer link -->
      <div class="border-t border-gray-800/60 pt-8 flex items-center justify-between text-sm">
        <RouterLink to="/terms" class="text-primary-400 hover:text-primary-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
          </svg>
          Read our Terms &amp; Conditions →
        </RouterLink>
        <RouterLink to="/" class="text-gray-500 hover:text-gray-300 transition-colors text-xs">← Back to RemoteArena</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const rights = [
  {
    title: '📋 Right of Access',
    desc: 'Request a copy of the personal data we hold about you.',
  },
  {
    title: '✏️ Right of Rectification',
    desc: 'Ask us to correct inaccurate or incomplete data.',
  },
  {
    title: '🗑️ Right of Erasure',
    desc: 'Request deletion of your data ("right to be forgotten"), subject to legal retention obligations.',
  },
  {
    title: '📦 Right of Portability',
    desc: 'Receive your data in a structured, machine-readable format.',
  },
  {
    title: '🚫 Right to Object',
    desc: 'Object to processing based on legitimate interests or for direct marketing.',
  },
  {
    title: '⏸️ Right to Restrict',
    desc: 'Ask us to restrict processing of your data in certain circumstances.',
  },
]
</script>

<style scoped>
.section-heading {
  @apply text-xl font-bold text-white mb-5 pb-3 border-b border-gray-800/80 flex items-center gap-2;
}
.section-heading::before {
  content: '';
  @apply w-1 h-6 bg-gradient-to-b from-primary-400 to-violet-500 rounded-full inline-block flex-shrink-0;
}
.subsection-heading {
  @apply text-base font-semibold text-gray-200 mb-2;
}
.body-text {
  @apply text-sm text-gray-400 leading-relaxed;
}
.list-items {
  @apply space-y-3;
}
.list-items li {
  @apply flex gap-3 text-sm text-gray-400 leading-relaxed;
}
.list-items li::before {
  content: '→';
  @apply text-primary-500 flex-shrink-0 mt-0.5;
}
.code-inline {
  @apply font-mono text-xs bg-gray-800 text-primary-300 px-1.5 py-0.5 rounded;
}
</style>
