<script setup>
import { Link } from '@inertiajs/vue3'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({
  alerts: { type: Array, default: () => [] },
})

const emit = defineEmits(['restock'])
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
      <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Attention Required</h2>
      <Link :href="route('inventory.index', { status: 'low_stock' })"
        class="text-xs text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
        View low stock
      </Link>
    </div>

    <div class="overflow-x-auto">
      <Table>
        <TableHeader class="bg-slate-50/50 dark:bg-zinc-900/50">
          <TableRow class="border-b border-slate-200/80 dark:border-zinc-800 hover:bg-transparent">
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">Item / SKU</TableHead>
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">Status</TableHead>
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">On Hand</TableHead>
            <TableHead class="px-4 h-9 text-right text-xs font-medium text-slate-500 dark:text-zinc-400">Action
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="alert in alerts" :key="alert.id"
            class="border-b border-slate-100 dark:border-zinc-800/60 hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors">
            <TableCell class="px-4 py-2.5">
              <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                {{ alert.product_variant?.product?.name || 'Deleted Product' }}
              </div>
              <div class="text-[11px] font-mono text-slate-500 dark:text-zinc-400">
                {{ alert.product_variant?.sku }}
                <span v-if="alert.product_variant?.variant_label">
                  · {{ alert.product_variant.variant_label }}
                </span>
              </div>
            </TableCell>

            <TableCell class="px-4 py-2.5">
              <span class="text-xs font-medium" :class="(alert.product_variant?.stock_qty ?? 0) <= 0
                  ? 'text-rose-600 dark:text-rose-400'
                  : 'text-amber-600 dark:text-amber-400'
                ">
                {{ (alert.product_variant?.stock_qty ?? 0) <= 0 ? 'Out of Stock' : 'Low Stock' }} </span>
            </TableCell>

            <TableCell class="px-4 py-2.5 font-mono text-xs text-slate-700 dark:text-zinc-300">
              {{ alert.product_variant?.stock_qty ?? 0 }}
              <span class="text-slate-400 dark:text-zinc-500">/ {{ alert.threshold_reached }} min</span>
            </TableCell>

            <TableCell class="px-4 py-2.5 text-right">
              <button v-if="alert.product_variant" type="button" @click="emit('restock', alert.product_variant)"
                class="text-xs font-medium text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                Restock
              </button>
              <span v-else class="text-xs text-slate-400 dark:text-zinc-500">—</span>
            </TableCell>
          </TableRow>

          <TableRow v-if="alerts.length === 0">
            <TableCell colspan="4" class="h-24 text-center text-xs text-slate-400 dark:text-zinc-500">
              All stock levels are healthy.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>