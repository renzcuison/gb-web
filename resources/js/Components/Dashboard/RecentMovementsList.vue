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
  movements: { type: Array, default: () => [] },
})

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Recent Stock Activity</h2>
      <Link
        :href="route('inventory.movements.index')"
        aria-label="View all stock activity"
        class="inline-flex items-center justify-center p-1 -mr-1 text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-100"
      >
        <ImsChevronRightIcon class="h-3.5 w-3.5" />
      </Link>
    </div>

    <div v-if="movements.length" class="overflow-x-auto -mx-2">
      <Table>
        <TableHeader class="[&_tr]:border-0">
          <TableRow class="border-0 hover:bg-transparent">
            <TableHead class="px-2 h-7 text-xs font-medium text-zinc-400 dark:text-zinc-500">Item</TableHead>
            <TableHead class="px-2 h-7 text-xs font-medium text-zinc-400 dark:text-zinc-500">Reason / By</TableHead>
            <TableHead class="px-2 h-7 text-xs font-medium text-zinc-400 dark:text-zinc-500">Time</TableHead>
            <TableHead class="px-2 h-7 text-right text-xs font-medium text-zinc-400 dark:text-zinc-500">Change</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody class="[&_tr]:border-0">
          <TableRow
            v-for="move in movements"
            :key="move.id"
            class="border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
          >
            <TableCell class="px-2 py-2.5">
              <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                {{ move.variant?.product?.name || 'Deleted Product' }}
              </div>
              <div class="font-mono text-xs text-zinc-400 dark:text-zinc-500">
                {{ move.variant?.sku || '—' }}
              </div>
            </TableCell>

            <TableCell class="px-2 py-2.5">
              <div class="text-[13px] text-zinc-700 dark:text-zinc-300 truncate max-w-[220px]">
                {{ move.reason || (move.type === 'in' ? 'Manual Stock In' : 'Manual Stock Out') }}
              </div>
              <div class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ move.user?.name || 'System' }}
              </div>
            </TableCell>

            <TableCell class="px-2 py-2.5 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap font-mono">
              {{ formatDate(move.created_at) }}
            </TableCell>

            <TableCell class="px-2 py-2.5 text-right whitespace-nowrap">
              <span
                :class="[
                  'font-mono text-[13px] font-medium',
                  move.type === 'in'
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-rose-600 dark:text-rose-400',
                ]"
              >
                {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
              </span>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-else class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
      No stock movements recorded yet.
    </div>
  </div>
</template>