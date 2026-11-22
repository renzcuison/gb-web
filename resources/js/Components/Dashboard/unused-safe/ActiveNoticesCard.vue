<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  notices: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
      <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Active Notices</h2>
      <Link
        href="/announcements"
        class="text-xs text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
        Manage →
      </Link>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-zinc-800/60">
      <div v-for="notice in notices" :key="notice.id" class="px-4 py-2.5 space-y-0.5">
        <div class="text-xs font-medium text-slate-900 dark:text-zinc-100 truncate">
          {{ notice.title }}
        </div>
        <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2">
          {{ notice.content || notice.created_at }}
        </p>
      </div>

      <div v-if="notices.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
        No active notices.
      </div>
    </div>
  </div>
</template>