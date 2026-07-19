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
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Control Center</h1>
        <p class="text-sm text-slate-500 mt-1">
          Real-time overview of your operational inventory metrics, system conditions, and retail configurations.
        </p>
      </div>

      <!-- Quick Stats Grid -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Items -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500">Total Variants</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ props.stats.totalItems }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
            <Package class="h-5 w-5" />
          </div>
        </div>

        <!-- Low Stock Items -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500">Low Stock Warnings</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ props.stats.lowStockCount }}</h3>
          </div>
          <div class="p-3 rounded-lg" :class="props.stats.lowStockCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400'">
            <AlertTriangle class="h-5 w-5" />
          </div>
        </div>

        <!-- Valuation -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500">Inventory Value</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ formattedTotalValue }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
            <TrendingUp class="h-5 w-5" />
          </div>
        </div>

        <!-- Announcements Banner State -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500">Active Notices</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ props.stats.activeAnnouncements }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
            <Megaphone class="h-5 w-5" />
          </div>
        </div>
      </div>

      <!-- Main Operational Dashboard Layout Split -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- Left: Urgent System Alerts (Takes 2 columns) -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <ShieldAlert class="h-5 w-5 text-rose-500" />
              <h2 class="text-lg font-semibold text-slate-900">Urgent System Alerts</h2>
            </div>
            <Button variant="ghost" size="sm" as-child class="text-slate-600 hover:text-slate-900">
              <Link href="/inventory/alerts" class="flex items-center gap-1">
                <span>View all</span>
                <ArrowRight class="h-4 w-4" />
              </Link>
            </Button>
          </div>

          <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Status</TableHead>
                  <TableHead>Item / SKU</TableHead>
                  <TableHead>Details</TableHead>
                  <TableHead class="text-right">Stock State</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="alert in props.recentAlerts" :key="alert.id">
                  <TableCell>
                    <Badge :variant="alert.is_resolved ? 'secondary' : 'destructive'">
                      {{ alert.is_resolved ? 'Resolved' : 'Active Warning' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="font-medium text-slate-900">
                      {{ alert.product_variant?.product?.name || 'Deleted Product' }}
                    </div>
                    <div class="text-xs font-mono text-slate-500">
                      {{ alert.product_variant?.sku }} <span v-if="alert.product_variant?.variant_label">({{ alert.product_variant.variant_label }})</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-slate-500 text-sm">
                    Stock dropped to or below threshold of {{ alert.threshold_reached }} units.
                  </TableCell>
                  <TableCell class="text-right font-mono font-bold text-rose-600">
                    {{ alert.product_variant?.stock_qty ?? 0 }} Left
                  </TableCell>
                </TableRow>
                <TableRow v-if="props.recentAlerts.length === 0">
                  <TableCell colspan="4" class="h-32 text-center text-slate-400 text-sm">
                    No system hardware or stock level faults detected.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>

        <!-- Right: Actionable Panels (Takes 1 column) -->
        <div class="space-y-6">
          <!-- Quick Operations Shortcuts -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Quick Commands</h2>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-2">
              <Button class="w-full justify-start gap-2" variant="outline" as-child>
                <Link href="/inventory">
                  <PlusCircle class="h-4 w-4 text-slate-500" />
                  <span>Update Stock Levels</span>
                </Link>
              </Button>
              <Button class="w-full justify-start gap-2" variant="outline" as-child>
                <Link href="/announcements">
                  <Megaphone class="h-4 w-4 text-slate-500" />
                  <span>Modify Banner Notice</span>
                </Link>
              </Button>
            </div>
          </div>

          <!-- High Quantity Assets Overview -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Top Allocated Stock</h2>
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden p-4 space-y-4">
              <div v-for="item in props.topStockedItems" :key="item.id" class="flex items-center justify-between border-b border-slate-100 last:border-0 pb-3 last:pb-0">
                <div class="space-y-0.5 min-w-0 flex-1 pr-2">
                  <p class="text-sm font-medium text-slate-900 truncate">
                    {{ item.product?.name || 'Unknown Product' }}
                  </p>
                  <p class="text-xs font-mono text-slate-400 truncate">
                    {{ item.sku }} <span v-if="item.variant_label">({{ item.variant_label }})</span>
                  </p>
                </div>
                <Badge variant="outline" class="bg-slate-50 text-slate-700 font-semibold font-mono flex-shrink-0">
                  {{ item.stock_qty }} units
                </Badge>
              </div>
              <div v-if="props.topStockedItems.length === 0" class="text-center py-4 text-slate-400 text-sm">
                No stock tracks logged yet.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>