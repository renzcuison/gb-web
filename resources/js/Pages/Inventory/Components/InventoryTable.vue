<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

defineProps({
  inventory: Object,
  hasActiveFilters: Boolean,
})

const emit = defineEmits(['select', 'adjust'])

const copiedSku = ref(null)
const copySku = (sku) => {
  navigator.clipboard?.writeText(sku)
  copiedSku.value = sku
  setTimeout(() => {
    if (copiedSku.value === sku) copiedSku.value = null
  }, 1200)
}

const getStockDisplay = (variant) => {
  if (variant.stock_qty <= 0) {
    return {
      text: `${variant.stock_qty} units · Out`,
      class: 'text-rose-600 dark:text-rose-400',
    }
  }
  if (variant.stock_qty <= variant.low_stock_threshold) {
    return {
      text: `${variant.stock_qty} units · Low`,
      class: 'text-amber-600 dark:text-amber-400',
    }
  }
  return {
    text: `${variant.stock_qty} units`,
    class: 'text-slate-700 dark:text-zinc-300',
  }
}

const getClassification = (variant) => {
  const parts = [variant.product?.category?.name, variant.product?.brand?.name].filter(Boolean)
  return parts.length > 0 ? parts.join(' · ') : '—'
}

const formatPrice = (variant) => {
  const raw = variant.price_override ?? variant.product?.price ?? variant.product?.base_price
  if (raw === null || raw === undefined) return '—'
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(raw))
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-none">
    <div class="overflow-x-auto">
      <Table>
        <TableHeader class="bg-slate-50/50 dark:bg-zinc-900/50">
          <TableRow class="border-b border-slate-200/80 dark:border-zinc-800 hover:bg-transparent">
            <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">Product / Variant</TableHead>
            <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">SKU</TableHead>
            <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">Category & Brand</TableHead>
            <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">Price</TableHead>
            <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">Stock</TableHead>
            <TableHead class="text-right text-xs font-medium text-slate-500 dark:text-zinc-400 h-9">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="variant in inventory.data"
            :key="variant.id"
            @click="emit('select', variant)"
            class="border-b border-slate-100 dark:border-zinc-800/60 hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors cursor-pointer">
            <TableCell class="py-2.5">
              <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                {{ variant.product?.name }}
              </div>
              <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                {{ variant.variant_label || 'Standard' }}
              </div>
            </TableCell>

            <TableCell class="py-2.5" @click.stop>
              <button
                type="button"
                @click="copySku(variant.sku)"
                title="Click to copy SKU"
                class="font-mono text-xs text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 transition-colors">
                <span v-if="copiedSku === variant.sku" class="text-emerald-600 dark:text-emerald-400">Copied</span>
                <span v-else>{{ variant.sku }}</span>
              </button>
            </TableCell>

            <TableCell class="py-2.5 text-xs text-slate-500 dark:text-zinc-400">
              {{ getClassification(variant) }}
            </TableCell>

            <TableCell class="py-2.5 font-mono text-xs text-slate-600 dark:text-zinc-400">
              {{ formatPrice(variant) }}
            </TableCell>

            <TableCell class="py-2.5">
              <span :class="['text-xs font-mono font-medium', getStockDisplay(variant).class]">
                {{ getStockDisplay(variant).text }}
              </span>
            </TableCell>

            <TableCell class="py-2.5 text-right" @click.stop>
              <button
                type="button"
                @click="emit('adjust', variant)"
                class="text-xs font-medium text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                Adjust
              </button>
            </TableCell>
          </TableRow>

          <TableRow v-if="!inventory.data || inventory.data.length === 0">
            <TableCell colspan="6" class="h-28 text-center text-xs text-slate-400 dark:text-zinc-500">
              <div>No inventory records found.</div>
              <button
                v-if="hasActiveFilters"
                type="button"
                @click="router.get(route('inventory.index'))"
                class="text-xs text-slate-700 dark:text-zinc-300 underline mt-1">
                Clear filters
              </button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div
      v-if="inventory.total > 0"
      class="flex items-center justify-between px-4 py-2.5 border-t border-slate-200/80 dark:border-zinc-800 text-[11px] text-slate-500 dark:text-zinc-400">
      <span class="font-mono">
        {{ inventory.from }}–{{ inventory.to }} of {{ inventory.total }}
      </span>

      <div v-if="inventory.last_page > 1" class="flex items-center gap-4">
        <Link
          v-if="inventory.prev_page_url"
          :href="inventory.prev_page_url"
          preserve-scroll
          preserve-state
          class="hover:text-slate-900 dark:hover:text-zinc-100 transition-colors">
          Previous
        </Link>
        <span v-else class="opacity-40">Previous</span>

        <span class="font-mono">{{ inventory.current_page }} / {{ inventory.last_page }}</span>

        <Link
          v-if="inventory.next_page_url"
          :href="inventory.next_page_url"
          preserve-scroll
          preserve-state
          class="hover:text-slate-900 dark:hover:text-zinc-100 transition-colors">
          Next
        </Link>
        <span v-else class="opacity-40">Next</span>
      </div>
    </div>
  </div>
</template>