/**
 * useNotificationsStore
 *
 * Drives the in-app notification bell:
 *  - paginated notification feed
 *  - unread badge count
 *  - mark-one / mark-all read, delete
 *  - background polling (same cadence as messaging)
 *
 * The feed is intentionally lightweight: it reads Laravel database notifications
 * whose `data` payload carries a `type` discriminator the UI maps to a label/icon.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'
import { notificationsApi } from '@/api/notifications'
import { getEcho, isRealtimeEnabled } from '@/realtime/echo'

export const useNotificationsStore = defineStore('notifications', () => {
  const auth = useAuthStore()

  const items = ref([])
  const pagination = ref(null)
  const unread = ref(0)
  const loading = ref(false)
  const error = ref(null)

  let pollTimer = null
  const POLL_INTERVAL = 30_000 // 30 s — well within the 120/min budget

  const hasMore = computed(
    () => pagination.value && pagination.value.current_page < pagination.value.last_page
  )

  async function fetch(page = 1) {
    if (!auth.isAuthenticated) return
    loading.value = page === 1
    error.value = null
    try {
      const { data } = await notificationsApi.list(page)
      items.value = page === 1 ? data.data : [...items.value, ...data.data]
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        total: data.total,
      }
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Failed to load notifications.'
    } finally {
      loading.value = false
    }
  }

  async function loadMore() {
    if (!hasMore.value || loading.value) return
    await fetch(pagination.value.current_page + 1)
  }

  async function fetchUnreadCount() {
    if (!auth.isAuthenticated) return
    try {
      const { data } = await notificationsApi.unreadCount()
      unread.value = data.unread ?? 0
    } catch {
      // non-critical
    }
  }

  async function markRead(id) {
    const item = items.value.find((n) => n.id === id)
    if (item && !item.read_at) {
      item.read_at = new Date().toISOString()
      unread.value = Math.max(0, unread.value - 1)
    }
    try {
      await notificationsApi.markRead(id)
    } catch {
      // best-effort; next poll reconciles
    }
  }

  async function markAllRead() {
    items.value.forEach((n) => {
      n.read_at = n.read_at ?? new Date().toISOString()
    })
    unread.value = 0
    try {
      await notificationsApi.markAllRead()
    } catch {
      // best-effort
    }
  }

  async function remove(id) {
    const item = items.value.find((n) => n.id === id)
    if (item && !item.read_at) unread.value = Math.max(0, unread.value - 1)
    items.value = items.value.filter((n) => n.id !== id)
    try {
      await notificationsApi.remove(id)
    } catch {
      // best-effort
    }
  }

  let realtimeBound = false

  function startPolling() {
    stopPolling()
    fetchUnreadCount()
    pollTimer = setInterval(fetchUnreadCount, POLL_INTERVAL)
    startRealtime()
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  /**
   * Subscribe to the user's private channel for live notifications. No-op when
   * realtime is disabled — polling above keeps the badge fresh either way.
   */
  function startRealtime() {
    if (realtimeBound || !isRealtimeEnabled() || !auth.user?.id) return
    const echo = getEcho()
    if (!echo) return
    realtimeBound = true
    echo.private(`App.Models.User.${auth.user.id}`).notification((payload) => {
      unread.value += 1
      items.value.unshift({
        id: payload.id ?? `rt-${Date.now()}`,
        data: payload,
        read_at: null,
        created_at: new Date().toISOString(),
      })
    })
  }

  function reset() {
    stopPolling()
    realtimeBound = false
    items.value = []
    pagination.value = null
    unread.value = 0
    loading.value = false
    error.value = null
  }

  return {
    items,
    pagination,
    unread,
    loading,
    error,
    hasMore,
    fetch,
    loadMore,
    fetchUnreadCount,
    markRead,
    markAllRead,
    remove,
    startPolling,
    stopPolling,
    reset,
  }
})
