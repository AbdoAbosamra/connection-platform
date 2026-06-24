<template>
  <div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-white">Notifications</h2>
      <button
        v-if="store.unread > 0"
        class="text-sm text-primary-400 hover:text-primary-300"
        @click="store.markAllRead()"
      >
        Mark all as read
      </button>
    </div>

    <!-- Loading skeletons -->
    <div v-if="store.loading && store.items.length === 0" class="space-y-3">
      <div v-for="n in 5" :key="n" class="h-16 rounded-xl bg-white/[0.04] animate-pulse" />
    </div>

    <!-- Empty -->
    <div
      v-else-if="store.items.length === 0"
      class="text-center py-20 text-gray-500"
    >
      <BellSlashIcon class="w-10 h-10 mx-auto mb-3 opacity-40" />
      <p>No notifications yet.</p>
    </div>

    <!-- Feed -->
    <ul v-else class="space-y-2">
      <li
        v-for="n in store.items"
        :key="n.id"
        class="flex items-start gap-3 rounded-xl border border-gray-800 px-4 py-3.5 transition-colors"
        :class="n.read_at ? 'bg-gray-900/40' : 'bg-primary-500/[0.06] border-primary-500/20'"
      >
        <span
          class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0"
          :class="n.read_at ? 'bg-gray-700' : 'bg-primary-400'"
        />
        <div class="min-w-0 flex-1">
          <p class="text-sm text-gray-200">{{ label(n) }}</p>
          <p class="text-[11px] text-gray-500 mt-0.5">{{ formatDate(n.created_at) }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            v-if="!n.read_at"
            class="text-xs text-gray-400 hover:text-white"
            @click="store.markRead(n.id)"
          >
            Mark read
          </button>
          <button
            class="text-gray-600 hover:text-rose-400"
            aria-label="Delete notification"
            @click="store.remove(n.id)"
          >
            <TrashIcon class="w-4 h-4" />
          </button>
        </div>
      </li>
    </ul>

    <button
      v-if="store.hasMore"
      class="mt-5 w-full py-2.5 text-sm text-gray-400 hover:text-white border border-gray-800 rounded-xl"
      :disabled="store.loading"
      @click="store.loadMore()"
    >
      {{ store.loading ? 'Loading…' : 'Load more' }}
    </button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { BellSlashIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useNotificationsStore } from '@/stores/notifications'

const store = useNotificationsStore()

const LABELS = {
  application_received: (d) => `${d.seeker_name ?? 'Someone'} applied to "${d.job_title ?? 'your job'}"`,
  application_status_updated: (d) => `Your application for "${d.job_title ?? 'a job'}" is now ${(d.status ?? '').replace(/_/g, ' ')}`,
  interview_scheduled: (d) => `Interview scheduled for "${d.job_title ?? 'a position'}"`,
  interview_rescheduled: (d) => `Interview rescheduled for "${d.job_title ?? 'a position'}"`,
  interview_confirmed: (d) => `Candidate confirmed an interview for "${d.job_title ?? 'a position'}"`,
  interview_cancelled: (d) => `An interview for "${d.job_title ?? 'a position'}" was cancelled`,
  report_submitted: (d) => `New ${d.reason ?? ''} report on a ${d.reportable_type ?? 'item'}`,
}

function label(n) {
  const data = n.data ?? {}
  const fn = LABELS[data.type]
  return fn ? fn(data) : (data.message ?? 'You have a new notification')
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleString()
}

onMounted(() => store.fetch(1))
</script>
