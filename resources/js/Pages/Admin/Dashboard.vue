<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DashboardHeader from '@/Components/Dashboard/DashboardHeader.vue'
import TotalVariantsCard from '@/Components/Dashboard/TotalVariantsCard.vue'
import LowStockCard from '@/Components/Dashboard/LowStockCard.vue'
import InventoryValueCard from '@/Components/Dashboard/InventoryValueCard.vue'
import ActiveNoticesCard from '@/Components/Dashboard/ActiveNoticesCard.vue'
import UrgentAlertsTable from '@/Components/Dashboard/UrgentAlertsTable.vue'
import TopAllocatedStock from '@/Components/Dashboard/TopAllocatedStock.vue'

defineProps({
  stats: {
    type: Object,
    default: () => ({ totalItems: 0, lowStockCount: 0, activeAnnouncements: 0, totalValue: 0 })
  },
  recentAlerts: { type: Array, default: () => [] },
  topStockedItems: { type: Array, default: () => [] },
  recentVariants: { type: Array, default: () => [] },
  activeAnnouncementsList: { type: Array, default: () => [] }
})
</script>

<template>

  <Head title="Dashboard" />

  <AdminLayout>
    <div class="space-y-6">
      <DashboardHeader />

      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <TotalVariantsCard :count="stats.totalItems"
          :variants="recentVariants.length ? recentVariants : topStockedItems" />
        <LowStockCard :count="stats.lowStockCount" :alerts="recentAlerts" />
        <InventoryValueCard :total-value="stats.totalValue" :top-items="topStockedItems" />
        <ActiveNoticesCard :count="stats.activeAnnouncements" :notices="activeAnnouncementsList" />
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
          <UrgentAlertsTable :alerts="recentAlerts" />
        </div>
        <div>
          <TopAllocatedStock :items="topStockedItems" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>