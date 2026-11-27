<script setup>
import { useTheme } from '@/Composables/useTheme'
import { ImsSunIcon, ImsMoonIcon } from '@/Components/Icons/ImsIcons.vue'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <button
    type="button"
    @click="toggleTheme"
    class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:focus:ring-zinc-600 transition-colors"
    :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
    :aria-label="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
    <ImsSunIcon v-if="isDark" class="h-4 w-4 text-amber-400" />
    <ImsMoonIcon v-else class="h-4 w-4 text-zinc-600" />
  </button>
</template>