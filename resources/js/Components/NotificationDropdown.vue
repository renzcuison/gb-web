<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
  ImsBellIcon,
  ImsCheckDoubleIcon,
  ImsRefreshIcon,
  ImsCloseIcon,
  ImsStatusDangerIcon,
  ImsStatusWarningIcon,
  ImsStatusSuccessIcon,
  ImsStatusInfoIcon,
} from '@/Components/Icons/ImsIcons.vue'

const page = usePage()
const isOpen = ref(false)
const filterTab = ref('all')
const isRefreshing = ref(false)
const containerRef = ref(null)

let pollInterval = null

const notifications = computed(() => page.props.notifications || [])

const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.read_at).length
})

const filteredNotifications = computed(() => {
  if (filterTab.value === 'unread') {
    return notifications.value.filter((n) => !n.read_at)
  }
  return notifications.value
})

const isToday = (item) => {
  if (item.created_at) {
    const d = new Date(item.created_at)
    const now = new Date()
    return (
      d.getFullYear() === now.getFullYear() &&
      d.getMonth() === now.getMonth() &&
      d.getDate() === now.getDate()
    )
  }
  const human = (item.created_at_human || '').toLowerCase()
  return (
    human.includes('just now') ||
    human.includes('second') ||
    human.includes('minute') ||
    human.includes('hour') ||
    /^\d+[smh]$/.test(human.trim())
  )
}

const groupedNotifications = computed(() => {
  const newItems = []
  const todayItems = []
  const earlierItems = []

  for (const item of filteredNotifications.value) {
    if (!item.read_at) {
      newItems.push(item)
    } else if (isToday(item)) {
      todayItems.push(item)
    } else {
      earlierItems.push(item)
    }
  }

  return [
    { label: 'New', items: newItems },
    { label: 'Today', items: todayItems },
    { label: 'Earlier', items: earlierItems },
  ].filter((group) => group.items.length > 0)
})

const refreshNotifications = () => {
  if (isRefreshing.value) return
  isRefreshing.value = true

  router.reload({
    only: ['notifications', 'admin_badges'],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      isRefreshing.value = false
    },
  })
}

const togglePopover = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    refreshNotifications()
  }
}

const handleItemClick = (notification) => {
  if (!notification.read_at) {
    router.post(
      `/notifications/${notification.id}/read`,
      {},
      {
        preserveScroll: true,
        preserveState: true,
      }
    )
  }

  if (notification.link) {
    isOpen.value = false
    router.visit(notification.link)
  }
}

const markAllAsRead = () => {
  router.post(
    '/notifications/read-all',
    {},
    {
      preserveScroll: true,
      preserveState: true,
    }
  )
}

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

const handleKeydown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    refreshNotifications()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('visibilitychange', handleVisibilityChange)

  pollInterval = setInterval(() => {
    if (document.visibilityState === 'visible') {
      refreshNotifications()
    }
  }, 20000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  clearInterval(pollInterval)
})

const getTypeIcon = (type) => {
  switch (type) {
    case 'danger':
      return ImsStatusDangerIcon
    case 'warning':
      return ImsStatusWarningIcon
    case 'success':
      return ImsStatusSuccessIcon
    default:
      return ImsStatusInfoIcon
  }
}
</script>

<template>
  <div ref="containerRef" class="relative [&_*]:!transition-none [&_*]:!animate-none">
    <button
      @click="togglePopover"
      title="Notifications"
      aria-label="Notifications"
      class="relative flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 border border-slate-200 dark:border-zinc-700/80"
    >
      <ImsBellIcon class="h-4 w-4 text-slate-900 dark:text-zinc-100" />

      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white dark:ring-zinc-900"
      />
    </button>

    <div
      v-if="isOpen"
      class="fixed right-6 top-14 w-96 max-w-[calc(100vw-2rem)] rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-lg z-50 overflow-hidden text-slate-900 dark:text-zinc-100"
    >
      <div class="flex items-center justify-between px-4 pt-3.5 pb-2">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
          Notifications
        </span>

        <div class="flex items-center gap-2.5">
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            title="Mark all as read"
            class="flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            <ImsCheckDoubleIcon class="h-3.5 w-3.5 text-slate-900 dark:text-zinc-100" />
            <span>Mark all read</span>
          </button>

          <button
            @click="refreshNotifications"
            :disabled="isRefreshing"
            title="Refresh"
            class="text-slate-900 dark:text-zinc-100 disabled:opacity-40"
          >
            <ImsRefreshIcon class="h-3.5 w-3.5" />
          </button>

          <button
            @click="isOpen = false"
            aria-label="Close notifications"
            class="text-slate-900 dark:text-zinc-100"
          >
            <ImsCloseIcon class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <div class="flex items-center gap-1 px-4 pb-2">
        <button
          @click="filterTab = 'all'"
          class="px-2.5 py-1 rounded-md text-xs font-semibold"
          :class="
            filterTab === 'all'
              ? 'bg-slate-200 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100'
              : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-zinc-800/60 dark:hover:text-zinc-100 font-medium'
          "
        >
          All
        </button>
        <button
          @click="filterTab = 'unread'"
          class="px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5"
          :class="
            filterTab === 'unread'
              ? 'bg-slate-200 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100'
              : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-zinc-800/60 dark:hover:text-zinc-100 font-medium'
          "
        >
          <span>Unread</span>
          <span
            v-if="unreadCount > 0"
            class="font-mono text-xs font-semibold px-1.5 py-0.5 rounded bg-slate-200/80 text-slate-800 dark:bg-zinc-800 dark:text-zinc-200"
          >
            {{ unreadCount }}
          </span>
        </button>
      </div>

      <div class="max-h-96 overflow-y-auto px-1.5 pb-2 space-y-2">
        <div v-for="group in groupedNotifications" :key="group.label" class="space-y-0.5">
          <div class="px-3 pt-1.5 pb-0.5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
              {{ group.label }}
            </span>
          </div>

          <div
            v-for="item in group.items"
            :key="item.id"
            @click="handleItemClick(item)"
            class="flex items-start gap-2.5 px-3 py-2.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800/60 cursor-pointer"
          >
            <component
              :is="getTypeIcon(item.type)"
              class="h-4 w-4 shrink-0 mt-0.5 text-slate-900 dark:text-zinc-100"
            />

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 min-w-0">
                  <span
                    v-if="!item.read_at"
                    class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-zinc-100 shrink-0"
                  />
                  <p
                    class="text-[13px] truncate"
                    :class="
                      !item.read_at
                        ? 'font-semibold text-slate-900 dark:text-zinc-100'
                        : 'font-medium text-slate-700 dark:text-zinc-300'
                    "
                  >
                    {{ item.title }}
                  </p>
                </div>
                <span class="font-mono text-xs text-slate-400 dark:text-zinc-500 shrink-0">
                  {{ item.created_at_human }}
                </span>
              </div>

              <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 line-clamp-2">
                {{ item.message }}
              </p>
            </div>
          </div>
        </div>

        <div
          v-if="groupedNotifications.length === 0"
          class="px-4 py-10 text-center text-xs text-slate-400 dark:text-zinc-500"
        >
          No {{ filterTab === 'unread' ? 'unread' : '' }} notifications.
        </div>
      </div>
    </div>
  </div>
</template>