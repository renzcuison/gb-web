<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ImsChevronRightIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  fallbackUnits: {
    type: Number,
    default: 0,
  },
})

const page = usePage()
const currencySymbol = computed(() => page.props.system_settings?.currency_symbol || '₱')

const formatCurrency = (val) => {
  const formatted = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(val || 0))
  return `${currencySymbol.value}${formatted}`
}

const totalUnitsOnHand = computed(() =>
  typeof props.stats.totalUnits === 'number' && props.stats.totalUnits > 0
    ? props.stats.totalUnits
    : props.fallbackUnits
)

const cards = computed(() => [
  {
    label: 'Out of Stock',
    value: props.stats.outOfStockCount ?? 0,
    href: route('inventory.index', { status: 'out_of_stock' }),
    subtext: null,
  },
  {
    label: 'Low Stock',
    value: props.stats.lowStockCount ?? 0,
    href: route('inventory.index', { status: 'low_stock' }),
    subtext: null,
  },
  {
    label: 'New Inquiries',
    value: props.stats.newInquiriesCount ?? 0,
    href: route('inquiries.index', { status: 'new' }),
    subtext: null,
  },
  {
    label: 'Active SKUs',
    value: props.stats.totalItems ?? 0,
    href: route('inventory.index'),
    subtext: `${props.stats.outOfStockCount ?? 0} depleted · ${props.stats.archivedItems ?? 0} archived`,
  },
  {
    label: 'Valuation',
    value: formatCurrency(props.stats.totalValue),
    href: route('inventory.movements.index'),
    subtext:
      totalUnitsOnHand.value > 0
        ? `${totalUnitsOnHand.value.toLocaleString()} units on hand`
        : 'Total catalog stock value',
  },
])
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <Link
      v-for="card in cards"
      :key="card.label"
      :href="card.href"
      class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 rounded-md p-4 flex flex-col justify-between gap-2"
    >
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
          {{ card.label }}
        </span>
        <ImsChevronRightIcon
          class="h-3.5 w-3.5 text-zinc-400 group-hover:text-zinc-900 dark:text-zinc-500 dark:group-hover:text-zinc-100 shrink-0"
        />
      </div>

      <div v-if="card.subtext" class="space-y-1">
        <div class="font-mono text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 truncate">
          {{ card.value }}
        </div>
        <div class="text-xs text-zinc-400 dark:text-zinc-500 truncate">
          {{ card.subtext }}
        </div>
      </div>

      <div v-else class="font-mono text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
        {{ card.value }}
      </div>
    </Link>
  </div>
</template>