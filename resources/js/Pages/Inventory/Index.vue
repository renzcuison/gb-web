<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ImportModal from '@/Components/ImportModal.vue'
import InventoryToolbar from './Components/InventoryToolbar.vue'
import InventoryTable from './Components/InventoryTable.vue'
import InventoryDetailSheet from './Components/InventoryDetailSheet.vue'
import StockAdjustmentModal from './Components/StockAdjustmentModal.vue'
import { Button } from '@/Components/ui/button'
import { Download, ChevronDown } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  inventory: Object,
  stats: {
    type: Object,
    default: () => ({ total: 0, in_stock: 0, low_stock: 0, out_of_stock: 0 }),
  },
  categories: { type: Array, default: () => [] },
  brands: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ search: '', status: 'all', category: null, brand: null, sort: 'stock_asc' }),
  },
})

const isImportModalOpen = ref(false)
const isAdjustModalOpen = ref(false)
const isDetailSheetOpen = ref(false)
const adjustingVariant = ref(null)
const inspectedVariantId = ref(null)
const inspectedVariantFallback = ref(null)

const inspectedVariant = computed(() => {
  if (!inspectedVariantId.value) return null
  return (
    props.inventory?.data?.find((v) => v.id === inspectedVariantId.value) ||
    inspectedVariantFallback.value
  )
})

const buildExportUrl = (format) => {
  const params = new URLSearchParams()
  params.set('format', format)
  if (props.filters.search) params.set('search', props.filters.search)
  if (props.filters.status && props.filters.status !== 'all') params.set('status', props.filters.status)
  if (props.filters.category) params.set('category', String(props.filters.category))
  if (props.filters.brand) params.set('brand', String(props.filters.brand))
  if (props.filters.sort && props.filters.sort !== 'stock_asc') params.set('sort', props.filters.sort)
  return `${route('inventory.export')}?${params.toString()}`
}

const openDetailSheet = (variant) => {
  inspectedVariantId.value = variant.id
  inspectedVariantFallback.value = variant
  isDetailSheetOpen.value = true
}

const openAdjustModal = (variant) => {
  adjustingVariant.value = variant
  isAdjustModalOpen.value = true
}
</script>

<template>
  <div class="w-full space-y-5">
    <Head title="Inventory Stock" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
          Inventory Stock
        </h1>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
          Monitor variant stock levels and record manual adjustments.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              size="sm"
              variant="outline"
              class="h-8 text-xs font-medium gap-1.5 border-zinc-200 dark:border-zinc-800 bg-white hover:bg-zinc-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 px-3 shadow-none">
              <Download class="h-3.5 w-3.5" />
              <span>Export</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-44 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
            <DropdownMenuItem as-child class="text-xs cursor-pointer">
              <a :href="buildExportUrl('xlsx')">Export Excel (.xlsx)</a>
            </DropdownMenuItem>
            <DropdownMenuItem as-child class="text-xs cursor-pointer">
              <a :href="buildExportUrl('csv')">Export CSV (.csv)</a>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <Button
          size="sm"
          variant="outline"
          @click="isImportModalOpen = true"
          class="h-8 text-xs font-medium border-zinc-200 dark:border-zinc-800 bg-white hover:bg-zinc-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 px-3 shadow-none">
          Import Excel
        </Button>

        <Button
          size="sm"
          as-child
          class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 px-3 shadow-none">
          <Link :href="route('products.create')">
            Add Item Group
          </Link>
        </Button>
      </div>
    </div>

    <InventoryToolbar
      :stats="stats"
      :categories="categories"
      :brands="brands"
      :filters="filters"
    />

    <InventoryTable
      :inventory="inventory"
      :has-active-filters="Boolean(filters.search || filters.status !== 'all' || filters.category || filters.brand)"
      @select="openDetailSheet"
      @adjust="openAdjustModal"
    />

    <InventoryDetailSheet
      v-model:open="isDetailSheetOpen"
      :variant="inspectedVariant"
      @adjust="openAdjustModal"
    />

    <StockAdjustmentModal
      v-model:open="isAdjustModalOpen"
      :variant="adjustingVariant"
    />

    <ImportModal v-model:open="isImportModalOpen" />
  </div>
</template>