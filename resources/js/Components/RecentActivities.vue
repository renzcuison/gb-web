<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { History, X } from 'lucide-vue-next'

const page = usePage()
const recentActivities = computed(() => page.props.recent_activities || [])

const isRecentOpen = ref(false)
const containerRef = ref(null)

const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isRecentOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div ref="containerRef" class="relative">
        <button @click="isRecentOpen = !isRecentOpen" title="Recent Activities" aria-label="Recent Activities"
            class="flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-zinc-300 bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 transition-colors border border-slate-200/80 dark:border-zinc-700/60">
            <History class="h-4 w-4 text-slate-500 dark:text-zinc-400" />
        </button>

        <div v-if="isRecentOpen"
            class="absolute left-0 mt-2 w-80 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xl py-2 z-50 text-slate-700 dark:text-zinc-200">
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-100 dark:border-zinc-800">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                    Recent Activities
                </span>
                <button @click="isRecentOpen = false"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition">
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-zinc-800/60 max-h-[195px] overflow-y-auto">
                <div v-for="activity in recentActivities" :key="activity.id"
                    class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition cursor-pointer">
                    <p class="text-xs font-medium text-slate-800 dark:text-zinc-200">
                        {{ activity.description || activity.title }}
                    </p>
                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400 dark:text-zinc-500">
                        <span>{{ activity.user_name || 'System' }}</span>
                        <span>{{ activity.time || activity.created_at_human }}</span>
                    </div>
                </div>

                <div v-if="recentActivities.length === 0"
                    class="px-4 py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
                    No recent activity logged.
                </div>
            </div>
        </div>
    </div>
</template>