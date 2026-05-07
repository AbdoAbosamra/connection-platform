<template>
  <div class="flex h-[calc(100vh-7rem)] gap-4">
    <!-- Conversation list -->
    <div class="w-72 flex-shrink-0 card flex flex-col overflow-hidden">
      <div class="p-4 border-b font-semibold text-sm">Messages</div>
      <div v-if="loading" class="flex-1 flex items-center justify-center text-gray-400 text-sm">Loading…</div>
      <ul v-else class="flex-1 overflow-y-auto divide-y divide-gray-100">
        <li
          v-for="conv in conversations" :key="conv.id"
          @click="selectConversation(conv)"
          :class="['flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 transition-colors',
            selected?.id === conv.id ? 'bg-primary-50' : '']"
        >
          <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-semibold text-sm flex-shrink-0">
            {{ otherPartyName(conv)?.[0] }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate">{{ otherPartyName(conv) }}</p>
            <p class="text-xs text-gray-400 truncate">{{ conv.latest_message?.body }}</p>
          </div>
        </li>
      </ul>
    </div>

    <!-- Chat window -->
    <div class="flex-1 card flex flex-col overflow-hidden">
      <template v-if="selected">
        <div class="p-4 border-b flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-semibold text-sm">
            {{ otherPartyName(selected)?.[0] }}
          </div>
          <div>
            <p class="font-semibold text-sm">{{ otherPartyName(selected) }}</p>
            <p v-if="selected.job" class="text-xs text-gray-400">Re: {{ selected.job?.title }}</p>
          </div>
        </div>

        <div ref="chatEl" class="flex-1 overflow-y-auto p-4 space-y-3">
          <div v-for="msg in messages" :key="msg.id" :class="['flex', msg.sender_id === myUserId ? 'justify-end' : 'justify-start']">
            <div :class="['max-w-xs px-4 py-2 rounded-2xl text-sm',
              msg.sender_id === myUserId
                ? 'bg-primary-600 text-white rounded-br-none'
                : 'bg-gray-100 text-gray-900 rounded-bl-none']">
              {{ msg.body }}
              <p class="text-[10px] opacity-60 mt-1 text-right">{{ formatTime(msg.created_at) }}</p>
            </div>
          </div>
        </div>

        <div class="p-4 border-t flex gap-3">
          <input v-model="newMessage" class="input flex-1" placeholder="Type a message…" @keydown.enter="send" />
          <button @click="send" class="btn-primary" :disabled="!newMessage.trim()">Send</button>
        </div>
      </template>

      <div v-else class="flex-1 flex items-center justify-center text-gray-400">
        Select a conversation to start messaging
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { messagesApi } from '@/api/messages'

const auth        = useAuthStore()
const loading     = ref(true)
const conversations = ref([])
const selected    = ref(null)
const messages    = ref([])
const newMessage  = ref('')
const chatEl      = ref(null)

const role      = computed(() => auth.user?.role)
const myUserId  = computed(() => auth.user?.id)

function otherPartyName(conv) {
  if (auth.isEmployer) return conv.job_seeker?.user?.name
  return conv.employer?.company_name
}

async function selectConversation(conv) {
  selected.value  = conv
  const { data } = await messagesApi.messages(role.value, conv.id)
  messages.value  = data.data
  await nextTick()
  chatEl.value?.scrollTo({ top: chatEl.value.scrollHeight })
}

async function send() {
  if (!newMessage.value.trim()) return
  const { data } = await messagesApi.send(role.value, selected.value.id, { body: newMessage.value })
  messages.value.push(data.message)
  newMessage.value = ''
  await nextTick()
  chatEl.value?.scrollTo({ top: chatEl.value.scrollHeight, behavior: 'smooth' })
}

function formatTime(d) {
  return new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  const { data } = await messagesApi.conversations(role.value)
  conversations.value = data.data
  loading.value = false
})
</script>
