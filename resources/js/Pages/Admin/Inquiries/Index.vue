<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import InquiryStatsGrid from './Components/InquiryStatsGrid.vue'
import InquiriesTable from './Components/InquiriesTable.vue'
import InquiryDrawer from './Components/InquiryDrawer.vue'
import { ImsSearchIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  inquiries: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({
      new: 0,
      contacted: 0,
      resolved: 0,
      total: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      status: 'new',
    }),
  },
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'new')
const activeInquiry = ref(null)

let searchTimeout = null
const applyFilters = () => {
  router.get(
    route('inquiries.index'),
    {
      search: search.value || undefined,
      status: status.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 300)
})

const setStatus = (val) => {
  status.value = val
  applyFilters()
}

const openInquiryDrawer = (inquiry) => {
  activeInquiry.value = inquiry
}

const closeDrawer = () => {
  activeInquiry.value = null
}

const quickResolve = (inquiry) => {
  router.patch(
    route('inquiries.update', inquiry.id),
    {
      status: 'resolved',
      admin_notes: inquiry.admin_notes || '',
    },
    { preserveScroll: true }
  )
}

const deleteInquiry = (inquiry) => {
  if (!confirm(`Delete inquiry from ${inquiry.customer_name}?`)) return

  router.delete(route('inquiries.destroy', inquiry.id), {
    preserveScroll: true,
    onSuccess: () => {
      if (activeInquiry.value?.id === inquiry.id) {
        closeDrawer()
      }
    },
  })
}
</script>

<template>
  <Head title="Customer Inquiries" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
            Customer Inquiries
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Review storefront product requests, stock availability questions, and client follow-ups.
          </p>
        </div>
      </div>

      <InquiryStatsGrid
        :stats="stats"
        :status="status"
        @select-status="setStatus" />

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 text-xs">
          <button
            v-for="tab in [
              { key: 'new', label: 'New' },
              { key: 'contacted', label: 'Contacted' },
              { key: 'resolved', label: 'Resolved' },
              { key: 'archived', label: 'Archived' },
              { key: 'all', label: 'All' },
            ]"
            :key="tab.key"
            type="button"
            @click="setStatus(tab.key)"
            :class="[
              'rounded-md px-3 py-1 font-medium transition-colors',
              status === tab.key
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                : 'text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100',
            ]">
            {{ tab.label }}
          </button>
        </div>

        <div class="relative w-full sm:w-72">
          <ImsSearchIcon class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Search customer, email, SKU, product..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

      <InquiriesTable
        :inquiries="inquiries"
        @review="openInquiryDrawer"
        @resolve="quickResolve"
        @delete="deleteInquiry" />
    </div>

    <InquiryDrawer
      :inquiry="activeInquiry"
      @close="closeDrawer" />
  </AdminLayout>
</template>