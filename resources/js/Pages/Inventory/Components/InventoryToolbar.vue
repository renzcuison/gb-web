<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Search, X, ChevronDown, Check } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

const props = defineProps({
  stats: Object,
  categories: { type: Array, default: () => [] },
  brands: { type: Array, default: () => [] },
  filters: Object,
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || 'all')
const category = ref(props.filters.category || null)
const brand = ref(props.filters.brand || null)
const sort = ref(props.filters.sort || 'stock_asc')

const statusTabs = computed(() => [
  { key: 'all', label: 'All', count: props.stats.total },
  { key: 'in_stock', label: 'In Stock', count: props.stats.in_stock },
  { key: 'low_stock', label: 'Low Stock', count: props.stats.low_stock },
  { key: 'out_of_stock', label: 'Out of Stock', count: props.stats.out_of_stock },
])

const sortOptions = [
  { value: 'stock_asc', label: 'Lowest Stock' },
  { value: 'stock_desc', label: 'Highest Stock' },
  { value: 'name_asc', label: 'Name (A–Z)' },
  { value: 'sku_asc', label: 'SKU (A–Z)' },
]

const currentSortLabel = computed(
  () => sortOptions.find((s) => s.value === sort.value)?.label || 'Sort'
)
const selectedCategoryName = computed(
  () => props.categories.find((c) => c.id === category.value)?.name || 'Category'
)
const selectedBrandName = computed(
  () => props.brands.find((b) => b.id === brand.value)?.name || 'Brand'
)
const hasActiveFilters = computed(
  () => Boolean(search.value) || status.value !== 'all' || category.value !== null || brand.value !== null
)

const applyFilters = () => {
  router.get(
    route('inventory.index'),
    {
      search: search.value || undefined,
      status: status.value !== 'all' ? status.value : undefined,
      category: category.value || undefined,
      brand: brand.value || undefined,
      sort: sort.value !== 'stock_asc' ? sort.value : undefined,
    },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

let timer = null
watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(applyFilters, 300)
})

const setStatus = (val) => {
  status.value = val
  applyFilters()
}

const toggleCategory = (id) => {
  category.value = category.value === id ? null : id
  applyFilters()
}

const toggleBrand = (id) => {
  brand.value = brand.value === id ? null : id
  applyFilters()
}

const setSort = (val) => {
  sort.value = val
  applyFilters()
}

const resetAll = () => {
  search.value = ''
  status.value = 'all'
  category.value = null
  brand.value = null
  sort.value = 'stock_asc'
  applyFilters()
}
</script>

<template>
  <div class="space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="relative flex-1 max-w-xs">
        <Search class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <Input
          v-model="search"
          type="text"
          placeholder="Search item or SKU..."
          class="h-8 pl-8 pr-7 text-xs bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 text-slate-900 dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus-visible:ring-1 shadow-none"
        />
        <button
          v-if="search"
          type="button"
          @click="search = ''"
          class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300">
          <X class="h-3.5 w-3.5" />
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              :class="[
                'h-8 text-xs font-medium gap-1.5 px-2.5 shadow-none border-slate-200/80 dark:border-zinc-800',
                category
                  ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                  : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300',
              ]">
              <span>{{ selectedCategoryName }}</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-44 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
            <template v-if="categories.length > 0">
              <DropdownMenuItem
                v-for="cat in categories"
                :key="cat.id"
                @click="toggleCategory(cat.id)"
                class="text-xs flex items-center justify-between cursor-pointer">
                <span>{{ cat.name }}</span>
                <Check v-if="category === cat.id" class="h-3.5 w-3.5" />
              </DropdownMenuItem>
            </template>
            <div v-else class="px-2.5 py-2 text-xs text-slate-400 dark:text-zinc-500">
              No categories
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              :class="[
                'h-8 text-xs font-medium gap-1.5 px-2.5 shadow-none border-slate-200/80 dark:border-zinc-800',
                brand
                  ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                  : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300',
              ]">
              <span>{{ selectedBrandName }}</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-44 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
            <template v-if="brands.length > 0">
              <DropdownMenuItem
                v-for="b in brands"
                :key="b.id"
                @click="toggleBrand(b.id)"
                class="text-xs flex items-center justify-between cursor-pointer">
                <span>{{ b.name }}</span>
                <Check v-if="brand === b.id" class="h-3.5 w-3.5" />
              </DropdownMenuItem>
            </template>
            <div v-else class="px-2.5 py-2 text-xs text-slate-400 dark:text-zinc-500">
              No brands
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-8 text-xs font-medium gap-1.5 px-2.5 bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 text-slate-600 dark:text-zinc-300 shadow-none">
              <span>{{ currentSortLabel }}</span>
              <ChevronDown class="h-3 w-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-40 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
            <DropdownMenuItem
              v-for="opt in sortOptions"
              :key="opt.value"
              @click="setSort(opt.value)"
              class="text-xs flex items-center justify-between cursor-pointer">
              <span>{{ opt.label }}</span>
              <Check v-if="sort === opt.value" class="h-3.5 w-3.5" />
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <button
          v-if="hasActiveFilters"
          type="button"
          @click="resetAll"
          class="text-xs text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 px-1.5 transition-colors">
          Reset
        </button>
      </div>
    </div>

    <div class="flex items-center gap-5 border-b border-slate-200/80 dark:border-zinc-800 text-xs">
      <button
        v-for="tab in statusTabs"
        :key="tab.key"
        type="button"
        @click="setStatus(tab.key)"
        :class="[
          '-mb-px pb-2 border-b-2 transition-colors flex items-center gap-1.5',
          status === tab.key
            ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-medium'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200',
        ]">
        <span>{{ tab.label }}</span>
        <span class="font-mono text-[11px] opacity-60">{{ tab.count }}</span>
      </button>
    </div>
  </div>
</template>