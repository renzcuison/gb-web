<script setup>
defineProps({
    items: { type: Array, default: () => [] }
})
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800/80">
            <h2 class="text-xs font-medium text-slate-500 dark:text-zinc-400">Top Allocated Stock</h2>
            <span class="text-[11px] font-normal text-slate-400 dark:text-zinc-500">Qty</span>
        </div>

        <div class="space-y-2">
            <div v-for="item in items" :key="item.id"
                class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-zinc-800/30 last:border-0">
                <div class="space-y-0.5 min-w-0 flex-1 pr-2">
                    <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 truncate">
                        {{ item.product?.name || 'Unknown Product' }}
                    </p>
                    <p class="text-[11px] font-mono font-normal text-slate-400 dark:text-zinc-500 truncate">
                        {{ item.sku }} <span v-if="item.variant_label">({{ item.variant_label }})</span>
                    </p>
                </div>
                <span class="text-xs font-medium font-mono text-slate-700 dark:text-zinc-300 flex-shrink-0">
                    {{ item.stock_qty }} units
                </span>
            </div>

            <div v-if="items.length === 0"
                class="text-center py-6 text-slate-400 dark:text-zinc-500 text-xs font-normal">
                No stock tracks logged yet.
            </div>
        </div>
    </div>
</template>