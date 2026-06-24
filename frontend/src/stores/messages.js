/**
 * useMessagesStore
 *
 * Central store for the messaging system. Handles:
 *  - Conversation list with unread counts
 *  - Active conversation + paginated message history
 *  - Sending with optimistic updates + rollback on failure
 *  - Polling for new messages / unread badge
 *  - Page-level loading vs. background refresh states
 *  - Error surfaces per operation
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'
import { messagesApi } from '@/api/messages'
import { getEcho, isRealtimeEnabled, leaveChannel } from '@/realtime/echo'

export const useMessagesStore = defineStore('messages', () => {
  const auth = useAuthStore()

  // ── State ─────────────────────────────────────────────────────────────────

  const conversations        = ref([])       // loaded conversation list
  const convPagination       = ref(null)     // { current_page, last_page, … }
  const activeConversation   = ref(null)     // currently open Conversation object
  const messages             = ref([])       // messages in the active conversation
  const msgPagination        = ref(null)     // { current_page, last_page, … }
  const totalUnread          = ref(0)        // nav badge count

  const loadingConversations = ref(false)    // initial conversations fetch
  const loadingMessages      = ref(false)    // switching conversations
  const loadingMore          = ref(false)    // "load older messages" pagination
  const sending              = ref(false)    // send button state

  const convError            = ref(null)     // error string for conversation list
  const msgError             = ref(null)     // error string for chat window
  const sendError            = ref(null)     // per-send error (shown inline)

  let   pollTimer            = null          // setInterval handle
  const POLL_INTERVAL        = 15_000        // 15 s — 2 calls/poll × 4/min = 8 req/min vs the 120/min budget

  // ── Computed ──────────────────────────────────────────────────────────────

  const role = computed(() => auth.user?.role)

  const hasOlderMessages = computed(() =>
    msgPagination.value && msgPagination.value.current_page < msgPagination.value.last_page
  )

  const hasOlderConversations = computed(() =>
    convPagination.value &&
    convPagination.value.current_page < convPagination.value.last_page
  )

  // ── Helpers ───────────────────────────────────────────────────────────────

  function _mergeMessages(incoming) {
    // Deduplicate by id — handles poll overlap and optimistic updates
    const existing = new Set(messages.value.map((m) => m.id))
    const fresh    = incoming.filter((m) => !existing.has(m.id))
    messages.value.push(...fresh)
  }

  function _upsertConversation(conv) {
    const idx = conversations.value.findIndex((c) => c.id === conv.id)
    if (idx >= 0) {
      conversations.value[idx] = { ...conversations.value[idx], ...conv }
    } else {
      conversations.value.unshift(conv)
    }
  }

  function _setActiveUnread(newCount) {
    if (!activeConversation.value) return
    const idx = conversations.value.findIndex((c) => c.id === activeConversation.value.id)
    if (idx < 0) return
    const prev = conversations.value[idx].unread_count ?? 0
    const cleared = prev - newCount  // how many messages just got marked read
    conversations.value[idx] = { ...conversations.value[idx], unread_count: newCount }
    // Decrement the nav badge by exactly how many messages we cleared.
    totalUnread.value = Math.max(0, totalUnread.value - cleared)
  }

  // ── Conversations ─────────────────────────────────────────────────────────

  async function fetchConversations(page = 1) {
    if (!role.value) return
    loadingConversations.value = page === 1
    convError.value            = null
    try {
      const { data } = await messagesApi.conversations(role.value, page)
      if (page === 1) {
        conversations.value = data.data
      } else {
        conversations.value.push(...data.data)
      }
      convPagination.value = {
        current_page: data.current_page,
        last_page:    data.last_page,
        total:        data.total,
      }
    } catch (err) {
      convError.value = err.response?.data?.message ?? 'Failed to load conversations.'
    } finally {
      loadingConversations.value = false
    }
  }

  async function fetchUnreadCount() {
    if (!role.value) return
    try {
      const { data } = await messagesApi.unreadCount(role.value)
      totalUnread.value = data.unread ?? 0
    } catch {
      // Non-critical — badge just won't update until next poll
    }
  }

  // ── Active conversation + messages ────────────────────────────────────────

  async function selectConversation(conv) {
    if (activeConversation.value?.id === conv.id) return  // already open

    activeConversation.value = conv
    messages.value           = []
    msgPagination.value      = null
    msgError.value           = null
    sendError.value          = null

    await _fetchMessages(conv.id, 1)

    // Optimistically zero the unread badge for this conversation
    _setActiveUnread(0)

    _subscribeRealtime(conv.id)
  }

  // ── Realtime (Echo) ─────────────────────────────────────────────────────────
  // Progressive enhancement: when Reverb is configured, incoming messages arrive
  // instantly over the conversation's private channel. Otherwise polling covers it.
  let _rtConvId = null

  function _subscribeRealtime(convId) {
    if (!isRealtimeEnabled()) return
    const echo = getEcho()
    if (!echo) return
    _unsubscribeRealtime()
    _rtConvId = convId
    echo.private(`conversation.${convId}`).listen('.message.sent', ({ message }) => {
      if (!message || message.conversation_id !== activeConversation.value?.id) return
      // Dedupe against optimistic/own messages; merge the rest.
      _mergeMessages([message])
      _upsertConversation({
        id: message.conversation_id,
        last_message_at: message.created_at,
        latest_message: message,
      })
    })
  }

  function _unsubscribeRealtime() {
    if (_rtConvId) {
      leaveChannel(`conversation.${_rtConvId}`)
      _rtConvId = null
    }
  }

  async function _fetchMessages(convId, page) {
    loadingMessages.value = page === 1
    loadingMore.value     = page > 1
    msgError.value        = null
    try {
      const { data } = await messagesApi.messages(role.value, convId, page)

      if (page === 1) {
        messages.value = data.data
      } else {
        // Prepend older messages (page 2 = older)
        messages.value = [...data.data, ...messages.value]
      }

      msgPagination.value = {
        current_page: data.current_page,
        last_page:    data.last_page,
        total:        data.total,
      }
    } catch (err) {
      msgError.value = err.response?.data?.message ?? 'Failed to load messages.'
    } finally {
      loadingMessages.value = false
      loadingMore.value     = false
    }
  }

  async function loadOlderMessages() {
    if (!activeConversation.value || !hasOlderMessages.value || loadingMore.value) return
    const nextPage = msgPagination.value.current_page + 1
    await _fetchMessages(activeConversation.value.id, nextPage)
  }

  // ── Sending ───────────────────────────────────────────────────────────────

  /**
   * Send a message with OPTIMISTIC UPDATE.
   *
   * 1. Immediately inject a temporary message into the list (pending state).
   * 2. Call the API.
   * 3. On success: replace the temp message with the real server response.
   * 4. On failure: remove the temp message and surface the error.
   *
   * @param {string}  body       Message text
   * @param {File|null} attachment  Optional file attachment
   */
  async function sendMessage(body, attachment = null) {
    // Require at least a non-empty body OR an attachment.
    // Never send a whitespace-only body — it stores ' ' in the DB and breaks previews.
    const trimmedBody = body?.trim() ?? ''
    if (!activeConversation.value || (!trimmedBody && !attachment)) return
    // Normalise: empty string when attachment-only so backend can apply
    // required_without:attachment validation cleanly.
    body = trimmedBody || null

    sendError.value = null
    sending.value   = true

    // Build temp (optimistic) message
    const tempId  = `temp-${Date.now()}`
    const tempMsg = {
      id:          tempId,
      conversation_id: activeConversation.value.id,
      sender_id:   auth.user.id,
      sender:      { id: auth.user.id, name: auth.user.name, avatar: auth.user.avatar ?? null },
      body:        body ?? '',  // null when attachment-only; display as empty string
      attachment:  null,
      attachment_url: attachment ? URL.createObjectURL(attachment) : null,
      read_at:     null,
      created_at:  new Date().toISOString(),
      _pending:    true,   // flag for UI spinner
      _failed:     false,
    }
    messages.value.push(tempMsg)

    try {
      const { data } = await messagesApi.send(
        role.value,
        activeConversation.value.id,
        { body, attachment }
      )

      // Replace temp with real message
      const idx = messages.value.findIndex((m) => m.id === tempId)
      if (idx >= 0) {
        messages.value[idx] = data.message
      }

      // Bump conversation to top of list with latest preview
      _upsertConversation({
        id:             activeConversation.value.id,
        last_message_at: data.message.created_at,
        latest_message:  data.message,
        unread_count:    0,
      })
    } catch (err) {
      // Mark the temp message as failed (UI shows retry affordance)
      const idx = messages.value.findIndex((m) => m.id === tempId)
      if (idx >= 0) {
        messages.value[idx] = { ...messages.value[idx], _pending: false, _failed: true }
      }
      sendError.value = err.response?.data?.message
        ?? (err.response?.status === 429
          ? 'Sending too fast — please wait a moment.'
          : 'Message failed to send. Tap to retry.')
    } finally {
      sending.value = false
    }
  }

  /** Remove a failed temp message so the user can re-type. */
  function dismissFailedMessage(tempId) {
    messages.value = messages.value.filter((m) => m.id !== tempId)
  }

  /**
   * Pre-seed a conversation as "active" WITHOUT loading its messages yet.
   * Called by ProfessionalDetail / ApplicationDetail after `initiate` returns —
   * the Messages page then calls `ensureMessagesLoaded()` on mount to finish.
   */
  function openToConversation(conv) {
    _upsertConversation(conv)
    activeConversation.value = conv
    messages.value           = []   // signal to Messages.vue that loading is needed
    msgPagination.value      = null
    msgError.value           = null
    sendError.value          = null
  }

  /**
   * If a conversation is active but its messages haven't been fetched yet
   * (e.g. after openToConversation()), fetch page 1 now.
   * Safe to call on every mount — is a no-op if messages are already loaded.
   */
  async function ensureMessagesLoaded() {
    if (!activeConversation.value) return
    _subscribeRealtime(activeConversation.value.id)
    if (messages.value.length > 0) return
    await _fetchMessages(activeConversation.value.id, 1)
  }

  // ── Polling ───────────────────────────────────────────────────────────────

  /**
   * Start the background poll.
   * Every POLL_INTERVAL ms:
   *  - Refreshes the first page of conversations (updates unread counts + previews)
   *  - If a conversation is open, fetches the latest messages and deduplicates
   *  - Refreshes the global unread count for the nav badge
   */
  function startPolling() {
    stopPolling()
    pollTimer = setInterval(_poll, POLL_INTERVAL)
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  async function _poll() {
    if (!role.value) return

    try {
      // Refresh conversation list silently (no loading flag)
      const { data: convData } = await messagesApi.conversations(role.value, 1)
      if (convData?.data) {
        // Merge in updated unread counts + latest message previews
        convData.data.forEach(_upsertConversation)
        // Re-calculate total unread from fresh data
        totalUnread.value = convData.data.reduce((sum, c) => sum + (c.unread_count ?? 0), 0)
      }
    } catch {
      // silent — network hiccup, will retry on next tick
    }

    // Refresh messages in the active conversation
    if (activeConversation.value) {
      try {
        const { data: msgData } = await messagesApi.messages(
          role.value,
          activeConversation.value.id,
          1
        )
        if (msgData?.data) {
          _mergeMessages(msgData.data)
        }
      } catch {
        // silent
      }
    }
  }

  // ── Cleanup ───────────────────────────────────────────────────────────────

  function reset() {
    stopPolling()
    _unsubscribeRealtime()
    conversations.value        = []
    convPagination.value       = null
    activeConversation.value   = null
    messages.value             = []
    msgPagination.value        = null
    totalUnread.value          = 0
    loadingConversations.value = false
    loadingMessages.value      = false
    loadingMore.value          = false
    sending.value              = false
    convError.value            = null
    msgError.value             = null
    sendError.value            = null
  }

  return {
    // state
    conversations,
    convPagination,
    activeConversation,
    messages,
    msgPagination,
    totalUnread,
    loadingConversations,
    loadingMessages,
    loadingMore,
    sending,
    convError,
    msgError,
    sendError,
    // computed
    role,
    hasOlderMessages,
    hasOlderConversations,
    // actions
    fetchConversations,
    fetchUnreadCount,
    selectConversation,
    openToConversation,
    ensureMessagesLoaded,
    loadOlderMessages,
    sendMessage,
    dismissFailedMessage,
    startPolling,
    stopPolling,
    reset,
  }
})
