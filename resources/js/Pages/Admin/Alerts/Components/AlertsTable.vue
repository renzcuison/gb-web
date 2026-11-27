<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ImsStatusSuccessIcon,
  ImsPackagePlusIcon,
  ImsCheckIcon,
  ImsStatusWarningIcon,
  ImsStatusDangerIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  alerts: { type: Object, required: true },
})

const emit = defineEmits(['restock', 'resolve'])

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
                <ImsStatusSuccessIcon class="h-3 w-3" />
                Resolved
              </span>
              <span
                v-else-if="(alert.variant?.stock_qty ?? 0) <= 0"
                class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 text-[11px] font-medium text-rose-700 dark:text-rose-400">
                <ImsStatusDangerIcon class="h-3 w-3" />
                Out of Stock
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                <ImsStatusWarningIcon class="h-3 w-3" />
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
                  @click="emit('restock', alert.variant)"
                  class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                  <ImsPackagePlusIcon class="h-3 w-3" />
                  Restock
                </button>

                <button
                  v-if="!alert.is_resolved"
                  type="button"
                  @click="emit('resolve', alert)"
                  title="Dismiss alert"
                  class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2 py-1 text-[11px] font-medium text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                  <ImsCheckIcon class="h-3 w-3" />
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
</template>