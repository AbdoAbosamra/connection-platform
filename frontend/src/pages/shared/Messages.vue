<template>
  <div class="flex h-[calc(100vh-7rem)] gap-5">
    <!-- Conversations sidebar -->
    <div class="w-72 flex-shrink-0 card flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="px-4 py-3.5 border-b border-gray-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
        </svg>
        <h2 class="font-bold text-sm text-gray-900">Messages</h2>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex-1 flex items-center justify-center">
        <svg class="animate-spin w-5 h-5 text-primary-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Conversation list -->
      <ul v-else class="flex-1 overflow-y-auto divide-y divide-gray-50">
        <li
          v-for="conv in conversations"
          :key="conv.id"
          @click="selectConversation(conv)"
          :class="[
            'flex items-center gap-3 px-4 py-3.5 cursor-pointer transition-all duration-150',
            selected?.id === conv.id
              ? 'bg-primary-50 border-r-2 border-primary-500'
              : 'hover:bg-gray-50',
          ]"
        >
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0">
            {{ otherPartyName(conv)?.[0] }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-900 truncate">{{ otherPartyName(conv) }}</p>
            <p class="text-xs text-gray-400 truncate mt-0.5">{{ conv.latest_message?.body }}</p>
          </div>
        </li>

        <li v-if="!conversations.length" class="p-8 text-center text-sm text-gray-400">
          No conversations yet
        </li>
      </ul>
    </div>

    <!-- Chat window -->
    <div class="flex-1 card flex flex-col overflow-hidden">
      <!-- Active conversation -->
      <template v-if="selected">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3 bg-gray-50/60">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-violet-100 flex items-center justify-center text-primary-700 font-bold text-sm flex-shrink-0">
            {{ otherPartyName(selected)?.[0] }}
          </div>
          <div>
            <p class="font-bold text-sm text-gray-900">{{ otherPartyName(selected) }}</p>
            <p v-if="selected.job" class="text-xs text-gray-400">Re: {{ selected.job?.title }}</p>
          </div>
        </div>

        <!-- Messages -->
        <div ref="chatEl" class="flex-1 overflow-y-auto p-5 space-y-4">
          <div
            v-for="msg in messages"
            :key="msg.id"
            :class="['flex', msg.sender_id === myUserId ? 'justify-end' : 'justify-start']"
          >
            <div
              :class="[
                'max-w-sm px-4 py-2.5 rounded-2xl text-sm leading-relaxed',
                msg.sender_id === myUserId
                  ? 'bg-gradient-to-r from-primary-600 to-violet-600 text-white rounded-br-sm shadow-lg shadow-primary-500/20'
                  : 'bg-gray-100 text-gray-900 rounded-bl-sm',
              ]"
            >
              {{ msg.body }}
              <p :class="['text-[10px] mt-1 text-right', msg.sender_id === myUserId ? 'opacity-60' : 'text-gray-400']">
                {{ formatTime(msg.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/60 flex gap-3">
          <input
            v-model="newMessage"
            class="input flex-1 !py-3"
            placeholder="Type a message…"
            @keydown.enter="send"
          />
          <button
            @click="send"
            class="btn-primary !px-5"
            :disabled="!newMessage.trim()"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
          </button>
        </div>
      </template>

      <!-- No conversation selected -->
      <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-3">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-900">Select a conversation</p>
        <p class="text-xs text-gray-400">Choose from the list to start messaging</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { messagesApi } from '@/api/messages'

const auth          = useAuthStore()
const loading       = ref(true)
const conversations = ref([])
const selected      = ref(null)
const messages      = ref([])
const newMessage    = ref('')
const chatEl        = ref(null)

const role     = computed(() => auth.user?.role)
const myUserId = computed(() => auth.user?.id)

function otherPartyName(conv) {
  if (auth.isEmployer) return conv.job_seeker?.user?.name
  return conv.employer?.company_name
}

async function selectConversation(conv) {
  selected.value   = conv
  const { data }   = await messagesApi.messages(role.value, conv.id)
  messages.value   = data.data
  await nextTick()
  chatEl.value?.scrollTo({ top: chatEl.value.scrollHeight })
}

async function send() {
  if (!newMessage.value.trim()) return
  const { data }   = await messagesApi.send(role.value, selected.value.id, { body: newMessage.value })
  messages.value.push(data.message)
  newMessage.value = ''
  await nextTick()
  chatEl.value?.scrollTo({ top: chatEl.value.scrollHeight, behavior: 'smooth' })
}

function formatTime(d) {
  return new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  const { data }     = await messagesApi.conversations(role.value)
  conversations.value = data.data
  loading.value       = false
})
</script>
