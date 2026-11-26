<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UrgentAlertsTable from '@/Components/Dashboard/UrgentAlertsTable.vue'
import RecentMovementsList from '@/Components/Dashboard/RecentMovementsList.vue'
import TopAllocatedStock from '@/Components/Dashboard/TopAllocatedStock.vue'
import StockAdjustmentModal from '@/Pages/Inventory/Components/StockAdjustmentModal.vue'

defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalItems: 0,
      outOfStockCount: 0,
      lowStockCount: 0,
      newInquiriesCount: 0,
      activeAnnouncements: 0,
      totalValue: 0,
    }),
  },
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
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout>
    <div class="w-full h-[calc(100vh-4rem)] overflow-y-auto pb-8 pr-1 space-y-5">
      <div class="flex items-center justify-between pt-1">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
          Dashboard
        </h1>

        <Link
          :href="route('products.create')"
          class="text-xs font-medium text-slate-700 hover:text-slate-950 dark:text-zinc-300 dark:hover:text-white transition-colors">
          + Add Item Group
        </Link>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <Link
          :href="route('inventory.index', { status: 'out_of_stock' })"
          class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors block">
          <div class="flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
            <span>Out of Stock</span>
            <span>→</span>
          </div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              (stats.outOfStockCount ?? 0) > 0
                ? 'text-rose-600 dark:text-rose-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.outOfStockCount ?? 0 }}
          </div>
        </Link>

        <Link
          :href="route('inventory.index', { status: 'low_stock' })"
          class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors block">
          <div class="flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
            <span>Low Stock</span>
            <span>→</span>
          </div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              stats.lowStockCount > 0
                ? 'text-amber-600 dark:text-amber-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.lowStockCount }}
          </div>
        </Link>

        <Link
          :href="route('inquiries.index', { status: 'new' })"
          class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors block">
          <div class="flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
            <span>New Inquiries</span>
            <span>→</span>
          </div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              (stats.newInquiriesCount ?? 0) > 0
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.newInquiriesCount ?? 0 }}
          </div>
        </Link>

        <Link
          :href="route('inventory.index')"
          class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors block">
          <div class="flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
            <span>Total SKUs</span>
            <span>→</span>
          </div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.totalItems }}
          </div>
        </Link>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-3.5 col-span-2 lg:col-span-1">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Stock Valuation</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ formatCurrency(stats.totalValue) }}
          </div>
        </div>
      </div>

      <div class="grid gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 space-y-5">
          <UrgentAlertsTable :alerts="recentAlerts" @restock="openRestockModal" />
          <RecentMovementsList :movements="recentMovements" />
        </div>

        <div class="space-y-5">
          <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
              <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Open Inquiries</h2>
              <Link
                :href="route('inquiries.index')"
                class="text-[11px] font-medium text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                View all →
              </Link>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-zinc-800/60">
              <Link
                v-for="inq in recentInquiries"
                :key="inq.id"
                :href="route('inquiries.index', { status: inq.status })"
                class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <div class="min-w-0">
                  <div class="text-xs font-medium text-slate-900 dark:text-zinc-100 truncate">
                    {{ inq.customer_name }}
                  </div>
                  <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                    {{ inq.product?.name ?? 'Product' }}
                    <span v-if="inq.variant"> · {{ inq.variant.variant_label }}</span>
                    <span class="font-mono"> (×{{ inq.quantity }})</span>
                  </div>
                </div>

                <span
                  :class="[
                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium',
                    inq.status === 'new'
                      ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'
                      : 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400',
                  ]">
                  {{ inq.status === 'new' ? 'New' : 'Contacted' }}
                </span>
              </Link>

              <div v-if="recentInquiries.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
                No open inquiries.
              </div>
            </div>
          </div>

          <TopAllocatedStock :items="topStockedItems" />

          <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
              <h2 class="text-xs font-medium text-slate-700 dark:text-zinc-300">Active Notices</h2>
              <span class="font-mono text-[11px] text-slate-400 dark:text-zinc-500">
                {{ stats.activeAnnouncements }}
              </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-zinc-800/60">
              <div v-for="notice in activeAnnouncementsList" :key="notice.id" class="px-4 py-2.5">
                <p class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed">
                  {{ notice.content }}
                </p>
              </div>

              <div v-if="activeAnnouncementsList.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
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