import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import router from '@/router'
import { disconnectEcho } from '@/realtime/echo'

export const useAuthStore = defineStore('auth', () => {
  const user        = ref(null)
  const token       = ref(localStorage.getItem('token') ?? null)
  const initialized = ref(false)

  // Base isAuthenticated on both token AND loaded user object.
  // A token alone is not proof of a valid session — it may have been revoked
  // server-side (suspended account, forced logout). The 401 interceptor in
  // client.js clears the token on the first failed request, but checking
  // user.value ensures the router guard doesn't treat a stale token as valid.
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isEmployer      = computed(() => user.value?.role === 'employer')
  const isJobSeeker     = computed(() => user.value?.role === 'job_seeker')
  const isAdmin         = computed(() => user.value?.role === 'admin')

  // Shared init promise so concurrent guards don't duplicate the fetchMe call
  let _initPromise = null

  async function init() {
    if (initialized.value) return
    if (_initPromise) return _initPromise
    _initPromise = fetchMe().finally(() => {
      initialized.value = true
    })
    return _initPromise
  }

  async function register(payload) {
    const { data } = await authApi.register(payload)
    setSession(data.token, data.user)
    // Mark as initialized immediately — we already have the user from the register response.
    // This prevents the router guard from calling fetchMe (and potentially wiping the session).
    initialized.value = true
    await navigateToDashboard()
  }

  async function login(payload) {
    const { data } = await authApi.login(payload)
    setSession(data.token, data.user)
    // Same: skip the redundant fetchMe in the router guard.
    initialized.value = true

    const redirect = router.currentRoute.value.query.redirect
    try {
      if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
        await router.push(redirect)
      } else {
        await navigateToDashboard()
      }
    } catch { /* navigation failures (e.g. NavigationDuplicated) are not errors */ }
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const { data } = await authApi.me()
      user.value = data.user
    } catch (err) {
      if (err.response?.status === 401) clearSession()
    }
  }

  async function logout() {
    try { await authApi.logout() } catch { /* ignore */ }
    clearSession()
    router.push('/login')
  }

  function setSession(t, u) {
    token.value = t
    user.value  = u
    localStorage.setItem('token', t)
  }

  function clearSession() {
    token.value       = null
    user.value        = null
    _initPromise      = null
    initialized.value = false
    localStorage.removeItem('token')
    // Tear down any realtime connection bound to the old token.
    disconnectEcho()
  }

  // Returns the dashboard path for the current user role
  function dashboardPath() {
    const role = user.value?.role
    if (role === 'employer')   return '/employer/dashboard'
    if (role === 'job_seeker') return '/job-seeker/dashboard'
    if (role === 'admin')      return '/admin/dashboard'
    return '/'
  }

  // Navigates to the dashboard and swallows router navigation errors
  async function navigateToDashboard() {
    try {
      await router.push(dashboardPath())
    } catch { /* e.g. NavigationDuplicated — not a real error */ }
  }

  return {
    user, token, initialized,
    isAuthenticated, isEmployer, isJobSeeker, isAdmin,
    init, register, login, logout, fetchMe, dashboardPath,
  }
})
