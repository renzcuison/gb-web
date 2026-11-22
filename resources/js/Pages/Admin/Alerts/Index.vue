<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StockAdjustmentModal from '@/Pages/Inventory/Components/StockAdjustmentModal.vue'
import {
  Search,
  CheckCircle2,
  PackagePlus,
  Check,
  AlertTriangle,
  XCircle,
} from 'lucide-vue-next'

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

const formatDate = (iso) => {
  if (!iso) return '—'
  return new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(iso))
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
          <CheckCircle2 class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
          Resolve All ({{ stats.unresolved }})
        </button>
      </div>

      <div
        class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
        <button
          type="button"
          @click="setSeverity('all')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'unresolved' && severity === 'all'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Open Alerts</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.unresolved }}
          </div>
        </button>

        <button
          type="button"
          @click="setSeverity('out_of_stock')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'unresolved' && severity === 'out_of_stock'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Out of Stock</div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              stats.out_of_stock > 0
                ? 'text-rose-600 dark:text-rose-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.out_of_stock }}
          </div>
        </button>

        <button
          type="button"
          @click="setSeverity('low_stock')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'unresolved' && severity === 'low_stock'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Low Stock</div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              stats.low_stock > 0
                ? 'text-amber-600 dark:text-amber-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.low_stock }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('resolved')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'resolved'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Resolved History</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.resolved }}
          </div>
        </button>
      </div>

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
          <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Search SKU, product, variant..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

      <!-- Alerts Table -->
      <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200/80 dark:border-zinc-800 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                <th class="px-4 py-3">Product / Variant</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Current Stock</th>
                <th class="px-4 py-3">Threshold</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Triggered</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
              <tr
                v-for="alert in alerts.data"
                :key="alert.id"
                class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900 dark:text-zinc-100">
                    {{ alert.variant?.product?.name ?? 'Unknown Product' }}
                  </div>
                  <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                    {{ alert.variant?.variant_label }}
                    <span v-if="alert.variant?.product?.category">
                      · {{ alert.variant.product.category.name }}
                    </span>
                  </div>
                </td>

                <td class="px-4 py-3 font-mono text-[11px] text-slate-600 dark:text-zinc-300">
                  {{ alert.variant?.sku }}
                </td>

                <td class="px-4 py-3 font-mono font-semibold">
                  <span
                    :class="[
                      (alert.variant?.stock_qty ?? 0) <= 0
                        ? 'text-rose-600 dark:text-rose-400'
                        : (alert.variant?.stock_qty ?? 0) <= (alert.variant?.low_stock_threshold ?? 0)
                          ? 'text-amber-600 dark:text-amber-400'
                          : 'text-emerald-600 dark:text-emerald-400',
                    ]">
                    {{ alert.variant?.stock_qty ?? 0 }}
                  </span>
                </td>

                <td class="px-4 py-3 font-mono text-slate-500 dark:text-zinc-400">
                  ≤ {{ alert.variant?.low_stock_threshold ?? alert.threshold_reached }}
                </td>

                <td class="px-4 py-3">
                  <span
                    v-if="alert.is_resolved"
                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:text-emerald-400">
                    <CheckCircle2 class="h-3 w-3" />
                    Resolved
                  </span>
                  <span
                    v-else-if="(alert.variant?.stock_qty ?? 0) <= 0"
                    class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 text-[11px] font-medium text-rose-700 dark:text-rose-400">
                    <XCircle class="h-3 w-3" />
                    Out of Stock
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                    <AlertTriangle class="h-3 w-3" />
                    Low Stock
                  </span>
                </td>

                <td class="px-4 py-3 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                  {{ formatDate(alert.created_at) }}
                </td>

                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <div class="inline-flex items-center justify-end gap-2">
                    <button
                      type="button"
                      @click="openRestockModal(alert.variant)"
                      class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                      <PackagePlus class="h-3 w-3" />
                      Restock
                    </button>

                    <button
                      v-if="!alert.is_resolved"
                      type="button"
                      @click="resolveAlert(alert)"
                      title="Dismiss alert"
                      class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2 py-1 text-[11px] font-medium text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                      <Check class="h-3 w-3" />
                      Resolve
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="alerts.data.length === 0">
                <td colspan="7" class="py-12 text-center text-xs text-slate-400 dark:text-zinc-500">
                  No inventory alerts match the current filter.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="alerts.links && alerts.links.length > 3"
          class="flex items-center justify-between border-t border-slate-200/80 dark:border-zinc-800 px-4 py-3 text-xs text-slate-500 dark:text-zinc-400">
          <div>
            Showing <span class="font-mono">{{ alerts.from ?? 0 }}</span>–<span class="font-mono">{{ alerts.to ?? 0 }}</span> of
            <span class="font-mono">{{ alerts.total }}</span>
          </div>
          <div class="flex items-center gap-1">
            <template v-for="(link, idx) in alerts.links" :key="idx">
              <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                v-html="link.label"
                :class="[
                  'rounded-md px-2.5 py-1 transition-colors',
                  link.active
                    ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-medium'
                    : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400',
                ]" />
              <span
                v-else
                v-html="link.label"
                class="px-2.5 py-1 text-slate-300 dark:text-zinc-600" />
            </template>
          </div>
        </div>
      </div>

      <StockAdjustmentModal v-model:open="isAdjustModalOpen" :variant="selectedVariant" />
    </div>
  </AdminLayout>
</template>