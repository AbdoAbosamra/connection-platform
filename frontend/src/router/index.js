import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ── Public ──────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        { path: '',       name: 'home',    component: () => import('@/pages/Home.vue') },
        { path: 'jobs',   name: 'jobs',    component: () => import('@/pages/jobs/JobSearch.vue') },
        { path: 'jobs/:slug', name: 'job', component: () => import('@/pages/jobs/JobDetail.vue') },
        // Find Professionals directory
        { path: 'professionals',      name: 'professionals',        component: () => import('@/pages/professionals/ProfessionalSearch.vue') },
        { path: 'professionals/:id',  name: 'professional.detail',  component: () => import('@/pages/professionals/ProfessionalDetail.vue') },
        // Public pricing page
        { path: 'pricing', name: 'pricing', component: () => import('@/pages/Pricing.vue') },
        // About + FAQ — trust infrastructure for visitors evaluating the platform
        { path: 'about', name: 'about', component: () => import('@/pages/About.vue') },
        { path: 'faq',   name: 'faq',   component: () => import('@/pages/FAQ.vue') },
        // Legal pages — public, no auth required, inherit PublicLayout navbar + footer
        { path: 'privacy', name: 'privacy', component: () => import('@/pages/PrivacyPolicy.vue') },
        { path: 'terms',   name: 'terms',   component: () => import('@/pages/TermsAndConditions.vue') },
        { path: 'community-guidelines', name: 'guidelines', component: () => import('@/pages/CommunityGuidelines.vue') },
      ],
    },

    // ── Auth ─────────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/layouts/AuthLayout.vue'),
      meta: { guest: true },
      children: [
        { path: 'login',    name: 'login',    component: () => import('@/pages/auth/Login.vue') },
        { path: 'register', name: 'register', component: () => import('@/pages/auth/Register.vue') },
        { path: 'forgot-password', name: 'forgot-password', component: () => import('@/pages/auth/ForgotPassword.vue') },
        { path: 'reset-password',  name: 'reset-password',  component: () => import('@/pages/auth/ResetPassword.vue') },
      ],
    },

    // ── Employer ─────────────────────────────────────────────────
    {
      path: '/employer',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { auth: true, role: 'employer' },
      children: [
        { path: 'dashboard',              name: 'employer.dashboard',     component: () => import('@/pages/employer/Dashboard.vue') },
        { path: 'profile',                name: 'employer.profile',       component: () => import('@/pages/employer/Profile.vue') },
        { path: 'jobs',                   name: 'employer.jobs',          component: () => import('@/pages/employer/Jobs.vue') },
        { path: 'jobs/new',               name: 'employer.jobs.create',   component: () => import('@/pages/employer/PostJob.vue') },
        { path: 'jobs/:id/edit',          name: 'employer.jobs.edit',     component: () => import('@/pages/employer/PostJob.vue') },
        { path: 'applications',           name: 'employer.applications',  component: () => import('@/pages/employer/Applications.vue') },
        { path: 'applications/:id',       name: 'employer.application',   component: () => import('@/pages/employer/ApplicationDetail.vue') },
        { path: 'messages',               name: 'employer.messages',      component: () => import('@/pages/shared/Messages.vue') },
        { path: 'billing',                name: 'employer.billing',       component: () => import('@/pages/employer/Billing.vue') },
        { path: 'verification',           name: 'employer.verification',  component: () => import('@/pages/employer/Verification.vue') },
        { path: 'notifications',          name: 'employer.notifications', component: () => import('@/pages/shared/Notifications.vue') },
      ],
    },

    // ── Job Seeker ───────────────────────────────────────────────
    {
      path: '/job-seeker',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { auth: true, role: 'job_seeker' },
      children: [
        { path: 'dashboard',          name: 'seeker.dashboard',      component: () => import('@/pages/jobseeker/Dashboard.vue') },
        { path: 'profile',            name: 'seeker.profile',        component: () => import('@/pages/jobseeker/Profile.vue') },
        { path: 'applications',       name: 'seeker.applications',   component: () => import('@/pages/jobseeker/Applications.vue') },
        { path: 'saved',              name: 'seeker.saved',          component: () => import('@/pages/jobseeker/SavedJobs.vue') },
        { path: 'messages',           name: 'seeker.messages',       component: () => import('@/pages/shared/Messages.vue') },
        { path: 'interviews',         name: 'seeker.interviews',     component: () => import('@/pages/jobseeker/Interviews.vue') },
        { path: 'notifications',      name: 'seeker.notifications',  component: () => import('@/pages/shared/Notifications.vue') },
      ],
    },

    // ── Admin ────────────────────────────────────────────────────
    {
      path: '/admin',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { auth: true, role: 'admin' },
      children: [
        { path: 'dashboard',      name: 'admin.dashboard',    component: () => import('@/pages/admin/Dashboard.vue') },
        { path: 'users',          name: 'admin.users',        component: () => import('@/pages/admin/Users.vue') },
        { path: 'users/:id',      name: 'admin.user',         component: () => import('@/pages/admin/UserDetail.vue') },
        { path: 'jobs',           name: 'admin.jobs',         component: () => import('@/pages/admin/Jobs.vue') },
        { path: 'reports',        name: 'admin.reports',      component: () => import('@/pages/admin/Reports.vue') },
        { path: 'analytics',      name: 'admin.analytics',    component: () => import('@/pages/admin/Analytics.vue') },
        { path: 'notifications',  name: 'admin.notifications', component: () => import('@/pages/shared/Notifications.vue') },
      ],
    },

    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/pages/NotFound.vue') },
  ],
})

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore()

  // Await auth initialization exactly once per app load
  await auth.init()

  // Authenticated users trying to reach guest-only pages → send to their dashboard
  if (to.meta.guest && auth.isAuthenticated) {
    return next(auth.dashboardPath())
  }

  // Unauthenticated users trying to reach protected pages → send to login
  if (to.meta.auth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Wrong role (admins bypass role checks so they can inspect any section)
  if (to.meta.role && auth.user?.role !== to.meta.role && auth.user?.role !== 'admin') {
    return next(auth.dashboardPath())
  }

  next()
})

export default router
