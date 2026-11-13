<script setup>
import { Button } from '@/Components/ui/button'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/Components/ui/sheet'

defineProps({
  open: Boolean,
  variant: Object,
})

const emit = defineEmits(['update:open', 'adjust'])

const formatPrice = (variant) => {
  const raw = variant?.price_override ?? variant?.product?.price ?? variant?.product?.base_price
  if (raw === null || raw === undefined) return '—'
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(raw))
}

const formatDate = (dateStr) => {
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
  <Sheet :open="open" @update:open="emit('update:open', $event)">
    <SheetContent
      class="w-full sm:max-w-[400px] p-0 flex flex-col bg-white dark:bg-zinc-950 border-l border-slate-200 dark:border-zinc-800 shadow-none">
      <template v-if="variant">
        <SheetHeader class="p-5 pb-4 border-b border-slate-200/80 dark:border-zinc-800 text-left space-y-1">
          <div class="font-mono text-[11px] text-slate-500 dark:text-zinc-400">
            {{ variant.sku }}
          </div>
          <SheetTitle class="text-base font-semibold text-slate-900 dark:text-zinc-100">
            {{ variant.product?.name }}
          </SheetTitle>
          <SheetDescription class="text-xs text-slate-500 dark:text-zinc-400">
            {{ variant.variant_label || 'Standard' }}
          </SheetDescription>
        </SheetHeader>

        <div class="flex-1 overflow-y-auto p-5 space-y-6">
          <div class="divide-y divide-slate-100 dark:divide-zinc-800/80 border-y border-slate-100 dark:border-zinc-800/80 text-xs">
            <div class="flex items-center justify-between py-2.5">
              <span class="text-slate-500 dark:text-zinc-400">On Hand</span>
              <span class="font-mono font-medium text-slate-900 dark:text-zinc-100">
                {{ variant.stock_qty }} units
              </span>
            </div>
            <div class="flex items-center justify-between py-2.5">
              <span class="text-slate-500 dark:text-zinc-400">Alert Threshold</span>
              <span class="font-mono text-slate-700 dark:text-zinc-300">
                {{ variant.low_stock_threshold }} units
              </span>
            </div>
            <div class="flex items-center justify-between py-2.5">
              <span class="text-slate-500 dark:text-zinc-400">Unit Price</span>
              <span class="font-mono text-slate-700 dark:text-zinc-300">
                {{ formatPrice(variant) }}
              </span>
            </div>
            <div class="flex items-center justify-between py-2.5">
              <span class="text-slate-500 dark:text-zinc-400">Category</span>
              <span class="text-slate-800 dark:text-zinc-200">
                {{ variant.product?.category?.name || '—' }}
              </span>
            </div>
            <div class="flex items-center justify-between py-2.5">
              <span class="text-slate-500 dark:text-zinc-400">Brand</span>
              <span class="text-slate-800 dark:text-zinc-200">
                {{ variant.product?.brand?.name || '—' }}
              </span>
            </div>
          </div>

          <Button
            size="sm"
            variant="outline"
            @click="emit('adjust', variant)"
            class="w-full h-8 text-xs font-medium border-slate-200 dark:border-zinc-800 shadow-none">
            Adjust Stock
          </Button>

          <div class="space-y-2">
            <h3 class="text-xs font-medium text-slate-500 dark:text-zinc-400">
              Recent Movements
            </h3>

            <div
              v-if="variant.inventory_movements && variant.inventory_movements.length > 0"
              class="divide-y divide-slate-100 dark:divide-zinc-800/80 border-t border-slate-100 dark:border-zinc-800/80">
              <div
                v-for="move in variant.inventory_movements"
                :key="move.id"
                class="py-2.5 flex items-center justify-between gap-3 text-xs">
                <div class="min-w-0">
                  <div class="text-slate-800 dark:text-zinc-200 truncate">
                    {{ move.reason || (move.type === 'in' ? 'Stock In' : 'Stock Out') }}
                  </div>
                  <div class="text-[11px] text-slate-400 dark:text-zinc-500">
                    {{ formatDate(move.created_at) }}
                  </div>
                </div>
                <span
                  :class="[
                    'font-mono font-medium shrink-0',
                    move.type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400',
                  ]">
                  {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
                </span>
              </div>
            </div>

            <p v-else class="text-xs text-slate-400 dark:text-zinc-500 py-3">
              No movements recorded yet.
            </p>
          </div>
        </div>
      </template>
    </SheetContent>
  </Sheet>
</template>