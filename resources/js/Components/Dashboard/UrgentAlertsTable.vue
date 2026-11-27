<script setup>
import { Link } from '@inertiajs/vue3'
import { ImsChevronRightIcon } from '@/Components/Icons/ImsIcons.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

defineProps({
  alerts: { type: Array, default: () => [] },
})

const emit = defineEmits(['restock'])
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Attention Required</h2>
      <Link
        :href="route('inventory.alerts.index')"
        aria-label="View all alerts"
        class="inline-flex items-center justify-center p-1 -mr-1 text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-100"
      >
        <ImsChevronRightIcon class="h-3.5 w-3.5" />
      </Link>
    </div>

    <div v-if="alerts.length" class="overflow-x-auto -mx-2">
      <Table>
        <TableHeader class="[&_tr]:border-0">
          <TableRow class="border-0 hover:bg-transparent">
            <TableHead class="px-2 h-7 text-xs font-medium text-zinc-400 dark:text-zinc-500">Item / SKU</TableHead>
            <TableHead class="px-2 h-7 text-xs font-medium text-zinc-400 dark:text-zinc-500">On Hand</TableHead>
            <TableHead class="px-2 h-7 text-right text-xs font-medium text-zinc-400 dark:text-zinc-500">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody class="[&_tr]:border-0">
          <TableRow
            v-for="alert in alerts"
            :key="alert.id"
            class="border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
          >
            <TableCell class="px-2 py-2.5">
              <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                {{ alert.product_variant?.product?.name || 'Unknown Item' }}
              </div>
              <div class="font-mono text-xs text-zinc-400 dark:text-zinc-500">
                {{ alert.product_variant?.sku || '—' }}
                <span v-if="alert.product_variant?.variant_label && alert.product_variant.variant_label !== 'Standard'">
                  · {{ alert.product_variant.variant_label }}
                </span>
              </div>
            </TableCell>

            <TableCell class="px-2 py-2.5 font-mono text-[13px] whitespace-nowrap">
              <span
                :class="[
                  'font-semibold',
                  (alert.product_variant?.stock_qty ?? 0) <= 0
                    ? 'text-rose-600 dark:text-rose-400'
                    : 'text-amber-600 dark:text-amber-400',
                ]"
              >
                {{ alert.product_variant?.stock_qty ?? 0 }}
              </span>
              <span class="text-xs text-zinc-400 dark:text-zinc-500">
                / {{ alert.product_variant?.low_stock_threshold ?? 0 }}
              </span>
            </TableCell>

            <TableCell class="px-2 py-2.5 text-right whitespace-nowrap">
              <button
                type="button"
                @click="emit('restock', alert.product_variant)"
                class="text-xs font-medium text-zinc-900 hover:text-zinc-600 dark:text-zinc-100 dark:hover:text-zinc-400"
              >
                Restock
              </button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-else class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
      All stock levels are healthy.
    </div>
  </div>
</template>