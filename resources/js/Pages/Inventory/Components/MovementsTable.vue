<script setup>
import { Link } from '@inertiajs/vue3'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

defineProps({
  movements: { type: Object, required: true },
  hasActiveFilters: { type: Boolean, default: false },
})

const emit = defineEmits(['reset'])

const formatTimestamp = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="overflow-x-auto pt-2 pb-1">
      <Table>
        <TableHeader class="[&_tr]:border-0">
          <TableRow class="border-0 hover:bg-transparent">
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Timestamp</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Product / Variant</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">SKU</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Reason</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Recorded By</TableHead>
            <TableHead class="px-4 text-right text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Change</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody class="[&_tr]:border-0">
          <TableRow
            v-for="move in movements.data"
            :key="move.id"
            class="border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
            <TableCell class="px-4 py-2.5 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap font-mono">
              {{ formatTimestamp(move.created_at) }}
            </TableCell>

            <TableCell class="px-4 py-2.5">
              <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                {{ move.variant?.product?.name || 'Deleted Product' }}
                <span
                  v-if="move.variant?.deleted_at || move.variant?.product?.deleted_at"
                  class="text-xs font-normal text-zinc-400 ml-1">
                  (Archived)
                </span>
              </div>
              <div class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ move.variant?.variant_label || 'Standard' }}
              </div>
            </TableCell>

            <TableCell class="px-4 py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400">
              {{ move.variant?.sku || '—' }}
            </TableCell>

            <TableCell class="px-4 py-2.5 text-[13px] text-zinc-600 dark:text-zinc-300 max-w-xs truncate">
              {{ move.reason || (move.type === 'in' ? 'Manual Stock In' : 'Manual Stock Out') }}
            </TableCell>

            <TableCell class="px-4 py-2.5 text-[13px] text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
              {{ move.user?.name || 'System' }}
            </TableCell>

            <TableCell class="px-4 py-2.5 text-right whitespace-nowrap">
              <span
                :class="[
                  'font-mono text-[13px] font-medium',
                  move.type === 'in'
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-rose-600 dark:text-rose-400',
                ]">
                {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
              </span>
            </TableCell>
          </TableRow>

          <TableRow v-if="!movements.data || movements.data.length === 0" class="border-0 hover:bg-transparent">
            <TableCell colspan="6" class="h-28 text-center text-xs text-zinc-400 dark:text-zinc-500">
              <div>No movement records found.</div>
              <button
                v-if="hasActiveFilters"
                type="button"
                @click="emit('reset')"
                class="text-xs text-zinc-800 dark:text-zinc-300 underline mt-1">
                Clear filters
              </button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div
      v-if="movements.total > 0"
      class="flex items-center justify-between px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
      <span class="font-mono">
        {{ movements.from }}–{{ movements.to }} of {{ movements.total }}
      </span>

      <div v-if="movements.last_page > 1" class="flex items-center gap-4">
        <Link
          v-if="movements.prev_page_url"
          :href="movements.prev_page_url"
          preserve-scroll
          preserve-state
          class="hover:text-zinc-900 dark:hover:text-zinc-100">
          Previous
        </Link>
        <span v-else class="opacity-40">Previous</span>

        <span class="font-mono">{{ movements.current_page }} / {{ movements.last_page }}</span>

        <Link
          v-if="movements.next_page_url"
          :href="movements.next_page_url"
          preserve-scroll
          preserve-state
          class="hover:text-zinc-900 dark:hover:text-zinc-100">
          Next
        </Link>
        <span v-else class="opacity-40">Next</span>
      </div>
    </div>
  </div>
</template>