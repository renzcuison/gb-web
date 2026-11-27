<script setup>
import { Link } from '@inertiajs/vue3'
import { ImsChevronRightIcon } from '@/Components/Icons/ImsIcons.vue'

defineProps({
  items: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Top Stocked Items</h2>
      <Link
        :href="route('inventory.index', { sort: 'stock_desc' })"
        aria-label="View top stocked items"
        class="inline-flex items-center justify-center p-1 -mr-1 text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-100"
      >
        <ImsChevronRightIcon class="h-3.5 w-3.5" />
      </Link>
    </div>

    <div v-if="items.length" class="space-y-1 -mx-2">
      <Link
        v-for="item in items"
        :key="item.id"
        :href="route('inventory.index', { search: item.sku })"
        class="px-2 py-2 rounded-md flex items-center justify-between gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
      >
        <div class="min-w-0">
          <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100 truncate">
            {{ item.product?.name || 'Item' }}
          </div>
          <div class="font-mono text-xs text-zinc-400 dark:text-zinc-500 truncate">
            {{ item.sku }}
            <span v-if="item.variant_label && item.variant_label !== 'Standard'">
              · {{ item.variant_label }}
            </span>
          </div>
        </div>

        <span class="shrink-0 font-mono text-[13px] font-medium text-zinc-700 dark:text-zinc-300">
          {{ item.stock_qty }}
        </span>
      </Link>
    </div>

    <div v-else class="py-6 text-center text-xs text-zinc-400 dark:text-zinc-500">
      No stock data available.
    </div>
  </div>
</template>