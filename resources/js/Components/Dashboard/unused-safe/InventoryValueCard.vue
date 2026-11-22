<script setup>
import { computed } from 'vue'

const props = defineProps({
    totalValue: { type: Number, default: 0 },
    topItems: { type: Array, default: () => [] }
})

const PHP_TO_CAD_RATE = 0.0241

const formattedPhpValue = computed(() => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(props.totalValue)
})

const formattedCadValue = computed(() => {
    const cadAmount = props.totalValue * PHP_TO_CAD_RATE
    return 'CAD $' + new Intl.NumberFormat('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(cadAmount)
})
</script>

<template>
    <div
        class="p-5 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 flex flex-col justify-between min-h-[280px]">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800/80">
                <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">Inventory Value</span>
                <span class="text-xs font-normal font-mono text-slate-400 dark:text-zinc-500">{{ formattedCadValue
                    }}</span>
            </div>

            <div class="mt-3 pb-3 border-b border-slate-100 dark:border-zinc-800/60">
                <div class="text-xl font-semibold text-slate-900 dark:text-zinc-100 tracking-tight">
                    {{ formattedPhpValue }}
                </div>
            </div>

            <div class="mt-3 h-32 overflow-y-auto pr-1 space-y-2">
                <p class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">
                    Top Stock Allocations
                </p>
                <div v-for="item in topItems" :key="item.id"
                    class="flex items-center justify-between text-xs py-1 border-b border-slate-50 dark:border-zinc-800/30 last:border-0">
                    <span class="truncate pr-2 text-slate-600 dark:text-zinc-300 font-normal">
                        {{ item.product?.name || item.sku }}
                    </span>
                    <span class="font-mono text-slate-800 dark:text-zinc-200 font-medium flex-shrink-0">
                        {{ item.stock_qty }} units
                    </span>
                </div>

                <div v-if="topItems.length === 0" class="text-xs text-slate-400 dark:text-zinc-500 py-4 text-center">
                    No active inventory logged.
                </div>
            </div>
        </div>
    </div>
</template>