<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboard/DashboardHeader.vue'
import KpiStrip from '@/Components/Dashboard/KpiStrip.vue'
import UrgentAlertsTable from '@/Components/Dashboard/UrgentAlertsTable.vue'
import RecentMovementsList from '@/Components/Dashboard/RecentMovementsList.vue'
import OpenInquiriesList from '@/Components/Dashboard/OpenInquiriesList.vue'
import TopAllocatedStock from '@/Components/Dashboard/TopAllocatedStock.vue'
import StockAdjustmentModal from '@/Pages/Inventory/Components/StockAdjustmentModal.vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalItems: 0,
      archivedItems: 0,
      outOfStockCount: 0,
      lowStockCount: 0,
      newInquiriesCount: 0,
      totalValue: 0,
      totalUnits: 0,
    }),
  },
  recentAlerts: { type: Array, default: () => [] },
  recentMovements: { type: Array, default: () => [] },
  topStockedItems: { type: Array, default: () => [] },
  recentInquiries: { type: Array, default: () => [] },
})

const isAdjustModalOpen = ref(false)
const selectedVariant = ref(null)

const openRestockModal = (variant) => {
  if (!variant) return
  selectedVariant.value = variant
  isAdjustModalOpen.value = true
}

const fallbackUnits = computed(() =>
  props.topStockedItems.reduce((sum, item) => sum + Number(item.stock_qty || 0), 0)
)
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout>
    <div class="w-full h-[calc(100vh-4rem)] overflow-y-auto pb-8 pr-1 space-y-5 [&_*]:!transition-none [&_*]:!animate-none">
      <DashboardHeader />

      <KpiStrip :stats="stats" :fallback-units="fallbackUnits" />

      <div class="grid gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 space-y-5">
          <UrgentAlertsTable :alerts="recentAlerts" @restock="openRestockModal" />
          <RecentMovementsList :movements="recentMovements" />
        </div>

        <div class="space-y-5">
          <OpenInquiriesList :inquiries="recentInquiries" />
          <TopAllocatedStock :items="topStockedItems" />
        </div>
      </div>

      <StockAdjustmentModal v-model:open="isAdjustModalOpen" :variant="selectedVariant" />
    </div>
  </AdminLayout>
</template>