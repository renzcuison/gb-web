<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import MovementsToolbar from './Components/MovementsToolbar.vue'
import MovementsTable from './Components/MovementsTable.vue'
import { Button } from '@/Components/ui/button'
import {
  ImsDownloadIcon,
  ImsChevronDownIcon,
} from '@/Components/Icons/ImsIcons.vue'
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

const updateFrom = (val) => {
  from.value = val
  applyFilters()
}

const updateTo = (val) => {
  to.value = val
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
            <Button
              size="sm"
              variant="outline"
              class="h-8 text-xs font-medium gap-1.5 border-zinc-200 dark:border-zinc-800 bg-white hover:bg-zinc-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 px-3 shadow-none">
              <ImsDownloadIcon class="h-3.5 w-3.5" />
              <span>Export</span>
              <ImsChevronDownIcon class="h-3 w-3 opacity-60" />
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

    <MovementsToolbar
      v-model:search="search"
      :type="type"
      :user="user"
      :from="from"
      :to="to"
      :stats="stats"
      :actors="actors"
      :has-active-filters="hasActiveFilters"
      @update:type="setType"
      @toggle-user="toggleUser"
      @update:from="updateFrom"
      @update:to="updateTo"
      @reset="resetAll"
    />

    <MovementsTable
      :movements="movements"
      :has-active-filters="hasActiveFilters"
      @reset="resetAll"
    />
  </div>
</template>