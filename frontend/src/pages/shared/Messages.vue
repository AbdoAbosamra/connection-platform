<template>
  <div class="flex h-[calc(100vh-7rem)] gap-5">

    <!-- ── Conversations sidebar ───────────────────────────────────────────── -->
    <aside class="w-72 flex-shrink-0 card flex flex-col overflow-hidden">

      <!-- Header -->
      <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
          </svg>
          <h2 class="font-bold text-sm text-gray-900">Messages</h2>
        </div>
        <!-- Global unread badge -->
        <span v-if="store.totalUnread > 0"
          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-rose-500 text-white rounded-full">
          {{ store.totalUnread > 99 ? '99+' : store.totalUnread }}
        </span>
      </div>

      <!-- Initial load skeleton -->
      <div v-if="store.loadingConversations" class="flex-1 overflow-y-auto divide-y divide-gray-50">
        <div v-for="i in 4" :key="i" class="flex items-center gap-3 px-4 py-3.5 animate-pulse">
          <div class="w-9 h-9 rounded-xl bg-gray-200 flex-shrink-0" />
          <div class="flex-1 space-y-1.5">
            <div class="h-3 bg-gray-200 rounded w-3/4" />
            <div class="h-2.5 bg-gray-100 rounded w-1/2" />
          </div>
        </div>
      </div>

      <!-- Error state -->
      <div v-else-if="store.convError" class="flex-1 flex flex-col items-center justify-center gap-2 p-6 text-center">
        <svg class="w-8 h-8 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <p class="text-xs text-rose-600">{{ store.convError }}</p>
        <button @click="store.fetchConversations()" class="text-xs text-primary-600 font-semibold hover:underline">Retry</button>
      </div>

      <!-- Conversation list -->
      <ul v-else class="flex-1 overflow-y-auto divide-y divide-gray-50">
        <li
          v-for="conv in store.conversations"
          :key="conv.id"
          @click="selectConv(conv)"
          :class="[
            'flex items-center gap-3 px-4 py-3.5 cursor-pointer transition-all duration-150 relative',
            store.activeConversation?.id === conv.id
              ? 'bg-primary-50 border-r-2 border-primary-500'
              : 'hover:bg-gray-50',
          ]"
        >
          <!-- Avatar -->
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0 relative">
            {{ otherPartyInitial(conv) }}
            <!-- Unread dot -->
            <span v-if="conv.unread_count > 0"
              class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 rounded-full text-white text-[9px] font-bold flex items-center justify-center shadow">
              {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
            </span>
          </div>

          <!-- Text -->
          <div class="flex-1 min-w-0">
            <p :class="['text-sm truncate', conv.unread_count > 0 ? 'font-bold text-gray-900' : 'font-semibold text-gray-700']">
              {{ otherPartyName(conv) }}
            </p>
            <p v-if="conv.job" class="text-[10px] text-primary-500 font-medium truncate">
              Re: {{ conv.job.title }}
            </p>
            <p class="text-xs text-gray-400 truncate mt-0.5">
              {{ conv.latest_message?.body ?? 'No messages yet' }}
            </p>
          </div>

          <!-- Time -->
          <span v-if="conv.last_message_at" class="text-[10px] text-gray-300 flex-shrink-0 self-start mt-0.5">
            {{ formatDate(conv.last_message_at) }}
          </span>
        </li>

        <!-- Empty state -->
        <li v-if="!store.conversations.length" class="flex flex-col items-center justify-center gap-2 p-8 text-center">
          <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-1">
            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-700">No conversations yet</p>
          <p class="text-xs text-gray-400">Messages will appear here once started</p>
        </li>
      </ul>
    </aside>

    <!-- ── Chat window ─────────────────────────────────────────────────────── -->
    <div class="flex-1 card flex flex-col overflow-hidden">

      <template v-if="store.activeConversation">

        <!-- Chat header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3 bg-gray-50/60 flex-shrink-0">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0">
            {{ otherPartyInitial(store.activeConversation) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-sm text-gray-900 truncate">{{ otherPartyName(store.activeConversation) }}</p>
            <p v-if="store.activeConversation.job" class="text-xs text-primary-500 truncate">
              Re: {{ store.activeConversation.job.title }}
            </p>
          </div>
          <!-- Polling indicator: subtle animated dot when background refresh is running -->
          <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse flex-shrink-0" title="Live updates on" />
        </div>

        <!-- "Load older messages" button -->
        <div v-if="store.hasOlderMessages" class="flex justify-center py-2 border-b border-gray-50 flex-shrink-0">
          <button
            @click="loadOlder"
            :disabled="store.loadingMore"
            class="text-xs text-primary-600 font-semibold hover:underline disabled:opacity-50 flex items-center gap-1.5"
          >
            <svg v-if="store.loadingMore" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ store.loadingMore ? 'Loading…' : 'Load older messages' }}
          </button>
        </div>

        <!-- Messages scroll area -->
        <div ref="chatEl" class="flex-1 overflow-y-auto p-5 space-y-4">

          <!-- Loading skeleton (switching convs) -->
          <template v-if="store.loadingMessages">
            <div v-for="i in 5" :key="i" :class="['flex', i % 2 === 0 ? 'justify-end' : 'justify-start']">
              <div :class="['rounded-2xl px-4 py-3 animate-pulse', i % 2 === 0 ? 'bg-primary-100 w-48' : 'bg-gray-100 w-56']">
                <div class="h-3 bg-gray-200 rounded w-full mb-1.5" />
                <div class="h-3 bg-gray-200 rounded w-2/3" />
              </div>
            </div>
          </template>

          <!-- Message bubbles -->
          <template v-else>
            <div
              v-for="msg in store.messages"
              :key="msg.id"
              :class="['flex items-end gap-2', isMine(msg) ? 'justify-end' : 'justify-start']"
            >
              <!-- Other party avatar (first char) -->
              <div v-if="!isMine(msg)"
                class="w-7 h-7 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold flex-shrink-0 mb-0.5">
                {{ msg.sender?.name?.[0] ?? '?' }}
              </div>

              <!-- Bubble -->
              <div class="max-w-sm space-y-1">
                <div
                  :class="[
                    'px-4 py-2.5 rounded-2xl text-sm leading-relaxed',
                    isMine(msg)
                      ? 'bg-gradient-to-r from-primary-600 to-violet-600 text-white rounded-br-sm shadow-lg shadow-primary-500/20'
                      : 'bg-gray-100 text-gray-900 rounded-bl-sm',
                    msg._failed ? '!bg-rose-100 !text-rose-700' : '',
                    msg._pending ? 'opacity-70' : '',
                  ]"
                >
                  <!-- XSS safe: rendered as text, not v-html -->
                  <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>

                  <!-- Attachment -->
                  <a v-if="msg.attachment_url"
                    :href="msg.attachment_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    :class="[
                      'flex items-center gap-1.5 mt-2 text-xs font-medium underline',
                      isMine(msg) ? 'text-white/80 hover:text-white' : 'text-primary-600 hover:text-primary-700',
                    ]"
                  >
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                    </svg>
                    Attachment
                  </a>

                  <!-- Timestamp + state -->
                  <div :class="['flex items-center gap-1 mt-1 justify-end', isMine(msg) ? 'text-white/50' : 'text-gray-400']">
                    <span class="text-[10px]">{{ formatTime(msg.created_at) }}</span>
                    <!-- Pending spinner -->
                    <svg v-if="msg._pending" class="animate-spin w-2.5 h-2.5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <!-- Read tick (double tick if read_at is set and it's mine) -->
                    <svg v-else-if="isMine(msg) && msg.read_at" class="w-3 h-3 text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <!-- Failed indicator -->
                    <button v-if="msg._failed" @click="store.dismissFailedMessage(msg.id)"
                      class="text-[10px] text-rose-500 underline ml-1">dismiss</button>
                  </div>
                </div>

                <!-- Failed retry hint -->
                <p v-if="msg._failed" class="text-[10px] text-rose-500 text-right">
                  Failed to send — tap dismiss and try again
                </p>
              </div>
            </div>
          </template>

          <!-- Message area error -->
          <div v-if="store.msgError" class="flex justify-center">
            <div class="bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 text-xs text-rose-600 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
              </svg>
              {{ store.msgError }}
            </div>
          </div>
        </div>

        <!-- Send error banner -->
        <div v-if="store.sendError"
          class="mx-4 mb-2 px-3 py-2 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-600 flex items-center justify-between">
          {{ store.sendError }}
          <button @click="store.sendError = null" class="text-rose-400 hover:text-rose-600 ml-2 flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Input bar -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/60 flex-shrink-0">
          <!-- Attachment preview -->
          <div v-if="attachment" class="flex items-center gap-2 mb-2 bg-primary-50 border border-primary-100 rounded-xl px-3 py-2">
            <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
            </svg>
            <span class="text-xs text-primary-700 font-medium truncate flex-1">{{ attachment.name }}</span>
            <button @click="clearAttachment" class="text-primary-400 hover:text-primary-600 flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="flex gap-2 items-end">
            <!-- Hidden file input -->
            <input ref="fileInput" type="file" class="hidden"
              accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg,.gif,.webp"
              @change="onFileSelect"
            />

            <!-- Attach button -->
            <button @click="fileInput?.click()"
              class="flex-shrink-0 w-10 h-10 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:text-primary-600 hover:border-primary-300 hover:bg-primary-50 transition-all"
              title="Attach file"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
              </svg>
            </button>

            <!-- Text input — Shift+Enter for newline, Enter to send -->
            <textarea
              ref="textareaEl"
              v-model="newMessage"
              rows="1"
              class="input flex-1 !py-2.5 resize-none overflow-hidden"
              placeholder="Type a message… (Enter to send)"
              style="min-height: 40px; max-height: 120px;"
              @keydown.enter.exact.prevent="handleSend"
              @input="autoResize"
            />

            <!-- Send button -->
            <button
              @click="handleSend"
              :disabled="store.sending || (!newMessage.trim() && !attachment)"
              class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-r from-primary-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
            >
              <svg v-if="store.sending" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
              </svg>
            </button>
          </div>
        </div>
      </template>

      <!-- No conversation selected -->
      <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-3">
        <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center">
          <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
          </svg>
        </div>
        <p class="font-semibold text-gray-700">Select a conversation</p>
        <p class="text-sm text-gray-400">Choose from the list on the left to start messaging</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useMessagesStore } from '@/stores/messages'

const auth  = useAuthStore()
const store = useMessagesStore()

// Local refs
const chatEl     = ref(null)   // scroll container
const newMessage = ref('')
const attachment = ref(null)   // File | null
const fileInput  = ref(null)   // hidden <input type="file">
const textareaEl = ref(null)   // message textarea (template ref — never document.querySelector)

// ── Helpers ────────────────────────────────────────────────────────────────

function isMine(msg) {
  return msg.sender_id === auth.user?.id
}

function otherPartyName(conv) {
  if (auth.isEmployer) return conv.job_seeker?.user?.name ?? 'Job Seeker'
  return conv.employer?.company_name ?? 'Employer'
}

function otherPartyInitial(conv) {
  return otherPartyName(conv)?.[0]?.toUpperCase() ?? '?'
}

function formatTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function formatDate(d) {
  if (!d) return ''
  const date = new Date(d)
  const now  = new Date()
  const diff = now - date
  if (diff < 86_400_000) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  if (diff < 604_800_000) return date.toLocaleDateString([], { weekday: 'short' })
  return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
}

// ── Attachment ─────────────────────────────────────────────────────────────

const MAX_ATTACHMENT = 10 * 1024 * 1024 // 10 MB — matches backend

function onFileSelect(e) {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > MAX_ATTACHMENT) {
    store.sendError = `File too large — maximum is 10 MB (your file is ${(file.size / 1024 / 1024).toFixed(1)} MB).`
    e.target.value = ''
    return
  }
  attachment.value  = file
  store.sendError   = null
  e.target.value    = ''   // reset so same file can be re-selected
}

function clearAttachment() {
  attachment.value = null
}

// ── Auto-resize textarea ───────────────────────────────────────────────────

function autoResize(e) {
  const el = e.target
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

// ── Conversation selection ─────────────────────────────────────────────────

async function selectConv(conv) {
  await store.selectConversation(conv)
  await nextTick()
  scrollToBottom()
}

// ── Sending ────────────────────────────────────────────────────────────────

async function handleSend() {
  const body = newMessage.value.trim()
  if (!body && !attachment.value) return
  if (store.sending) return

  const file = attachment.value
  newMessage.value = ''
  attachment.value = null
  // Reset textarea height via template ref — never document.querySelector
  await nextTick()
  if (textareaEl.value) textareaEl.value.style.height = 'auto'

  // Pass null (not ' ') when attachment-only — store handles the null body correctly
  await store.sendMessage(body || null, file)
  await nextTick()
  scrollToBottom('smooth')
}

// ── Scroll helpers ─────────────────────────────────────────────────────────

function scrollToBottom(behavior = 'auto') {
  if (chatEl.value) {
    chatEl.value.scrollTo({ top: chatEl.value.scrollHeight, behavior })
  }
}

// Auto-scroll when new messages arrive (only if already near bottom)
watch(
  () => store.messages.length,
  async () => {
    if (!chatEl.value) return
    const { scrollTop, scrollHeight, clientHeight } = chatEl.value
    const nearBottom = scrollHeight - scrollTop - clientHeight < 180
    if (nearBottom) {
      await nextTick()
      scrollToBottom('smooth')
    }
  }
)

// ── Load older (pagination) ────────────────────────────────────────────────

async function loadOlder() {
  const prevHeight = chatEl.value?.scrollHeight ?? 0
  await store.loadOlderMessages()
  // Restore scroll position so the view doesn't jump to top
  await nextTick()
  if (chatEl.value) {
    chatEl.value.scrollTop = chatEl.value.scrollHeight - prevHeight
  }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────

onMounted(async () => {
  await store.fetchConversations()
  await store.fetchUnreadCount()
  store.startPolling()

  // If another page (ProfessionalDetail, ApplicationDetail) pre-seeded a
  // conversation via openToConversation(), load its messages now and scroll down.
  if (store.activeConversation && store.messages.length === 0) {
    await store.ensureMessagesLoaded()
    await nextTick()
    scrollToBottom()
  }
})

onUnmounted(() => {
  store.stopPolling()
  // Don't reset — store persists so badge + list remain correct when navigating away
})
</script>
