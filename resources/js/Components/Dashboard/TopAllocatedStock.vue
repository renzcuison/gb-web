<script setup>
defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
      <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Top Stocked Items</h2>
      <span class="text-[11px] text-slate-400 dark:text-zinc-500">Qty</span>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-zinc-800/60">
      <div
        v-for="item in items"
        :key="item.id"
        class="px-4 py-2.5 flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
          <div class="text-xs font-medium text-slate-900 dark:text-zinc-100 truncate">
            {{ item.product?.name || 'Unknown Product' }}
          </div>
          <div class="text-[11px] font-mono text-slate-500 dark:text-zinc-400 truncate">
            {{ item.sku }}<span v-if="item.variant_label"> · {{ item.variant_label }}</span>
          </div>
        </div>

        <span class="text-xs font-mono font-medium text-slate-700 dark:text-zinc-300 shrink-0">
          {{ item.stock_qty }} units
        </span>
      </div>

      <div v-if="items.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
        No stock recorded yet.
      </div>
    </div>
  </div>
</template>