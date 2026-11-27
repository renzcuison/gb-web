<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import {
  ImsActivityClockIcon,
  ImsRefreshIcon,
  ImsCloseIcon,
} from '@/Components/Icons/ImsIcons.vue'

const page = usePage()
const recentActivities = computed(() => page.props.recent_activities || [])

const isRecentOpen = ref(false)
const isRefreshing = ref(false)
const lastSeenId = ref(null)
const containerRef = ref(null)

let pollInterval = null

const latestId = computed(() => recentActivities.value[0]?.id ?? null)

const hasNewActivity = computed(() => {
  if (!latestId.value || lastSeenId.value === null) return false
  return latestId.value > lastSeenId.value
})

const refreshActivities = () => {
  if (isRefreshing.value) return
  isRefreshing.value = true

  router.reload({
    only: ['recent_activities'],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      isRefreshing.value = false
      if (isRecentOpen.value && latestId.value) {
        lastSeenId.value = latestId.value
      }
    },
  })
}

const togglePopover = () => {
  isRecentOpen.value = !isRecentOpen.value
  if (isRecentOpen.value) {
    if (latestId.value) {
      lastSeenId.value = latestId.value
    }
    refreshActivities()
  }
}

watch(
  latestId,
  (val) => {
    if (lastSeenId.value === null && val) {
      lastSeenId.value = val
    }
  },
  { immediate: true }
)

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    isRecentOpen.value = false
  }
}

const handleKeydown = (event) => {
  if (event.key === 'Escape' && isRecentOpen.value) {
    isRecentOpen.value = false
  }
}

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    refreshActivities()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('visibilitychange', handleVisibilityChange)

  pollInterval = setInterval(() => {
    if (document.visibilityState === 'visible') {
      refreshActivities()
    }
  }, 20000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  clearInterval(pollInterval)
})
</script>

<template>
  <div ref="containerRef" class="relative [&_*]:!transition-none">
    <button
      @click="togglePopover"
      title="Recent Activities"
      aria-label="Recent Activities"
      class="relative flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 border border-slate-200 dark:border-zinc-700/80"
    >
      <ImsActivityClockIcon class="h-4 w-4 text-slate-900 dark:text-zinc-100" />

      <span
        v-if="hasNewActivity"
        class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-slate-900 dark:bg-zinc-100 ring-2 ring-white dark:ring-zinc-900"
      />
    </button>

    <div
      v-if="isRecentOpen"
      class="absolute left-0 mt-2 w-96 max-w-[calc(100vw-2rem)] rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-lg z-50 overflow-hidden text-slate-900 dark:text-zinc-100"
    >
      <div class="flex items-center justify-between px-4 pt-3.5 pb-2">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
          Recent Activities
        </span>
        <div class="flex items-center gap-2.5">
          <button
            @click="refreshActivities"
            :disabled="isRefreshing"
            title="Refresh"
            class="text-slate-900 dark:text-zinc-100 disabled:opacity-40"
          >
            <ImsRefreshIcon class="h-3.5 w-3.5" />
          </button>
          <button
            @click="isRecentOpen = false"
            title="Close"
            class="text-slate-900 dark:text-zinc-100"
          >
            <ImsCloseIcon class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <div class="max-h-96 overflow-y-auto px-1.5 pb-2 space-y-0.5">
        <Link
          v-for="activity in recentActivities"
          :key="activity.id"
          :href="activity.href || '/inventory/movements'"
          @click="isRecentOpen = false"
          class="flex items-start justify-between gap-3 px-3 py-2.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800/60"
        >
          <div class="min-w-0 flex-1">
            <p class="text-[13px] font-medium text-slate-900 dark:text-zinc-100">
              {{ activity.description || activity.title }}
            </p>
            <div class="flex items-center justify-between mt-0.5 text-xs text-slate-500 dark:text-zinc-400">
              <span>{{ activity.user_name || 'System' }}</span>
              <span class="font-mono text-slate-400 dark:text-zinc-500">
                {{ activity.time || activity.created_at_human }}
              </span>
            </div>
          </div>

          <span
            v-if="activity.quantity"
            :class="[
              'font-mono text-xs font-semibold shrink-0',
              activity.type === 'in'
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-rose-600 dark:text-rose-400',
            ]"
          >
            {{ activity.type === 'in' ? '+' : '-' }}{{ activity.quantity }}
          </span>
        </Link>

        <div
          v-if="recentActivities.length === 0"
          class="px-4 py-10 text-center text-xs text-slate-400 dark:text-zinc-500"
        >
          No recent activity logged.
        </div>
      </div>
    </div>
  </div>
</template>