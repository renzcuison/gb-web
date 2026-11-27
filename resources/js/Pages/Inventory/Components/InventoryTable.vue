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
      text: `${variant.stock_qty} · Out`,
      class: 'text-rose-600 dark:text-rose-400',
    }
  }
  if (variant.stock_qty <= variant.low_stock_threshold) {
    return {
      text: `${variant.stock_qty} · Low`,
      class: 'text-amber-600 dark:text-amber-400',
    }
  }
  return {
    text: `${variant.stock_qty}`,
    class: 'text-zinc-900 dark:text-zinc-100',
  }
}

const getClassification = (variant) => {
  const parts = [variant.product?.category?.name, variant.product?.brand?.name].filter(Boolean)
  return parts.length > 0 ? parts.join(' / ') : '—'
}

const formatPrice = (variant) => {
  const raw = variant.price_override ?? variant.product?.price ?? variant.product?.base_price
  if (raw === null || raw === undefined) return '—'
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(raw))
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="overflow-x-auto pt-2 pb-1">
      <Table>
        <TableHeader class="[&_tr]:border-0">
          <TableRow class="border-0 hover:bg-transparent">
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Product / Variant</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">SKU</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Category &amp; Brand</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Price</TableHead>
            <TableHead class="px-4 text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Stock</TableHead>
            <TableHead class="px-4 text-right text-xs font-medium text-zinc-400 dark:text-zinc-500 h-8">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody class="[&_tr]:border-0">
          <TableRow v-for="variant in inventory.data" :key="variant.id" @click="emit('select', variant)"
            class="border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 cursor-pointer transition-colors">
            <TableCell class="px-4 py-2.5">
              <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                {{ variant.product?.name }}
              </div>
              <div class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ variant.variant_label || 'Standard' }}
              </div>
            </TableCell>

            <TableCell class="px-4 py-2.5" @click.stop>
              <button type="button" @click="copySku(variant.sku)" title="Click to copy SKU"
                class="font-mono text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                <span v-if="copiedSku === variant.sku" class="text-emerald-600 dark:text-emerald-400">Copied</span>
                <span v-else>{{ variant.sku }}</span>
              </button>
            </TableCell>

            <TableCell class="px-4 py-2.5 text-[13px] text-zinc-500 dark:text-zinc-400">
              {{ getClassification(variant) }}
            </TableCell>

            <TableCell class="px-4 py-2.5 font-mono text-[13px] text-zinc-700 dark:text-zinc-300">
              {{ formatPrice(variant) }}
            </TableCell>

            <TableCell class="px-4 py-2.5">
              <span :class="['text-[13px] font-mono font-medium', getStockDisplay(variant).class]">
                {{ getStockDisplay(variant).text }}
              </span>
            </TableCell>

            <TableCell class="px-4 py-2.5 text-right" @click.stop>
              <div v-if="!variant.deleted_at" class="inline-flex items-center gap-3.5">
                <Link :href="route('products.edit', variant.product_id)"
                  class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                  Edit
                </Link>
                <button type="button" @click="emit('adjust', variant)"
                  class="text-xs font-medium text-zinc-900 hover:text-zinc-600 dark:text-zinc-200 dark:hover:text-zinc-400">
                  Adjust
                </button>
              </div>
              <button v-else type="button"
                @click="router.post(route('products.restore', variant.product_id), {}, { preserveScroll: true })"
                class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                Restore
              </button>
            </TableCell>
          </TableRow>

          <TableRow v-if="!inventory.data || inventory.data.length === 0" class="border-0 hover:bg-transparent">
            <TableCell colspan="6" class="h-28 text-center text-xs text-zinc-400 dark:text-zinc-500">
              <div>No inventory records found.</div>
              <button v-if="hasActiveFilters" type="button" @click="router.get(route('inventory.index'))"
                class="text-xs text-zinc-800 dark:text-zinc-300 underline mt-1">
                Clear filters
              </button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-if="inventory.total > 0"
      class="flex items-center justify-between px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
      <span class="font-mono">
        {{ inventory.from }}–{{ inventory.to }} of {{ inventory.total }}
      </span>

      <div v-if="inventory.last_page > 1" class="flex items-center gap-4">
        <Link v-if="inventory.prev_page_url" :href="inventory.prev_page_url" preserve-scroll preserve-state
          class="hover:text-zinc-900 dark:hover:text-zinc-100">
          Previous
        </Link>
        <span v-else class="opacity-40">Previous</span>

        <span class="font-mono">{{ inventory.current_page }} / {{ inventory.last_page }}</span>

        <Link v-if="inventory.next_page_url" :href="inventory.next_page_url" preserve-scroll preserve-state
          class="hover:text-zinc-900 dark:hover:text-zinc-100">
          Next
        </Link>
        <span v-else class="opacity-40">Next</span>
      </div>
    </div>
  </div>
</template>