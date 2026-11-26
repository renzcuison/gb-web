<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
      <h2 class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Top Stocked Items</h2>
      <Link :href="route('inventory.index', { sort: 'stock_desc' })"
        class="text-[11px] font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
        View all
      </Link>
    </div>

    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
      <Link v-for="item in items" :key="item.id" :href="route('inventory.index', { search: item.sku })"
        class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
        <div class="min-w-0 flex-1">
          <div class="text-xs font-medium text-zinc-900 dark:text-zinc-100 truncate">
            {{ item.product?.name || 'Unknown Product' }}
          </div>
          <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 truncate">
            {{ item.sku }}<span v-if="item.variant_label"> · {{ item.variant_label }}</span>
          </div>
        </div>

        <span class="text-xs font-mono font-medium text-zinc-700 dark:text-zinc-300 shrink-0">
          {{ item.stock_qty }} units
        </span>
      </Link>

      <div v-if="items.length === 0" class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
        No stock recorded yet.
      </div>
    </div>
  </div>
</template>