<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UrgentAlertsTable from '@/Components/Dashboard/UrgentAlertsTable.vue'
import RecentMovementsList from '@/Components/Dashboard/RecentMovementsList.vue'
import TopAllocatedStock from '@/Components/Dashboard/TopAllocatedStock.vue'
import StockAdjustmentModal from '@/Pages/Inventory/Components/StockAdjustmentModal.vue'
import { Plus } from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalItems: 0,
      archivedItems: 0,
      outOfStockCount: 0,
      lowStockCount: 0,
      newInquiriesCount: 0,
      activeAnnouncements: 0,
      totalValue: 0,
    }),
  },
  outOfStockItems: { type: Array, default: () => [] },
  lowStockItems: { type: Array, default: () => [] },
  recentAlerts: { type: Array, default: () => [] },
  recentMovements: { type: Array, default: () => [] },
  topStockedItems: { type: Array, default: () => [] },
  activeAnnouncementsList: { type: Array, default: () => [] },
  recentInquiries: { type: Array, default: () => [] },
})

const isAdjustModalOpen = ref(false)
const selectedVariant = ref(null)

const openRestockModal = (variant) => {
  if (!variant) return
  selectedVariant.value = variant
  isAdjustModalOpen.value = true
}

const formatCurrency = (val) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(val || 0))

const newInquiriesPreview = computed(() =>
  props.recentInquiries
    .filter((i) => i.status === 'new')
    .slice(0, 3)
)

const topSkusPreview = computed(() =>
  props.topStockedItems.slice(0, 3)
)

const recentMovementsPreview = computed(() =>
  props.recentMovements.slice(0, 3)
)

const outOfStockRemaining = computed(() =>
  Math.max(0, (props.stats.outOfStockCount ?? 0) - props.outOfStockItems.length)
)

const lowStockRemaining = computed(() =>
  Math.max(0, (props.stats.lowStockCount ?? 0) - props.lowStockItems.length)
)

const newInquiriesRemaining = computed(() =>
  Math.max(0, (props.stats.newInquiriesCount ?? 0) - newInquiriesPreview.value.length)
)
</script>

<template>

  <Head title="Dashboard" />

  <AdminLayout>
    <div class="w-full h-[calc(100vh-4rem)] overflow-y-auto pb-8 pr-1 space-y-5">
      <div class="flex items-center justify-between pt-1">
        <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
          Dashboard
        </h1>

        <Link :href="route('products.create')"
          class="inline-flex items-center gap-1.5 h-8 px-3 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 text-xs font-medium transition-colors">
          <Plus class="h-3.5 w-3.5" />
          <span>Add Item Group</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <Link :href="route('inventory.index', { status: 'out_of_stock' })"
          class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 transition-colors flex flex-col justify-between">
          <div>
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                Out of Stock
              </span>
              <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ stats.outOfStockCount ?? 0 }}
              </span>
            </div>

            <div v-if="outOfStockItems.length" class="mt-3 space-y-1.5">
              <div v-for="item in outOfStockItems" :key="item.id"
                class="flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-700 dark:text-zinc-300 truncate">
                  {{ item.product?.name || item.sku }}
                </span>
                <span class="font-mono text-zinc-400 dark:text-zinc-500 shrink-0">
                  {{ item.variant_label || item.sku }}
                </span>
              </div>
            </div>

            <div v-else class="mt-3 text-[11px] text-zinc-400 dark:text-zinc-500">
              No depleted variants
            </div>
          </div>

          <div v-if="outOfStockRemaining > 0"
            class="mt-3 text-[11px] text-zinc-400 group-hover:text-zinc-700 dark:text-zinc-500 dark:group-hover:text-zinc-300 transition-colors">
            +{{ outOfStockRemaining }} more depleted
          </div>
        </Link>

        <Link :href="route('inventory.index', { status: 'low_stock' })"
          class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 transition-colors flex flex-col justify-between">
          <div>
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                Low Stock
              </span>
              <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ stats.lowStockCount }}
              </span>
            </div>

            <div v-if="lowStockItems.length" class="mt-3 space-y-1.5">
              <div v-for="item in lowStockItems" :key="item.id"
                class="flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-700 dark:text-zinc-300 truncate">
                  {{ item.product?.name || item.sku }}
                </span>
                <span class="font-mono text-zinc-500 dark:text-zinc-400 shrink-0">
                  {{ item.stock_qty }}/{{ item.low_stock_threshold ?? 10 }}
                </span>
              </div>
            </div>

            <div v-else class="mt-3 text-[11px] text-zinc-400 dark:text-zinc-500">
              All variants above threshold
            </div>
          </div>

          <div v-if="lowStockRemaining > 0"
            class="mt-3 text-[11px] text-zinc-400 group-hover:text-zinc-700 dark:text-zinc-500 dark:group-hover:text-zinc-300 transition-colors">
            +{{ lowStockRemaining }} more low
          </div>
        </Link>

        <Link :href="route('inquiries.index', { status: 'new' })"
          class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 transition-colors flex flex-col justify-between">
          <div>
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                New Inquiries
              </span>
              <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ stats.newInquiriesCount ?? 0 }}
              </span>
            </div>

            <div v-if="newInquiriesPreview.length" class="mt-3 space-y-1.5">
              <div v-for="inq in newInquiriesPreview" :key="inq.id"
                class="flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-700 dark:text-zinc-300 truncate">
                  {{ inq.customer_name }} · {{ inq.product?.name || 'Inquiry' }}
                </span>
                <span class="font-mono text-zinc-400 dark:text-zinc-500 shrink-0">
                  ×{{ inq.quantity }}
                </span>
              </div>
            </div>

            <div v-else class="mt-3 text-[11px] text-zinc-400 dark:text-zinc-500">
              No unread customer inquiries
            </div>
          </div>

          <div v-if="newInquiriesRemaining > 0"
            class="mt-3 text-[11px] text-zinc-400 group-hover:text-zinc-700 dark:text-zinc-500 dark:group-hover:text-zinc-300 transition-colors">
            +{{ newInquiriesRemaining }} more unread
          </div>
        </Link>

        <Link :href="route('inventory.index')"
          class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 transition-colors flex flex-col justify-between">
          <div>
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                Active SKUs
              </span>
              <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ stats.totalItems }}
              </span>
            </div>

            <div v-if="topSkusPreview.length" class="mt-3 space-y-1.5">
              <div v-for="item in topSkusPreview" :key="item.id"
                class="flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-700 dark:text-zinc-300 truncate">
                  {{ item.product?.name || item.sku }}
                </span>
                <span class="font-mono text-zinc-500 dark:text-zinc-400 shrink-0">
                  {{ item.stock_qty }}
                </span>
              </div>
            </div>

            <div v-else class="mt-3 text-[11px] text-zinc-400 dark:text-zinc-500">
              No active SKUs logged
            </div>
          </div>

          <div
            class="mt-3 text-[11px] text-zinc-400 group-hover:text-zinc-700 dark:text-zinc-500 dark:group-hover:text-zinc-300 transition-colors">
            <span v-if="(stats.archivedItems ?? 0) > 0">{{ stats.archivedItems }} archived</span>
            <span v-else>Browse full catalog</span>
          </div>
        </Link>

        <!-- Stock Valuation -->
        <Link :href="route('inventory.movements.index')"
          class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 transition-colors flex flex-col justify-between">
          <div>
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 shrink-0">
                Valuation
              </span>
              <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                {{ formatCurrency(stats.totalValue) }}
              </span>
            </div>

            <div v-if="recentMovementsPreview.length" class="mt-3 space-y-1.5">
              <div v-for="move in recentMovementsPreview" :key="move.id"
                class="flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-700 dark:text-zinc-300 truncate">
                  {{ move.variant?.product?.name || move.variant?.sku || 'Item' }}
                </span>
                <span class="font-mono text-zinc-500 dark:text-zinc-400 shrink-0">
                  {{ move.type === 'in' ? '+' : '-' }}{{ move.quantity }}
                </span>
              </div>
            </div>

            <div v-else class="mt-3 text-[11px] text-zinc-400 dark:text-zinc-500">
              No recent stock movements
            </div>
          </div>

          <div
            class="mt-3 text-[11px] text-zinc-400 group-hover:text-zinc-700 dark:text-zinc-500 dark:group-hover:text-zinc-300 transition-colors">
            View stock ledger
          </div>
        </Link>
      </div>

      <div class="grid gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 space-y-5">
          <UrgentAlertsTable :alerts="recentAlerts" @restock="openRestockModal" />
          <RecentMovementsList :movements="recentMovements" />
        </div>

        <div class="space-y-5">
          <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h2 class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Open Inquiries</h2>
              <Link :href="route('inquiries.index')"
                class="text-[11px] font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                View all
              </Link>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
              <Link v-for="inq in recentInquiries" :key="inq.id"
                :href="route('inquiries.index', { status: inq.status })"
                class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <div class="min-w-0">
                  <div class="text-xs font-medium text-zinc-900 dark:text-zinc-100 truncate">
                    {{ inq.customer_name }}
                  </div>
                  <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                    {{ inq.product?.name ?? 'Product' }}
                    <span v-if="inq.variant"> · {{ inq.variant.variant_label }}</span>
                    <span class="font-mono"> (×{{ inq.quantity }})</span>
                  </div>
                </div>

                <span :class="[
                  'shrink-0 text-xs font-medium',
                  inq.status === 'new'
                    ? 'text-blue-600 dark:text-blue-400'
                    : 'text-amber-600 dark:text-amber-400',
                ]">
                  {{ inq.status === 'new' ? 'New' : 'Contacted' }}
                </span>
              </Link>

              <div v-if="recentInquiries.length === 0"
                class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
                No open inquiries.
              </div>
            </div>
          </div>

          <TopAllocatedStock :items="topStockedItems" />

          <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h2 class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Active Notices</h2>
              <span class="font-mono text-[11px] text-zinc-400 dark:text-zinc-500">
                {{ stats.activeAnnouncements }}
              </span>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
              <div v-for="notice in activeAnnouncementsList" :key="notice.id" class="px-4 py-2.5">
                <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                  {{ notice.content }}
                </p>
              </div>

              <div v-if="activeAnnouncementsList.length === 0"
                class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
                No active notices.
              </div>
            </div>
          </div>
        </div>
      </div>

      <StockAdjustmentModal v-model:open="isAdjustModalOpen" :variant="selectedVariant" />
    </div>
  </AdminLayout>
</template>