import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@/api/reports', () => ({
  reportsApi: { create: vi.fn().mockResolvedValue({ data: {} }) },
}))

import ReportModal from '@/components/ReportModal.vue'
import { reportsApi } from '@/api/reports'

describe('ReportModal', () => {
  beforeEach(() => vi.clearAllMocks())

  it('does not render its dialog when closed', () => {
    const wrapper = mount(ReportModal, { props: { modelValue: false, type: 'job', id: 1 } })
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
  })

  it('submits a report with the selected reason and shows confirmation', async () => {
    const wrapper = mount(ReportModal, { props: { modelValue: true, type: 'job', id: 42 } })

    await wrapper.find('select').setValue('scam')
    await wrapper.find('form').trigger('submit.prevent')
    await new Promise((r) => setTimeout(r, 0))

    expect(reportsApi.create).toHaveBeenCalledWith(
      expect.objectContaining({ type: 'job', id: 42, reason: 'scam' })
    )
    expect(wrapper.text()).toContain('submitted for review')
  })

  it('surfaces a server error message', async () => {
    reportsApi.create.mockRejectedValueOnce({ response: { data: { message: 'Already reported' } } })
    const wrapper = mount(ReportModal, { props: { modelValue: true, type: 'user', id: 7 } })

    await wrapper.find('form').trigger('submit.prevent')
    await new Promise((r) => setTimeout(r, 0))

    expect(wrapper.text()).toContain('Already reported')
  })
})
