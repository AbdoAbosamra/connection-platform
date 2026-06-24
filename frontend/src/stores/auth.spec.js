import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

// Mock the router so the store can be imported without a real Vue app.
vi.mock('@/router', () => ({
  default: {
    push: vi.fn(),
    currentRoute: { value: { query: {} } },
  },
}))

// Mock the auth API module.
vi.mock('@/api/auth', () => ({
  authApi: {
    register: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
  },
}))

import { useAuthStore } from '@/stores/auth'
import { authApi } from '@/api/auth'

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('starts unauthenticated when no user is loaded', () => {
    const auth = useAuthStore()
    expect(auth.isAuthenticated).toBe(false)
  })

  it('maps roles to the correct dashboard path', () => {
    const auth = useAuthStore()
    auth.user = { role: 'employer' }
    expect(auth.dashboardPath()).toBe('/employer/dashboard')
    auth.user = { role: 'job_seeker' }
    expect(auth.dashboardPath()).toBe('/job-seeker/dashboard')
    auth.user = { role: 'admin' }
    expect(auth.dashboardPath()).toBe('/admin/dashboard')
    auth.user = null
    expect(auth.dashboardPath()).toBe('/')
  })

  it('persists token to localStorage on login', async () => {
    authApi.login.mockResolvedValue({ data: { token: 'abc123', user: { id: 1, role: 'admin' } } })
    const auth = useAuthStore()
    await auth.login({ email: 'a@b.com', password: 'x' })
    expect(localStorage.getItem('token')).toBe('abc123')
    expect(auth.isAuthenticated).toBe(true)
    expect(auth.isAdmin).toBe(true)
  })

  it('only clears the session on a 401 from fetchMe, not on network errors', async () => {
    const auth = useAuthStore()
    auth.token = 'tok'
    localStorage.setItem('token', 'tok')

    authApi.me.mockRejectedValueOnce({ response: { status: 500 } })
    await auth.fetchMe()
    expect(localStorage.getItem('token')).toBe('tok') // preserved

    authApi.me.mockRejectedValueOnce({ response: { status: 401 } })
    await auth.fetchMe()
    expect(localStorage.getItem('token')).toBeNull() // cleared
  })
})
