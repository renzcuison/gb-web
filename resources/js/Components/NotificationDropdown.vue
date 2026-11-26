<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import {
  Bell,
  CheckCheck,
  RotateCw,
  X,
  ArrowUpRight,
  AlertTriangle,
  Package,
  CheckCircle2,
  Info
} from 'lucide-vue-next'

const page = usePage()
const isOpen = ref(false)
const filterTab = ref('all')
const isRefreshing = ref(false)
const containerRef = ref(null)

let pollInterval = null

const notifications = computed(() => page.props.notifications || [])

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read_at).length
})

const filteredNotifications = computed(() => {
  if (filterTab.value === 'unread') {
    return notifications.value.filter(n => !n.read_at)
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
  ].filter(group => group.items.length > 0)
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

const markAsRead = (notification) => {
  if (!notification.read_at) {
    router.post(`/notifications/${notification.id}/read`, {}, {
      preserveScroll: true,
      preserveState: true
    })
  }
}

const markAllAsRead = () => {
  router.post('/notifications/read-all', {}, {
    preserveScroll: true,
    preserveState: true
  })
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

const getTypeStyle = (type) => {
  switch (type) {
    case 'danger':
      return {
        icon: Package,
        badge: 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
      }
    case 'warning':
      return {
        icon: AlertTriangle,
        badge: 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
      }
    case 'success':
      return {
        icon: CheckCircle2,
        badge: 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
      }
    default:
      return {
        icon: Info,
        badge: 'bg-sky-500/10 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
      }
  }
}
</script>

<template>
  <div ref="containerRef" class="relative">
    <button @click="togglePopover" title="Notifications" aria-label="Notifications"
      class="relative flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-zinc-300 bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 transition-colors border border-slate-200/80 dark:border-zinc-700/60">
      <Bell class="h-4 w-4 text-slate-500 dark:text-zinc-400" />

      <span v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 font-mono text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-zinc-900">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div v-if="isOpen"
      class="fixed right-4 top-14 w-96 max-w-[calc(100vw-2rem)] rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl z-50 overflow-hidden text-zinc-800 dark:text-zinc-200">
      <div class="px-4 pt-3.5 pb-2 flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
          Notifications
        </span>

        <div class="flex items-center gap-2.5">
          <button v-if="unreadCount > 0" @click="markAllAsRead" title="Mark all as read"
            class="flex items-center gap-1 text-[11px] font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 transition">
            <CheckCheck class="h-3.5 w-3.5" />
            <span>Read all</span>
          </button>

          <button @click="refreshNotifications" :disabled="isRefreshing" title="Refresh"
            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition disabled:opacity-50">
            <RotateCw class="h-3.5 w-3.5" :class="{ 'animate-spin': isRefreshing }" />
          </button>

          <button @click="isOpen = false" aria-label="Close notifications"
            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
            <X class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <div class="flex items-center gap-1 px-4 pb-2">
        <button @click="filterTab = 'all'" class="px-2.5 py-1 rounded-md text-[11px] font-medium transition" :class="filterTab === 'all'
          ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
          : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'">
          All
        </button>
        <button @click="filterTab = 'unread'"
          class="px-2.5 py-1 rounded-md text-[11px] font-medium transition flex items-center gap-1.5" :class="filterTab === 'unread'
            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
            : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'">
          <span>Unread</span>
          <span v-if="unreadCount > 0"
            class="font-mono text-[10px] px-1 rounded bg-rose-500/15 text-rose-600 dark:text-rose-400"
            :class="{ '!bg-rose-500 !text-white': filterTab === 'unread' }">
            {{ unreadCount }}
          </span>
        </button>
      </div>

      <div class="max-h-[480px] overflow-y-auto px-1.5 pb-2 space-y-3">
        <div v-for="group in groupedNotifications" :key="group.label" class="space-y-0.5">
          <div class="px-3 pt-1 pb-0.5 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
              {{ group.label }}
            </span>
          </div>

          <div v-for="item in group.items" :key="item.id" @click="markAsRead(item)"
            class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition cursor-pointer">
            <div :class="['p-1.5 rounded-lg shrink-0 mt-0.5', getTypeStyle(item.type).badge]">
              <component :is="getTypeStyle(item.type).icon" class="h-3.5 w-3.5" />
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 min-w-0">
                  <span v-if="!item.read_at" class="h-1.5 w-1.5 rounded-full bg-rose-500 shrink-0" />
                  <p class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                    {{ item.title }}
                  </p>
                </div>
                <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500 shrink-0">
                  {{ item.created_at_human }}
                </span>
              </div>

              <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5 leading-relaxed">
                {{ item.message }}
              </p>

              <Link v-if="item.link" :href="item.link" @click.stop="isOpen = false; markAsRead(item)"
                class="inline-flex items-center gap-1 text-[11px] font-medium text-sky-600 dark:text-sky-400 hover:underline mt-1.5">
                <span>Open</span>
                <ArrowUpRight class="h-3 w-3" />
              </Link>
            </div>
          </div>
        </div>

        <div v-if="groupedNotifications.length === 0"
          class="px-4 py-12 text-center text-xs text-zinc-400 dark:text-zinc-500">
          No {{ filterTab === 'unread' ? 'unread' : '' }} notifications.
        </div>
      </div>
    </div>
  </div>
</template>