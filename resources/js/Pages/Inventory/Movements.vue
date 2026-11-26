<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Search, X, ChevronDown, Check, Download } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  movements: Object,
  stats: {
    type: Object,
    default: () => ({ total: 0, in_count: 0, out_count: 0, units_in: 0, units_out: 0 }),
  },
  actors: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ search: '', type: 'all', user: null, from: '', to: '' }),
  },
})

const search = ref(props.filters.search || '')
const type = ref(props.filters.type || 'all')
const user = ref(props.filters.user || null)
const from = ref(props.filters.from || '')
const to = ref(props.filters.to || '')

const typeTabs = computed(() => [
  { key: 'all', label: 'All Movements', count: props.stats.total },
  { key: 'in', label: 'Stock In', count: props.stats.in_count },
  { key: 'out', label: 'Stock Out', count: props.stats.out_count },
])

const selectedActorName = computed(
  () => props.actors.find((a) => a.id === user.value)?.name || 'Recorded By'
)

const hasActiveFilters = computed(
  () =>
    Boolean(search.value) ||
    type.value !== 'all' ||
    user.value !== null ||
    Boolean(from.value) ||
    Boolean(to.value)
)

const buildExportUrl = (format) => {
  const params = new URLSearchParams()
  params.set('format', format)
  if (search.value) params.set('search', search.value)
  if (type.value && type.value !== 'all') params.set('type', type.value)
  if (user.value) params.set('user', String(user.value))
  if (from.value) params.set('from', from.value)
  if (to.value) params.set('to', to.value)
  return `${route('inventory.movements.export')}?${params.toString()}`
}

const applyFilters = () => {
  router.get(
    route('inventory.movements.index'),
    {
      search: search.value || undefined,
      type: type.value !== 'all' ? type.value : undefined,
      user: user.value || undefined,
      from: from.value || undefined,
      to: to.value || undefined,
    },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

let timer = null
watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(applyFilters, 300)
})

const setType = (val) => {
  type.value = val
  applyFilters()
}

const toggleUser = (id) => {
  user.value = user.value === id ? null : id
  applyFilters()
}

const resetAll = () => {
  search.value = ''
  type.value = 'all'
  user.value = null
  from.value = ''
  to.value = ''
  applyFilters()
}

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
  <div class="w-full space-y-5">

    <Head title="Movement Logs" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
          Movement Logs
        </h1>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
          Audit history of all stock-in and stock-out adjustments.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2.5 text-xs font-mono">
          <span class="text-emerald-600 dark:text-emerald-400">
            +{{ stats.units_in.toLocaleString() }} in
          </span>
          <span class="text-zinc-300 dark:text-zinc-700">/</span>
          <span class="text-rose-600 dark:text-rose-400">
            -{{ stats.units_out.toLocaleString() }} out
          </span>
        </div>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button size="sm" variant="outline"
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
      </div>
    </div>

    <div class="space-y-3">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2">
        <div class="relative flex-1 max-w-xs">
          <Search
            class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          <Input v-model="search" type="text" placeholder="Search item, SKU, or reason..."
            class="h-8 pl-8 pr-7 text-xs bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:ring-1 shadow-none" />
          <button v-if="search" type="button" @click="search = ''"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            <X class="h-3.5 w-3.5" />
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm" :class="[
                'h-8 text-xs font-medium gap-1.5 px-2.5 shadow-none bg-white dark:bg-zinc-900',
                user
                  ? 'border-zinc-900 text-zinc-900 dark:border-zinc-200 dark:text-zinc-100'
                  : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300',
              ]">
                <span>{{ selectedActorName }}</span>
                <ChevronDown class="h-3 w-3 opacity-60" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
              <template v-if="actors.length > 0">
                <DropdownMenuItem v-for="actor in actors" :key="actor.id" @click="toggleUser(actor.id)"
                  class="text-xs flex items-center justify-between cursor-pointer">
                  <span>{{ actor.name }}</span>
                  <Check v-if="user === actor.id" class="h-3.5 w-3.5" />
                </DropdownMenuItem>
              </template>
              <div v-else class="px-2.5 py-2 text-xs text-zinc-400 dark:text-zinc-500">
                No recorded users
              </div>
            </DropdownMenuContent>
          </DropdownMenu>

          <div class="flex items-center gap-1.5">
            <input v-model="from" type="date" aria-label="From date" @change="applyFilters"
              class="h-8 rounded-md border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-2.5 text-xs text-zinc-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-400" />
            <span class="text-xs text-zinc-400">to</span>
            <input v-model="to" type="date" aria-label="To date" @change="applyFilters"
              class="h-8 rounded-md border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-2.5 text-xs text-zinc-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-400" />
          </div>

          <button v-if="hasActiveFilters" type="button" @click="resetAll"
            class="text-xs text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 px-1.5">
            Reset
          </button>
        </div>
      </div>

      <div class="flex items-center gap-6 border-b border-zinc-200 dark:border-zinc-800 text-xs">
        <button v-for="tab in typeTabs" :key="tab.key" type="button" @click="setType(tab.key)" :class="[
          '-mb-px pb-2.5 border-b-2 transition-colors flex items-center gap-1.5',
          type === tab.key
            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-medium'
            : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200',
        ]">
          <span>{{ tab.label }}</span>
          <span class="font-mono text-[11px] text-zinc-400">{{ tab.count }}</span>
        </button>
      </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow
              class="border-b border-zinc-200 dark:border-zinc-800 hover:bg-transparent bg-zinc-50/60 dark:bg-zinc-900">
              <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Timestamp</TableHead>
              <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Product / Variant</TableHead>
              <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">SKU</TableHead>
              <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Reason</TableHead>
              <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Recorded By</TableHead>
              <TableHead class="text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Change</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="move in movements.data" :key="move.id"
              class="border-b border-zinc-100 dark:border-zinc-800/70 hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
              <TableCell class="py-2.5 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap font-mono">
                {{ formatTimestamp(move.created_at) }}
              </TableCell>

              <TableCell class="py-2.5">
                <div class="text-xs font-medium text-zinc-900 dark:text-zinc-100">
                  {{ move.variant?.product?.name || 'Deleted Product' }}
                  <span v-if="move.variant?.deleted_at || move.variant?.product?.deleted_at"
                    class="text-[11px] font-normal text-zinc-400 ml-1">
                    (Archived)
                  </span>
                </div>
                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                  {{ move.variant?.variant_label || 'Standard' }}
                </div>
              </TableCell>

              <TableCell class="py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                {{ move.variant?.sku || '—' }}
              </TableCell>

              <TableCell class="py-2.5 text-xs text-zinc-600 dark:text-zinc-300 max-w-xs truncate">
                {{ move.reason || (move.type === 'in' ? 'Manual Stock In' : 'Manual Stock Out') }}
              </TableCell>

              <TableCell class="py-2.5 text-xs text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                {{ move.user?.name || 'System' }}
              </TableCell>

              <TableCell class="py-2.5 text-right whitespace-nowrap">
                <span :class="[
                  'font-mono text-xs font-medium',
                  move.type === 'in'
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-rose-600 dark:text-rose-400',
                ]">
                  {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
                </span>
              </TableCell>
            </TableRow>

            <TableRow v-if="!movements.data || movements.data.length === 0">
              <TableCell colspan="6" class="h-28 text-center text-xs text-zinc-400 dark:text-zinc-500">
                <div>No movement records found.</div>
                <button v-if="hasActiveFilters" type="button" @click="resetAll"
                  class="text-xs text-zinc-800 dark:text-zinc-300 underline mt-1">
                  Clear filters
                </button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="movements.total > 0"
        class="flex items-center justify-between px-4 py-2.5 border-t border-zinc-200 dark:border-zinc-800 text-[11px] text-zinc-500 dark:text-zinc-400">
        <span class="font-mono">
          {{ movements.from }}–{{ movements.to }} of {{ movements.total }}
        </span>

        <div v-if="movements.last_page > 1" class="flex items-center gap-4">
          <Link v-if="movements.prev_page_url" :href="movements.prev_page_url" preserve-scroll preserve-state
            class="hover:text-zinc-900 dark:hover:text-zinc-100">
            Previous
          </Link>
          <span v-else class="opacity-40">Previous</span>

          <span class="font-mono">{{ movements.current_page }} / {{ movements.last_page }}</span>

          <Link v-if="movements.next_page_url" :href="movements.next_page_url" preserve-scroll preserve-state
            class="hover:text-zinc-900 dark:hover:text-zinc-100">
            Next
          </Link>
          <span v-else class="opacity-40">Next</span>
        </div>
      </div>
    </div>
  </div>
</template>