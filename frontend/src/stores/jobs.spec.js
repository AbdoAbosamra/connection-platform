import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

vi.mock('@/api/jobs', () => ({
  jobsApi: {
    list: vi.fn(),
    get: vi.fn(),
    save: vi.fn(),
    unsave: vi.fn(),
  },
}))

import { useJobsStore } from '@/stores/jobs'
import { jobsApi } from '@/api/jobs'

describe('jobs store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('populates jobs, pagination and facets from the search response', async () => {
    jobsApi.list.mockResolvedValue({
      data: {
        data: [{ id: 1, title: 'Rust Engineer' }],
        current_page: 1,
        last_page: 1,
        total: 1,
        facets: { category: { Engineering: 1 }, employment_type: { full_time: 1 }, experience_level: { senior: 1 } },
      },
    })

    const store = useJobsStore()
    await store.search({ q: 'Rust' })

    expect(store.jobs).toHaveLength(1)
    expect(store.pagination.total).toBe(1)
    expect(store.facets.category.Engineering).toBe(1)
  })

  it('strips blank/false/null filters before calling the API', async () => {
    jobsApi.list.mockResolvedValue({ data: { data: [], facets: {} } })
    const store = useJobsStore()

    await store.search({ q: 'php', category: '', visa: false, x: null, page: 1 })

    expect(jobsApi.list).toHaveBeenCalledWith({ q: 'php', page: 1 })
  })

  it('falls back to empty facets when the response omits them', async () => {
    jobsApi.list.mockResolvedValue({ data: { data: [] } })
    const store = useJobsStore()
    await store.search({})

    expect(store.facets).toEqual({ category: {}, employment_type: {}, experience_level: {} })
  })
})
