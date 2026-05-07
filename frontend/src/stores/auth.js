import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(null)
  const token = ref(localStorage.getItem('token') ?? null)

  const isAuthenticated = computed(() => !!token.value)
  const isEmployer      = computed(() => user.value?.role === 'employer')
  const isJobSeeker     = computed(() => user.value?.role === 'job_seeker')
  const isAdmin         = computed(() => user.value?.role === 'admin')

  async function register(payload) {
    const { data } = await authApi.register(payload)
    setSession(data.token, data.user)
    redirectToDashboard()
  }

  async function login(payload) {
    const { data } = await authApi.login(payload)
    setSession(data.token, data.user)
    redirectToDashboard()
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const { data } = await authApi.me()
      user.value = data.user
    } catch {
      clearSession()
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
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
  }

  function redirectToDashboard() {
    const role = user.value?.role
    if (role === 'employer')   router.push('/employer/dashboard')
    else if (role === 'job_seeker') router.push('/job-seeker/dashboard')
    else if (role === 'admin') router.push('/admin/dashboard')
  }

  return {
    user, token,
    isAuthenticated, isEmployer, isJobSeeker, isAdmin,
    register, login, logout, fetchMe,
  }
})
