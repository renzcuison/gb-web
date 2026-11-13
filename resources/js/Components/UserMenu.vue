<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import {
    User,
    Settings,
    Moon,
    Sun,
    Bug,
    LogOut,
    ChevronDown
} from 'lucide-vue-next'

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

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    syncDarkModeState()
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
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
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
})
</script>

<template>
    <div ref="containerRef" class="relative">
        <button @click="isOpen = !isOpen"
            class="flex items-center gap-2.5 p-1.5 pl-2 rounded-xl text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800/80 transition focus:outline-none">
            <div
                class="h-8 w-8 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                {{ userInitials }}
            </div>

            <div class="hidden md:block text-left">
                <p class="text-xs font-semibold leading-tight text-slate-800 dark:text-zinc-100">{{ user.name }}</p>
                <p class="text-[10px] text-slate-400 dark:text-zinc-500 leading-tight">Administrator</p>
            </div>

            <ChevronDown class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }" />
        </button>

        <div v-if="isOpen"
            class="absolute right-0 mt-2 w-72 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-zinc-800/70">

            <div class="p-4 bg-slate-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-indigo-600 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        {{ userInitials }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <div class="py-1 text-xs">
                <Link href="/profile" @click="isOpen = false"
                    class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800/60 transition">
                    <User class="h-4 w-4 text-slate-400 dark:text-zinc-500" />
                    <span>Profile Settings</span>
                </Link>

                <Link href="/settings" @click="isOpen = false"
                    class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800/60 transition">
                    <Settings class="h-4 w-4 text-slate-400 dark:text-zinc-500" />
                    <span>System Preferences</span>
                </Link>

                <a href="#" @click.prevent="handleReportBug"
                    class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800/60 transition">
                    <Bug class="h-4 w-4 text-amber-500" />
                    <span>Report a Bug</span>
                </a>
            </div>

            <div class="p-3">
                <div class="px-1 mb-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-zinc-500">
                    Accessibility Preferences
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-100 dark:bg-zinc-800/80 text-xs">
                    <div class="flex items-center gap-2 text-slate-700 dark:text-zinc-300">
                        <Sun v-if="!isDarkMode" class="h-4 w-4 text-amber-500" />
                        <Moon v-else class="h-4 w-4 text-indigo-400" />
                        <span class="font-medium">{{ isDarkMode ? 'Dark Mode' : 'Light Mode' }}</span>
                    </div>

                    <button type="button" @click="toggleDarkMode"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="isDarkMode ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                            :class="isDarkMode ? 'translate-x-4' : 'translate-x-0'" />
                    </button>
                </div>
            </div>

            <div class="py-1">
                <button @click="handleLogout"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition font-medium">
                    <LogOut class="h-4 w-4" />
                    <span>Sign Out</span>
                </button>
            </div>
        </div>
    </div>
</template>