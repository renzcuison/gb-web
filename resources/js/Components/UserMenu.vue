<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import {
  ImsUserIcon,
  ImsSettingsIcon,
  ImsMoonIcon,
  ImsSunIcon,
  ImsBugIcon,
  ImsSignOutIcon,
  ImsChevronDownIcon,
} from '@/Components/Icons/ImsIcons.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user || { name: 'Admin User', email: 'admin@example.com' })

const isOpen = ref(false)
const containerRef = ref(null)
const isDarkMode = ref(false)

const syncDarkModeState = () => {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  syncDarkModeState()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

const handleLogout = () => {
  isOpen.value = false
  router.post('/logout')
}

const handleReportBug = () => {
  isOpen.value = false
  alert('Report a Bug form initialized. You can connect a modal or route here.')
}

const userInitials = computed(() => {
  if (!user.value?.name) return 'A'
  return user.value.name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const avatarBgColor = computed(() => user.value?.resolved_avatar_color || '#0f172a')
</script>

<template>
  <div ref="containerRef" class="relative [&_*]:!transition-none">
    <button
      @click="isOpen = !isOpen"
      class="flex items-center gap-2.5 p-1.5 pl-2 rounded-md text-slate-900 dark:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800/80 focus:outline-none"
    >
      <img
        v-if="user.avatar_url"
        :src="user.avatar_url"
        :alt="user.name"
        class="h-8 w-8 rounded-md object-cover shrink-0"
      />
      <div
        v-else
        :style="{ backgroundColor: avatarBgColor }"
        class="h-8 w-8 rounded-md text-white font-semibold text-xs flex items-center justify-center shrink-0"
      >
        {{ userInitials }}
      </div>

      <div class="hidden md:block text-left">
        <p class="text-[13px] font-semibold leading-tight text-slate-900 dark:text-zinc-100">
          {{ user.name }}
        </p>
        <p class="text-xs text-slate-500 dark:text-zinc-400 leading-tight capitalize">
          {{ user.role === 'admin' ? 'Administrator' : user.role || 'Member' }}
        </p>
      </div>

      <ImsChevronDownIcon
        class="h-3.5 w-3.5 text-slate-900 dark:text-zinc-100"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-72 rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-lg z-50 overflow-hidden text-slate-900 dark:text-zinc-100"
    >
      <div class="px-4 pt-3.5 pb-2.5">
        <div class="flex items-center gap-3">
          <img
            v-if="user.avatar_url"
            :src="user.avatar_url"
            :alt="user.name"
            class="h-9 w-9 rounded-md object-cover shrink-0"
          />
          <div
            v-else
            :style="{ backgroundColor: avatarBgColor }"
            class="h-9 w-9 rounded-md text-white font-semibold text-xs flex items-center justify-center shrink-0"
          >
            {{ userInitials }}
          </div>
          <div class="min-w-0">
            <p class="text-[13px] font-semibold text-slate-900 dark:text-zinc-100 truncate">
              {{ user.name }}
            </p>
            <p class="text-xs text-slate-500 dark:text-zinc-400 truncate">
              {{ user.email }}
            </p>
          </div>
        </div>
      </div>

      <div class="px-1.5 py-1 space-y-0.5">
        <Link
          href="/profile"
          @click="isOpen = false"
          class="flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800/60"
        >
          <ImsUserIcon class="h-4 w-4 text-slate-900 dark:text-zinc-100" />
          <span>Profile Settings</span>
        </Link>

        <Link
          href="/settings"
          @click="isOpen = false"
          class="flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800/60"
        >
          <ImsSettingsIcon class="h-4 w-4 text-slate-900 dark:text-zinc-100" />
          <span>System Preferences</span>
        </Link>

        <a
          href="#"
          @click.prevent="handleReportBug"
          class="flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800/60"
        >
          <ImsBugIcon class="h-4 w-4 text-slate-900 dark:text-zinc-100" />
          <span>Report a Bug</span>
        </a>
      </div>

      <div class="px-3 py-2.5">
        <div class="px-1 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
          Appearance
        </div>
        <div class="flex items-center justify-between px-2.5 py-2 rounded-md bg-slate-100 dark:bg-zinc-800/80">
          <div class="flex items-center gap-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-300">
            <ImsSunIcon v-if="!isDarkMode" class="h-4 w-4 text-slate-900 dark:text-zinc-100" />
            <ImsMoonIcon v-else class="h-4 w-4 text-slate-900 dark:text-zinc-100" />
            <span>{{ isDarkMode ? 'Dark Mode' : 'Light Mode' }}</span>
          </div>

          <button
            type="button"
            @click="toggleDarkMode"
            aria-label="Toggle dark mode"
            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent focus:outline-none"
            :class="isDarkMode ? 'bg-zinc-700' : 'bg-slate-300'"
          >
            <span
              class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white ring-0"
              :class="isDarkMode ? 'translate-x-4' : 'translate-x-0'"
            />
          </button>
        </div>
      </div>

      <div class="px-1.5 pb-1.5">
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50/70 dark:hover:bg-rose-950/30"
        >
          <ImsSignOutIcon class="h-4 w-4" />
          <span>Sign Out</span>
        </button>
      </div>
    </div>
  </div>
</template>