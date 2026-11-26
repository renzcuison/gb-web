<script setup>
import { Link } from '@inertiajs/vue3'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({
  movements: { type: Array, default: () => [] },
})

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
      <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Recent Stock Activity</h2>
      <Link :href="route('inventory.movements.index')"
        class="text-[11px] font-medium text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
        View all
      </Link>
    </div>

    <div class="overflow-x-auto">
      <Table>
        <TableHeader class="bg-slate-50/50 dark:bg-zinc-900/50">
          <TableRow class="border-b border-slate-200/80 dark:border-zinc-800 hover:bg-transparent">
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">Item</TableHead>
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">Reason / By</TableHead>
            <TableHead class="px-4 h-9 text-xs font-medium text-slate-500 dark:text-zinc-400">Time</TableHead>
            <TableHead class="px-4 h-9 text-right text-xs font-medium text-slate-500 dark:text-zinc-400">Change
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="move in movements" :key="move.id"
            class="border-b border-slate-100 dark:border-zinc-800/60 hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors">
            <TableCell class="px-4 py-2.5">
              <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                {{ move.variant?.product?.name || 'Item' }}
              </div>
              <div class="text-[11px] font-mono text-slate-500 dark:text-zinc-400">
                {{ move.variant?.sku }}
              </div>
            </TableCell>

            <TableCell class="px-4 py-2.5 text-xs text-slate-600 dark:text-zinc-400">
              <div>{{ move.reason || (move.type === 'in' ? 'Stock In' : 'Stock Out') }}</div>
              <div v-if="move.user?.name" class="text-[11px] text-slate-400 dark:text-zinc-500">
                {{ move.user.name }}
              </div>
            </TableCell>

            <TableCell class="px-4 py-2.5 text-xs text-slate-500 dark:text-zinc-400 whitespace-nowrap">
              {{ formatTime(move.created_at) }}
            </TableCell>

            <TableCell class="px-4 py-2.5 text-right font-mono text-xs font-medium" :class="move.type === 'in'
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-rose-600 dark:text-rose-400'
              ">
              {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
            </TableCell>
          </TableRow>

          <TableRow v-if="movements.length === 0">
            <TableCell colspan="4" class="h-24 text-center text-xs text-slate-400 dark:text-zinc-500">
              No recent stock movements recorded.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>