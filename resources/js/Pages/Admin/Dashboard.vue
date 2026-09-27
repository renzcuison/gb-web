<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import {
  Package,
  AlertTriangle,
  Megaphone,
  TrendingUp,
  ArrowRight,
  ShieldAlert,
  PlusCircle
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalItems: 0,
      lowStockCount: 0,
      activeAnnouncements: 0,
      totalValue: 0
    })
  },
  recentAlerts: {
    type: Array,
    default: () => []
  },
  topStockedItems: {
    type: Array,
    default: () => []
  }
})

const formattedTotalValue = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(props.stats.totalValue)
})
</script>

<template>

  <Head title="Admin Dashboard" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header Title & Subtitle -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-zinc-100">Control Center</h1>
        <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1">
          Real-time overview of your operational inventory metrics, system conditions, and retail configurations.
        </p>
      </div>

      <!-- Stat Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Variants -->
        <div
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between dark:border-zinc-800 dark:bg-zinc-900">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Total Variants</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-zinc-100">{{ props.stats.totalItems }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
            <Package class="h-5 w-5" />
          </div>
        </div>

        <!-- Low Stock Warnings -->
        <div
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between dark:border-zinc-800 dark:bg-zinc-900">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Low Stock Warnings</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-zinc-100">{{ props.stats.lowStockCount }}</h3>
          </div>
          <div class="p-3 rounded-lg"
            :class="props.stats.lowStockCount > 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-slate-50 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500'">
            <AlertTriangle class="h-5 w-5" />
          </div>
        </div>

        <!-- Inventory Value -->
        <div
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between dark:border-zinc-800 dark:bg-zinc-900">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Inventory Value</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-zinc-100">{{ formattedTotalValue }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
            <TrendingUp class="h-5 w-5" />
          </div>
        </div>

        <!-- Active Notices -->
        <div
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between dark:border-zinc-800 dark:bg-zinc-900">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Active Notices</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-zinc-100">{{ props.stats.activeAnnouncements }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
            <Megaphone class="h-5 w-5" />
          </div>
        </div>
      </div>

      <!-- Main Section -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- Urgent System Alerts -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <ShieldAlert class="h-5 w-5 text-rose-500" />
              <h2 class="text-lg font-semibold text-slate-900 dark:text-zinc-100">Urgent System Alerts</h2>
            </div>
            <Button variant="ghost" size="sm" as-child
              class="text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
              <Link href="/inventory/alerts" class="flex items-center gap-1">
                <span>View all</span>
                <ArrowRight class="h-4 w-4" />
              </Link>
            </Button>
          </div>

          <div
            class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-zinc-800 dark:bg-zinc-900">
            <Table>
              <TableHeader>
                <TableRow class="dark:border-zinc-800 dark:hover:bg-zinc-800/50">
                  <TableHead class="dark:text-zinc-400">Status</TableHead>
                  <TableHead class="dark:text-zinc-400">Item / SKU</TableHead>
                  <TableHead class="dark:text-zinc-400">Details</TableHead>
                  <TableHead class="text-right dark:text-zinc-400">Stock State</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="alert in props.recentAlerts" :key="alert.id"
                  class="dark:border-zinc-800 dark:hover:bg-zinc-800/50">
                  <TableCell>
                    <Badge :variant="alert.is_resolved ? 'secondary' : 'destructive'">
                      {{ alert.is_resolved ? 'Resolved' : 'Active Warning' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="font-medium text-slate-900 dark:text-zinc-100">
                      {{ alert.product_variant?.product?.name || 'Deleted Product' }}
                    </div>
                    <div class="text-xs font-mono text-slate-500 dark:text-zinc-400">
                      {{ alert.product_variant?.sku }} <span v-if="alert.product_variant?.variant_label">({{
                        alert.product_variant.variant_label }})</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-slate-500 dark:text-zinc-400 text-sm">
                    Stock dropped to or below threshold of {{ alert.threshold_reached }} units.
                  </TableCell>
                  <TableCell class="text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                    {{ alert.product_variant?.stock_qty ?? 0 }} Left
                  </TableCell>
                </TableRow>
                <TableRow v-if="props.recentAlerts.length === 0" class="dark:border-zinc-800">
                  <TableCell colspan="4" class="h-32 text-center text-slate-400 dark:text-zinc-500 text-sm">
                    No system hardware or stock level faults detected.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">
          <!-- Quick Operations Shortcuts -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-zinc-100">Quick Commands</h2>
            <div
              class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-2 dark:border-zinc-800 dark:bg-zinc-900">
              <Button
                class="w-full justify-start gap-2 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:bg-zinc-800"
                variant="outline" as-child>
                <Link href="/inventory">
                  <PlusCircle class="h-4 w-4 text-slate-500 dark:text-zinc-400" />
                  <span>Update Stock Levels</span>
                </Link>
              </Button>
              <Button
                class="w-full justify-start gap-2 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:bg-zinc-800"
                variant="outline" as-child>
                <Link href="/announcements">
                  <Megaphone class="h-4 w-4 text-slate-500 dark:text-zinc-400" />
                  <span>Modify Banner Notice</span>
                </Link>
              </Button>
            </div>
          </div>

          <!-- Top Allocated Stock -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-zinc-100">Top Allocated Stock</h2>
            <div
              class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden p-4 space-y-4 dark:border-zinc-800 dark:bg-zinc-900">
              <div v-for="item in props.topStockedItems" :key="item.id"
                class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 last:border-0 pb-3 last:pb-0">
                <div class="space-y-0.5 min-w-0 flex-1 pr-2">
                  <p class="text-sm font-medium text-slate-900 dark:text-zinc-100 truncate">
                    {{ item.product?.name || 'Unknown Product' }}
                  </p>
                  <p class="text-xs font-mono text-slate-400 dark:text-zinc-500 truncate">
                    {{ item.sku }} <span v-if="item.variant_label">({{ item.variant_label }})</span>
                  </p>
                </div>
                <Badge variant="outline"
                  class="bg-slate-50 text-slate-700 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 font-semibold font-mono flex-shrink-0">
                  {{ item.stock_qty }} units
                </Badge>
              </div>
              <div v-if="props.topStockedItems.length === 0"
                class="text-center py-4 text-slate-400 dark:text-zinc-500 text-sm">
                No stock tracks logged yet.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>