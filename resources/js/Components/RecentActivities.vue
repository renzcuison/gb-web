<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import { History, RotateCw, X } from 'lucide-vue-next'

const page = usePage()
const recentActivities = computed(() => page.props.recent_activities || [])

const isRecentOpen = ref(false)
const isRefreshing = ref(false)
const lastSeenId = ref(null)
const containerRef = ref(null)

let pollInterval = null

const latestId = computed(() => recentActivities.value[0]?.id ?? null)

const hasNewActivity = computed(() => {
    if (!latestId.value || lastSeenId.value === null) return false
    return latestId.value > lastSeenId.value
})

const refreshActivities = () => {
    if (isRefreshing.value) return
    isRefreshing.value = true

    router.reload({
        only: ['recent_activities'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false
            if (isRecentOpen.value && latestId.value) {
                lastSeenId.value = latestId.value
            }
        },
    })
}

const togglePopover = () => {
    isRecentOpen.value = !isRecentOpen.value
    if (isRecentOpen.value) {
        if (latestId.value) {
            lastSeenId.value = latestId.value
        }
        refreshActivities()
    }
}

watch(
    latestId,
    (val) => {
        if (lastSeenId.value === null && val) {
            lastSeenId.value = val
        }
    },
    { immediate: true }
)

const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isRecentOpen.value = false
    }
}

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        refreshActivities()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('visibilitychange', handleVisibilityChange)

    pollInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            refreshActivities()
        }
    }, 20000)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('visibilitychange', handleVisibilityChange)
    clearInterval(pollInterval)
})
</script>

<template>
    <div ref="containerRef" class="relative">
        <button @click="togglePopover" title="Recent Activities" aria-label="Recent Activities"
            class="relative flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-zinc-300 bg-slate-100 hover:bg-slate-200/80 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 transition-colors border border-slate-200/80 dark:border-zinc-700/60">
            <History class="h-4 w-4 text-slate-500 dark:text-zinc-400" />

            <span v-if="hasNewActivity"
                class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-900" />
        </button>

        <div v-if="isRecentOpen"
            class="absolute left-0 mt-2 w-96 max-w-[calc(100vw-2rem)] rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xl z-50 overflow-hidden text-slate-700 dark:text-zinc-200">
            <div class="flex items-center justify-between px-4 pt-3.5 pb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                    Recent Activities
                </span>
                <div class="flex items-center gap-2">
                    <button @click="refreshActivities" :disabled="isRefreshing" title="Refresh"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition disabled:opacity-50">
                        <RotateCw class="h-3.5 w-3.5" :class="{ 'animate-spin': isRefreshing }" />
                    </button>
                    <button @click="isRecentOpen = false" title="Close"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition">
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto px-1.5 pb-2 space-y-0.5">
                <Link v-for="activity in recentActivities" :key="activity.id"
                    :href="activity.href || '/inventory/movements'" @click="isRecentOpen = false"
                    class="flex items-start justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200">
                            {{ activity.description || activity.title }}
                        </p>
                        <div
                            class="flex items-center justify-between mt-1 text-[10px] text-slate-400 dark:text-zinc-500">
                            <span>{{ activity.user_name || 'System' }}</span>
                            <span>{{ activity.time || activity.created_at_human }}</span>
                        </div>
                    </div>

                    <span v-if="activity.quantity" :class="[
                        'font-mono text-xs font-medium shrink-0',
                        activity.type === 'in'
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-rose-600 dark:text-rose-400',
                    ]">
                        {{ activity.type === 'in' ? '+' : '-' }}{{ activity.quantity }}
                    </span>
                </Link>

                <div v-if="recentActivities.length === 0"
                    class="px-4 py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
                    No recent activity logged.
                </div>
            </div>
        </div>
    </div>
</template>