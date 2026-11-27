<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StockAdjustmentModal from '@/Pages/Inventory/Components/StockAdjustmentModal.vue'
import AlertStatsGrid from './Components/AlertStatsGrid.vue'
import AlertsTable from './Components/AlertsTable.vue'
import {
  ImsSearchIcon,
  ImsStatusSuccessIcon,
} from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  alerts: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({
      unresolved: 0,
      out_of_stock: 0,
      low_stock: 0,
      resolved: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      status: 'unresolved',
      severity: 'all',
    }),
  },
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'unresolved')
const severity = ref(props.filters.severity ?? 'all')

const isAdjustModalOpen = ref(false)
const selectedVariant = ref(null)

let searchTimeout = null
const applyFilters = () => {
  router.get(
    route('inventory.alerts.index'),
    {
      search: search.value || undefined,
      status: status.value,
      severity: severity.value !== 'all' ? severity.value : undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 300)
})

const setStatus = (val) => {
  status.value = val
  if (val === 'resolved') severity.value = 'all'
  applyFilters()
}

const setSeverity = (val) => {
  status.value = 'unresolved'
  severity.value = val
  applyFilters()
}

const openRestockModal = (variant) => {
  if (!variant) return
  selectedVariant.value = variant
  isAdjustModalOpen.value = true
}

const resolveAlert = (alert) => {
  router.patch(route('inventory.alerts.resolve', alert.id), {}, { preserveScroll: true })
}

const resolveAllAlerts = () => {
  router.post(route('inventory.alerts.resolve-all'), {}, { preserveScroll: true })
}
</script>

<template>
  <Head title="Inventory Alerts" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
            Inventory Alerts
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Alerts auto-resolve when stock is replenished above the variant threshold.
          </p>
        </div>

        <button
          v-if="stats.unresolved > 0"
          type="button"
          @click="resolveAllAlerts"
          class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
          <ImsStatusSuccessIcon class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
          Resolve All ({{ stats.unresolved }})
        </button>
      </div>

      <AlertStatsGrid
        :stats="stats"
        :status="status"
        :severity="severity"
        @select-severity="setSeverity"
        @select-status="setStatus" />

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 text-xs">
          <button
            v-for="tab in [
              { key: 'unresolved', label: 'Open' },
              { key: 'resolved', label: 'Resolved' },
              { key: 'all', label: 'All' },
            ]"
            :key="tab.key"
            type="button"
            @click="setStatus(tab.key)"
            :class="[
              'rounded-md px-3 py-1 font-medium transition-colors',
              status === tab.key
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                : 'text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100',
            ]">
            {{ tab.label }}
          </button>
        </div>

        <div class="relative w-full sm:w-72">
          <ImsSearchIcon class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Search SKU, product, variant..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

      <AlertsTable
        :alerts="alerts"
        @restock="openRestockModal"
        @resolve="resolveAlert" />

      <StockAdjustmentModal v-model:open="isAdjustModalOpen" :variant="selectedVariant" />
    </div>
  </AdminLayout>
</template>