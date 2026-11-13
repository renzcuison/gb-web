<script setup>
import { ref, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import {
  Bell,
  CheckCheck,
  AlertTriangle,
  Package,
  Info,
  X,
  ChevronRight
} from 'lucide-vue-next'

const page = usePage()
const isOpen = ref(false)
const filterTab = ref('all')

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

const getIcon = (type) => {
  switch (type) {
    case 'warning': return AlertTriangle
    case 'danger': return Package
    default: return Info
  }
}

const getIconStyles = (type) => {
  switch (type) {
    case 'warning': return 'text-amber-500 bg-amber-50 dark:bg-amber-950/50'
    case 'danger': return 'text-rose-500 bg-rose-50 dark:bg-rose-950/50'
    default: return 'text-blue-500 bg-blue-50 dark:bg-blue-950/50'
  }
}
</script>

<template>
  <div>
    <!-- Bell Trigger Button -->
    <button @click="isOpen = !isOpen" title="Notifications" aria-label="Notifications"
      class="relative flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-zinc-300 bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 transition-colors border border-slate-200/80 dark:border-zinc-700/60">
      <Bell class="h-4 w-4 text-slate-600 dark:text-zinc-300" />

      <span v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-zinc-900">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Teleport Overlay & Panel to Body -->
    <Teleport to="body">
      <!-- Full Screen Dim Backdrop (Dims Sidebar & Main Area completely, sits behind z-50 Header) -->
      <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-40"></div>

      <!-- Right Drawer Panel (Sits at top-14 under the Header) -->
      <aside v-if="isOpen"
        class="fixed top-14 bottom-0 right-0 z-50 w-full max-w-sm sm:max-w-md bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 shadow-2xl flex flex-col">
        <!-- Header -->
        <div
          class="p-4 sm:p-5 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-zinc-900/80">
          <div class="flex items-center gap-2.5">
            <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">
              Notifications
            </h2>
            <span v-if="unreadCount > 0"
              class="px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300 rounded-full">
              {{ unreadCount }} new
            </span>
          </div>

          <div class="flex items-center gap-2">
            <button v-if="unreadCount > 0" @click="markAllAsRead" title="Mark all as read"
              class="flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-200 px-2 py-1 rounded-md hover:bg-slate-200/60 dark:hover:bg-zinc-800 transition">
              <CheckCheck class="h-3.5 w-3.5 text-blue-500" />
              <span>Mark all read</span>
            </button>

            <button @click="isOpen = false" aria-label="Close notifications panel"
              class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-slate-200/60 dark:hover:bg-zinc-800 transition">
              <X class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div
          class="flex items-center gap-2 px-4 py-2.5 border-b border-slate-100 dark:border-zinc-800/80 shrink-0 bg-white dark:bg-zinc-900">
          <button @click="filterTab = 'all'" class="px-3 py-1 rounded-lg text-xs font-semibold transition" :class="filterTab === 'all'
            ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs'
            : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800'">
            All Notifications
          </button>
          <button @click="filterTab = 'unread'"
            class="px-3 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1.5" :class="filterTab === 'unread'
              ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs'
              : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800'">
            <span>Unread</span>
            <span v-if="unreadCount > 0" class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
          </button>
        </div>

        <!-- Notification List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-zinc-800/60">
          <div v-for="item in filteredNotifications" :key="item.id" @click="markAsRead(item)"
            class="flex items-start gap-3.5 p-4 hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition cursor-pointer relative group"
            :class="{ 'bg-slate-50/50 dark:bg-zinc-800/20': !item.read_at }">
            <div v-if="!item.read_at" class="absolute left-0 top-0 bottom-0 w-1 bg-rose-500 rounded-r-sm"></div>

            <div class="p-2.5 rounded-xl shrink-0 mt-0.5" :class="getIconStyles(item.type)">
              <component :is="getIcon(item.type)" class="h-4 w-4" />
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-baseline justify-between gap-2">
                <p class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">
                  {{ item.title }}
                </p>
                <span class="text-[11px] font-medium text-slate-400 dark:text-zinc-500 shrink-0">
                  {{ item.created_at_human }}
                </span>
              </div>

              <p class="text-xs text-slate-600 dark:text-zinc-300 mt-1 leading-relaxed">
                {{ item.message }}
              </p>

              <Link v-if="item.link" :href="item.link" @click="isOpen = false; markAsRead(item)"
                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mt-2 transition">
                <span>View action item</span>
                <ChevronRight class="h-3.5 w-3.5" />
              </Link>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredNotifications.length === 0"
            class="h-full flex flex-col items-center justify-center p-8 text-center">
            <div class="p-3 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 mb-3">
              <Bell class="h-6 w-6" />
            </div>
            <p class="text-sm font-semibold text-slate-700 dark:text-zinc-300">
              No {{ filterTab === 'unread' ? 'unread' : '' }} notifications
            </p>
            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1 max-w-[200px]">
              When inventory alerts or system events occur, they will appear here.
            </p>
          </div>
        </div>

      </aside>
    </Teleport>
  </div>
</template>