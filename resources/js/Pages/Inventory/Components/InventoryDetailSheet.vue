<script setup>
import { Link, router } from '@inertiajs/vue3'
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
      class="w-full sm:max-w-[380px] p-0 flex flex-col bg-white dark:bg-zinc-950 border-l border-zinc-200 dark:border-zinc-800 shadow-none">
      <template v-if="variant">
        <SheetHeader class="p-5 pb-3 text-left space-y-1">
          <div class="font-mono text-xs text-zinc-500 dark:text-zinc-400">
            {{ variant.sku }}
          </div>
          <SheetTitle class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
            {{ variant.product?.name }}
          </SheetTitle>
          <SheetDescription class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ variant.variant_label || 'Standard' }}
          </SheetDescription>
        </SheetHeader>

        <div class="flex-1 overflow-y-auto px-5 pb-5 space-y-5">
          <div v-if="variant.product?.media && variant.product.media.length > 0"
            class="aspect-video w-full rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden bg-zinc-50 dark:bg-zinc-900">
            <img :src="variant.product.media[0].url" :alt="variant.product?.name" class="h-full w-full object-cover" />
          </div>

          <div class="rounded-md bg-zinc-50/70 dark:bg-zinc-900/70 p-3.5 space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-zinc-500 dark:text-zinc-400">On Hand</span>
              <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">
                {{ variant.stock_qty }} units
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-zinc-500 dark:text-zinc-400">Alert Threshold</span>
              <span class="font-mono text-zinc-700 dark:text-zinc-300">
                {{ variant.low_stock_threshold }} units
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-zinc-500 dark:text-zinc-400">Unit Price</span>
              <span class="font-mono text-zinc-700 dark:text-zinc-300">
                {{ formatPrice(variant) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-zinc-500 dark:text-zinc-400">Category</span>
              <span class="text-zinc-800 dark:text-zinc-200">
                {{ variant.product?.category?.name || '—' }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-zinc-500 dark:text-zinc-400">Brand</span>
              <span class="text-zinc-800 dark:text-zinc-200">
                {{ variant.product?.brand?.name || '—' }}
              </span>
            </div>
            <div v-if="variant.product?.tags && variant.product.tags.length > 0"
              class="flex items-start justify-between gap-4">
              <span class="text-zinc-500 dark:text-zinc-400 shrink-0">Tags</span>
              <span class="text-zinc-700 dark:text-zinc-300 text-right">
                {{ variant.product.tags.map((t) => t.name).join(', ') }}
              </span>
            </div>
          </div>

          <div v-if="!variant.deleted_at" class="grid grid-cols-2 gap-2">
            <Button size="sm" @click="emit('adjust', variant)"
              class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-none">
              Adjust Stock
            </Button>
            <Button size="sm" variant="outline" as-child
              class="h-8 text-xs font-medium border-zinc-200 dark:border-zinc-800 shadow-none">
              <Link :href="route('products.edit', variant.product_id)">
                Edit Product
              </Link>
            </Button>
          </div>
          <Button v-else size="sm" variant="outline"
            @click="router.post(route('products.restore', variant.product_id), {}, { preserveScroll: true, onSuccess: () => emit('update:open', false) })"
            class="w-full h-8 text-xs font-medium border-zinc-200 text-emerald-700 hover:bg-zinc-50 dark:border-zinc-800 dark:text-emerald-400 shadow-none">
            Restore Product
          </Button>

          <div class="space-y-2.5">
            <h3 class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
              Recent Movements
            </h3>

            <div v-if="variant.inventory_movements && variant.inventory_movements.length > 0"
              class="space-y-2">
              <div v-for="move in variant.inventory_movements" :key="move.id"
                class="py-1.5 flex items-center justify-between gap-3 text-xs">
                <div class="min-w-0">
                  <div class="text-[13px] text-zinc-800 dark:text-zinc-200 truncate">
                    {{ move.reason || (move.type === 'in' ? 'Stock In' : 'Stock Out') }}
                  </div>
                  <div class="text-xs text-zinc-400 dark:text-zinc-500">
                    {{ formatDate(move.created_at) }}
                  </div>
                </div>
                <span :class="[
                  'font-mono text-[13px] font-medium shrink-0',
                  move.type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400',
                ]">
                  {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
                </span>
              </div>
            </div>

            <p v-else class="text-xs text-zinc-400 dark:text-zinc-500 py-3">
              No movements recorded yet.
            </p>
          </div>
        </div>
      </template>
    </SheetContent>
  </Sheet>
</template>