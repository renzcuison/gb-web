<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    count: { type: Number, default: 0 },
    notices: { type: Array, default: () => [] }
})
</script>

<template>
    <div
        class="p-5 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 flex flex-col justify-between min-h-[280px]">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800/80">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">Active Notices</span>
                    <span
                        class="text-xs font-medium text-slate-600 dark:text-zinc-400 bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md font-mono">
                        {{ count }}
                    </span>
                </div>
                <Link href="/announcements"
                    class="text-xs font-normal text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                    Manage →
                </Link>
            </div>

            <div class="mt-3 h-52 overflow-y-auto pr-1 space-y-2.5 divide-y divide-slate-100 dark:divide-zinc-800/50">
                <div v-for="notice in notices" :key="notice.id" class="pt-2.5 first:pt-0 space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 truncate">
                            {{ notice.title }}
                        </p>
                        <span
                            class="text-[10px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-1.5 py-0.5 rounded shrink-0">
                            Active
                        </span>
                    </div>
                    <p class="text-xs font-normal text-slate-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                        {{ notice.content || notice.created_at }}
                    </p>
                </div>

                <div v-if="notices.length === 0"
                    class="flex flex-col items-center justify-center h-40 text-center space-y-1">
                    <span class="text-xs font-normal text-slate-400 dark:text-zinc-500">No active system notices</span>
                    <Link href="/announcements"
                        class="text-xs font-medium text-slate-600 dark:text-zinc-400 hover:underline">
                        + Broadcast a notice
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>