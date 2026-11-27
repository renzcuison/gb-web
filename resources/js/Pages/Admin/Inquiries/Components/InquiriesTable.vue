<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ImsEyeIcon,
  ImsTrashIcon,
  ImsStatusSuccessIcon,
  ImsClockIcon,
  ImsInquiriesIcon,
  ImsArchiveIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  inquiries: { type: Object, required: true },
})

const emit = defineEmits(['review', 'resolve', 'delete'])

const formatDate = (iso) => {
  if (!iso) return '—'
  return new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(iso))
}

const statusBadge = (val) => {
  switch (val) {
    case 'new':
      return {
        label: 'New',
        classes: 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400',
        icon: ImsInquiriesIcon,
      }
    case 'contacted':
      return {
        label: 'Contacted',
        classes: 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400',
        icon: ImsClockIcon,
      }
    case 'resolved':
      return {
        label: 'Resolved',
        classes: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400',
        icon: ImsStatusSuccessIcon,
      }
    default:
      return {
        label: 'Archived',
        classes: 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400',
        icon: ImsArchiveIcon,
      }
  }
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-slate-200/80 dark:border-zinc-800 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            <th class="px-4 py-3">Customer</th>
            <th class="px-4 py-3">Product / Variant</th>
            <th class="px-4 py-3">Qty</th>
            <th class="px-4 py-3">Message</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Received</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
          <tr
            v-for="item in inquiries.data"
            :key="item.id"
            @click="emit('review', item)"
            class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors cursor-pointer">
            <td class="px-4 py-3">
              <div class="font-medium text-slate-900 dark:text-zinc-100">
                {{ item.customer_name }}
              </div>
              <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                {{ item.customer_email }}
                <span v-if="item.customer_phone"> · {{ item.customer_phone }}</span>
              </div>
            </td>

            <td class="px-4 py-3">
              <div class="font-medium text-slate-900 dark:text-zinc-100">
                {{ item.product?.name ?? 'Deleted Product' }}
              </div>
              <div class="text-[11px] text-slate-500 dark:text-zinc-400 font-mono">
                {{ item.variant?.variant_label ?? 'Standard' }}
                <span v-if="item.variant?.sku"> · {{ item.variant.sku }}</span>
              </div>
            </td>

            <td class="px-4 py-3 font-mono font-semibold text-slate-900 dark:text-zinc-100">
              {{ item.quantity }}
            </td>

            <td class="px-4 py-3 max-w-xs">
              <p class="truncate text-slate-600 dark:text-zinc-300">
                {{ item.message }}
              </p>
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium',
                  statusBadge(item.status).classes,
                ]">
                <component :is="statusBadge(item.status).icon" class="h-3 w-3" />
                {{ statusBadge(item.status).label }}
              </span>
            </td>

            <td class="px-4 py-3 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
              {{ formatDate(item.created_at) }}
            </td>

            <td class="px-4 py-3 text-right whitespace-nowrap" @click.stop>
              <div class="inline-flex items-center justify-end gap-1.5">
                <button
                  type="button"
                  @click="emit('review', item)"
                  class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                  <ImsEyeIcon class="h-3 w-3" />
                  Review
                </button>

                <button
                  v-if="item.status !== 'resolved'"
                  type="button"
                  @click="emit('resolve', item)"
                  title="Mark as resolved"
                  class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2 py-1 text-[11px] font-medium text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                  <ImsStatusSuccessIcon class="h-3 w-3 text-emerald-600 dark:text-emerald-400" />
                </button>

                <button
                  type="button"
                  @click="emit('delete', item)"
                  title="Delete inquiry"
                  class="inline-flex items-center rounded-md border border-slate-200 dark:border-zinc-700 p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                  <ImsTrashIcon class="h-3.5 w-3.5" />
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="inquiries.data.length === 0">
            <td colspan="7" class="py-12 text-center text-xs text-slate-400 dark:text-zinc-500">
              No customer inquiries match the current filter.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="inquiries.links && inquiries.links.length > 3"
      class="flex items-center justify-between border-t border-slate-200/80 dark:border-zinc-800 px-4 py-3 text-xs text-slate-500 dark:text-zinc-400">
      <div>
        Showing <span class="font-mono">{{ inquiries.from ?? 0 }}</span>–<span class="font-mono">{{ inquiries.to ?? 0 }}</span> of
        <span class="font-mono">{{ inquiries.total }}</span>
      </div>
      <div class="flex items-center gap-1">
        <template v-for="(link, idx) in inquiries.links" :key="idx">
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