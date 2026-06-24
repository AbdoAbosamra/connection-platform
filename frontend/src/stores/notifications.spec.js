import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

vi.mock('@/api/notifications', () => ({
  notificationsApi: {
    list: vi.fn(),
    unreadCount: vi.fn(),
    markRead: vi.fn().mockResolvedValue({}),
    markAllRead: vi.fn().mockResolvedValue({}),
    remove: vi.fn().mockResolvedValue({}),
  },
}))

// The store depends on the auth store for the isAuthenticated guard.
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ isAuthenticated: true }),
}))

import { useNotificationsStore } from '@/stores/notifications'
import { notificationsApi } from '@/api/notifications'

describe('notifications store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('loads the feed and tracks pagination', async () => {
    notificationsApi.list.mockResolvedValue({
      data: { data: [{ id: 'a', read_at: null }, { id: 'b', read_at: null }], current_page: 1, last_page: 2, total: 4 },
    })
    const store = useNotificationsStore()
    await store.fetch(1)

    expect(store.items).toHaveLength(2)
    expect(store.hasMore).toBe(true)
  })

  it('marking one read decrements the unread counter optimistically', async () => {
    notificationsApi.list.mockResolvedValue({
      data: { data: [{ id: 'a', read_at: null }], current_page: 1, last_page: 1, total: 1 },
    })
    const store = useNotificationsStore()
    await store.fetch(1)
    store.unread = 1

    await store.markRead('a')

    expect(store.items[0].read_at).not.toBeNull()
    expect(store.unread).toBe(0)
    expect(notificationsApi.markRead).toHaveBeenCalledWith('a')
  })

  it('mark all read zeroes the counter', async () => {
    notificationsApi.list.mockResolvedValue({
      data: { data: [{ id: 'a', read_at: null }, { id: 'b', read_at: null }], current_page: 1, last_page: 1, total: 2 },
    })
    const store = useNotificationsStore()
    await store.fetch(1)
    store.unread = 2

    await store.markAllRead()
    expect(store.unread).toBe(0)
    expect(store.items.every((n) => n.read_at)).toBe(true)
  })

  it('removing an unread item decrements the counter and drops it', async () => {
    notificationsApi.list.mockResolvedValue({
      data: { data: [{ id: 'a', read_at: null }], current_page: 1, last_page: 1, total: 1 },
    })
    const store = useNotificationsStore()
    await store.fetch(1)
    store.unread = 1

    await store.remove('a')
    expect(store.items).toHaveLength(0)
    expect(store.unread).toBe(0)
  })
})
