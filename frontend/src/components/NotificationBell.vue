<template>
  <div ref="root" class="relative">
    <!-- Bell button -->
    <button
      type="button"
      class="relative w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-white hover:bg-white/[0.06] transition-colors"
      :aria-label="`Notifications${store.unread > 0 ? `, ${store.unread} unread` : ''}`"
      aria-haspopup="true"
      :aria-expanded="open"
      @click="toggle"
    >
      <BellIcon class="w-5 h-5" />
      <span
        v-if="store.unread > 0"
        class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[16px] h-[16px] px-1 text-[9px] font-bold bg-rose-500 text-white rounded-full"
      >
        {{ store.unread > 99 ? '99+' : store.unread }}
      </span>
    </button>

    <!-- Dropdown panel -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="open"
        class="absolute right-0 mt-2 w-80 max-h-[28rem] overflow-hidden rounded-2xl bg-gray-900 border border-gray-800 shadow-2xl shadow-black/50 z-30 flex flex-col"
        role="menu"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
          <h3 class="text-sm font-semibold text-white">Notifications</h3>
          <button
            v-if="store.unread > 0"
            class="text-xs text-primary-400 hover:text-primary-300"
            @click="store.markAllRead()"
          >
            Mark all read
          </button>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="store.loading && store.items.length === 0" class="p-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-12 rounded-lg bg-white/[0.04] animate-pulse" />
          </div>

          <p
            v-else-if="store.items.length === 0"
            class="px-4 py-10 text-center text-sm text-gray-500"
          >
            You're all caught up 🎉
          </p>

          <ul v-else class="divide-y divide-gray-800/60">
            <li
              v-for="n in store.items"
              :key="n.id"
              class="px-4 py-3 hover:bg-white/[0.03] transition-colors cursor-pointer"
              :class="{ 'bg-primary-500/[0.06]': !n.read_at }"
              @click="onClick(n)"
            >
              <div class="flex items-start gap-3">
                <span
                  class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0"
                  :class="n.read_at ? 'bg-transparent' : 'bg-primary-400'"
                />
                <div class="min-w-0 flex-1">
                  <p class="text-sm text-gray-200 leading-snug">{{ label(n) }}</p>
                  <p class="text-[11px] text-gray-500 mt-0.5">{{ timeAgo(n.created_at) }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <RouterLink
          :to="notificationsRoute"
          class="block text-center text-xs text-gray-400 hover:text-white py-3 border-t border-gray-800"
          @click="open = false"
        >
          View all notifications
        </RouterLink>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { BellIcon } from '@heroicons/vue/24/outline'
import { useNotificationsStore } from '@/stores/notifications'
import { useAuthStore } from '@/stores/auth'

const store = useNotificationsStore()
const auth = useAuthStore()
const router = useRouter()

const open = ref(false)
const root = ref(null)

const notificationsRoute = computed(() => {
  if (auth.isAdmin) return '/admin/notifications'
  if (auth.isEmployer) return '/employer/notifications'
  return '/job-seeker/notifications'
})

// Human-readable label derived from the notification's `data.type` discriminator.
const LABELS = {
  application_received: (d) => `${d.seeker_name ?? 'Someone'} applied to "${d.job_title ?? 'your job'}"`,
  application_status_updated: (d) => `Your application for "${d.job_title ?? 'a job'}" is now ${prettify(d.status)}`,
  interview_scheduled: (d) => `Interview scheduled for "${d.job_title ?? 'a position'}"`,
  interview_rescheduled: (d) => `Interview rescheduled for "${d.job_title ?? 'a position'}"`,
  interview_confirmed: (d) => `Candidate confirmed the interview for "${d.job_title ?? 'a position'}"`,
  interview_cancelled: (d) => `An interview for "${d.job_title ?? 'a position'}" was cancelled`,
  report_submitted: (d) => `New ${d.reason ?? ''} report on a ${d.reportable_type ?? 'item'}`,
}

function prettify(s) {
  return (s ?? '').replace(/_/g, ' ')
}

function label(n) {
  const data = n.data ?? {}
  const fn = LABELS[data.type]
  return fn ? fn(data) : (data.message ?? 'You have a new notification')
}

function timeAgo(iso) {
  if (!iso) return ''
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  return `${Math.floor(hours / 24)}d ago`
}

async function toggle() {
  open.value = !open.value
  if (open.value) await store.fetch(1)
}

function onClick(n) {
  if (!n.read_at) store.markRead(n.id)
  const data = n.data ?? {}
  // Best-effort deep link based on the notification type.
  if (data.application_id) {
    open.value = false
    const base = auth.isEmployer ? '/employer/applications/' : '/job-seeker/applications/'
    router.push(base + data.application_id)
  } else if (data.type === 'report_submitted' && auth.isAdmin) {
    open.value = false
    router.push('/admin/reports')
  }
}

function onClickOutside(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false
}

onMounted(() => {
  document.addEventListener('click', onClickOutside)
  store.startPolling()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
  store.stopPolling()
})
</script>
