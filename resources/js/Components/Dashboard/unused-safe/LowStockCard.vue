<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'

const props = defineProps({
    count: { type: Number, default: 0 },
    alerts: { type: Array, default: () => [] }
})

const activeAlerts = computed(() => props.alerts.filter(a => !a.is_resolved))
</script>

<template>
    <div
        class="p-5 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 flex flex-col justify-between min-h-[280px]">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800/80">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">Low Stock</span>
                    <span
                        class="text-xs font-medium text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-md font-mono">
                        {{ count }}
                    </span>
                </div>
                <Link href="/inventory/alerts"
                    class="text-xs font-normal text-rose-600 dark:text-rose-400 hover:underline">
                    View all →
                </Link>
            </div>

            <div class="mt-3 h-52 overflow-y-auto pr-1 space-y-2.5 divide-y divide-slate-100 dark:divide-zinc-800/50">
                <div v-for="alert in activeAlerts" :key="alert.id"
                    class="flex items-center justify-between pt-2.5 first:pt-0">
                    <div class="min-w-0 pr-3">
                        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 truncate">
                            {{ alert.product_variant?.product?.name || 'Deleted Product' }}
                        </p>
                        <p class="text-[11px] font-mono font-normal text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                            SKU: {{ alert.product_variant?.sku }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="font-mono font-medium text-rose-600 dark:text-rose-400 text-xs">
                            {{ alert.product_variant?.stock_qty ?? 0 }} left
                        </span>
                        <Button size="xs" as-child variant="outline"
                            class="h-6 text-[11px] font-medium border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/60 px-2">
                            <Link href="/inventory">Restock</Link>
                        </Button>
                    </div>
                </div>

                <div v-if="activeAlerts.length === 0"
                    class="flex flex-col items-center justify-center h-40 text-center space-y-1">
                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">All levels optimal</span>
                    <span class="text-[11px] font-normal text-slate-400 dark:text-zinc-500">No low stock thresholds
                        breached</span>
                </div>
            </div>
        </div>
    </div>
</template>