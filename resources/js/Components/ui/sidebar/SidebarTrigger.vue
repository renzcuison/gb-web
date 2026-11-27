<script setup>
import { inject } from 'vue'
import { ImsSidebarPanelIcon } from '@/Components/Icons/ImsIcons.vue'

const sidebar = inject('sidebar')
</script>

<template>
  <button
    @click="sidebar.toggleSidebar"
    type="button"
    aria-label="Toggle sidebar"
    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 dark:border-zinc-800 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 !transition-none"
  >
    <ImsSidebarPanelIcon class="h-4 w-4" />
  </button>
</template>