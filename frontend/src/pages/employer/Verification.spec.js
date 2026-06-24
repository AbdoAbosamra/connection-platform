import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@/api/verification', () => ({
  verificationApi: {
    status: vi.fn(),
    payment: vi.fn(),
    linkedInUrl: vi.fn(),
  },
}))
vi.mock('vue-router', () => ({ useRoute: () => ({ query: {} }) }))
vi.mock('@/stores/auth', () => ({ useAuthStore: () => ({ user: { employer_profile: { is_verified: false } } }) }))

import Verification from '@/pages/employer/Verification.vue'
import { verificationApi } from '@/api/verification'

const stubs = { CheckBadgeIcon: true, ExclamationTriangleIcon: true, CreditCardIcon: true, RouterLink: true }

describe('employer Verification page', () => {
  beforeEach(() => vi.clearAllMocks())

  it('shows verification options when unverified', async () => {
    verificationApi.status.mockResolvedValue({
      data: { is_verified: false, methods_available: { linkedin: true, payment: true } },
    })
    const wrapper = mount(Verification, { global: { stubs } })
    await new Promise((r) => setTimeout(r, 0))

    expect(wrapper.text()).toContain('Verify with LinkedIn')
    expect(wrapper.text()).toContain('Verify with a card')
  })

  it('verifies instantly via payment (mock gateway returns no checkout_url)', async () => {
    verificationApi.status.mockResolvedValue({
      data: { is_verified: false, methods_available: { linkedin: false, payment: true } },
    })
    verificationApi.payment.mockResolvedValue({ data: { is_verified: true, checkout_url: null, message: 'Your company is now verified.' } })
    const wrapper = mount(Verification, { global: { stubs } })
    await new Promise((r) => setTimeout(r, 0))

    await wrapper.findAll('button').find((b) => b.text().includes('Verify with payment')).trigger('click')
    await new Promise((r) => setTimeout(r, 0))

    expect(verificationApi.payment).toHaveBeenCalled()
    expect(wrapper.text()).toContain('verified')
  })

  it('shows the verified state when already verified', async () => {
    verificationApi.status.mockResolvedValue({
      data: { is_verified: true, method: 'domain', methods_available: {} },
    })
    const wrapper = mount(Verification, { global: { stubs } })
    await new Promise((r) => setTimeout(r, 0))

    expect(wrapper.text()).toContain('Your company is verified')
  })
})
