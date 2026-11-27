<script setup>
import { Link } from '@inertiajs/vue3'
import { ImsChevronRightIcon } from '@/Components/Icons/ImsIcons.vue'

defineProps({
  inquiries: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Open Inquiries</h2>
      <Link
        :href="route('inquiries.index')"
        aria-label="View all inquiries"
        class="inline-flex items-center justify-center p-1 -mr-1 text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-100"
      >
        <ImsChevronRightIcon class="h-3.5 w-3.5" />
      </Link>
    </div>

    <div v-if="inquiries.length" class="space-y-1 -mx-2">
      <Link
        v-for="inq in inquiries"
        :key="inq.id"
        :href="route('inquiries.index', { status: inq.status })"
        class="px-2 py-2 rounded-md flex items-center justify-between gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
      >
        <div class="min-w-0">
          <div class="text-[13px] font-medium text-zinc-900 dark:text-zinc-100 truncate">
            {{ inq.customer_name }}
          </div>
          <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
            {{ inq.product?.name ?? 'Product' }}
            <span v-if="inq.variant?.variant_label && inq.variant.variant_label !== 'Standard'">
              · {{ inq.variant.variant_label }}
            </span>
            <span class="font-mono"> (×{{ inq.quantity }})</span>
          </div>
        </div>

        <span
          :class="[
            'shrink-0 text-xs font-medium',
            inq.status === 'new'
              ? 'text-blue-600 dark:text-blue-400'
              : 'text-amber-600 dark:text-amber-400',
          ]"
        >
          {{ inq.status === 'new' ? 'New' : 'Contacted' }}
        </span>
      </Link>
    </div>

    <div v-else class="py-6 text-center text-xs text-zinc-400 dark:text-zinc-500">
      No open inquiries.
    </div>
  </div>
</template>