<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
Search,
  Mail,
  Phone,
  Eye,
  Trash2,
  X,
  CheckCircle2,
  Clock,
  MessageSquare,
  Archive,
  ExternalLink,
  PackageCheck
} from 'lucide-vue-next'

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
const fulfilling = ref(false)
const fulfillError = ref('')

const manageForm = useForm({
  status: 'new',
  admin_notes: '',
})

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
  manageForm.status = inquiry.status
  manageForm.admin_notes = inquiry.admin_notes || ''
  manageForm.clearErrors()
  fulfillError.value = ''
}

const fulfillAndDeduct = () => {
  if (!activeInquiry.value || fulfilling.value) return
  fulfillError.value = ''
  fulfilling.value = true

  router.post(
    route('inquiries.fulfill', activeInquiry.value.id),
    { admin_notes: manageForm.admin_notes },
    {
      preserveScroll: true,
      onSuccess: () => {
        fulfilling.value = false
        closeDrawer()
      },
      onError: (errors) => {
        fulfilling.value = false
        fulfillError.value = errors.fulfill || 'Unable to fulfill inquiry.'
      },
    }
  )
}

const closeDrawer = () => {
  activeInquiry.value = null
}

const saveInquiryChanges = () => {
  if (!activeInquiry.value) return

  manageForm.patch(route('inquiries.update', activeInquiry.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeDrawer()
    },
  })
}

const quickStatusUpdate = (inquiry, nextStatus) => {
  router.patch(
    route('inquiries.update', inquiry.id),
    {
      status: nextStatus,
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
        icon: MessageSquare,
      }
    case 'contacted':
      return {
        label: 'Contacted',
        classes: 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400',
        icon: Clock,
      }
    case 'resolved':
      return {
        label: 'Resolved',
        classes: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400',
        icon: CheckCircle2,
      }
    default:
      return {
        label: 'Archived',
        classes: 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400',
        icon: Archive,
      }
  }
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

      <div
        class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
        <button
          type="button"
          @click="setStatus('new')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'new'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">New Inquiries</div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              stats.new > 0
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.new }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('contacted')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'contacted'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">In Progress</div>
          <div
            :class="[
              'mt-1 font-mono text-lg font-semibold',
              stats.contacted > 0
                ? 'text-amber-600 dark:text-amber-400'
                : 'text-slate-900 dark:text-zinc-100',
            ]">
            {{ stats.contacted }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('resolved')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'resolved'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Resolved</div>
          <div class="mt-1 font-mono text-lg font-semibold text-emerald-600 dark:text-emerald-400">
            {{ stats.resolved }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('all')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'all'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Total Logged</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.total }}
          </div>
        </button>
      </div>

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
          <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Search customer, email, SKU, product..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

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
                @click="openInquiryDrawer(item)"
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
                      @click="openInquiryDrawer(item)"
                      class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                      <Eye class="h-3 w-3" />
                      Review
                    </button>

                    <button
                      v-if="item.status !== 'resolved'"
                      type="button"
                      @click="quickStatusUpdate(item, 'resolved')"
                      title="Mark as resolved"
                      class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2 py-1 text-[11px] font-medium text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                      <CheckCircle2 class="h-3 w-3 text-emerald-600 dark:text-emerald-400" />
                    </button>

                    <button
                      type="button"
                      @click="deleteInquiry(item)"
                      title="Delete inquiry"
                      class="inline-flex items-center rounded-md border border-slate-200 dark:border-zinc-700 p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                      <Trash2 class="h-3.5 w-3.5" />
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
    </div>

    <Teleport to="body">
      <div
        v-if="activeInquiry"
        @click="closeDrawer"
        class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-40"></div>

      <aside
        v-if="activeInquiry"
        class="fixed top-14 bottom-0 right-0 z-50 w-full max-w-md bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 shadow-2xl flex flex-col">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
          <div>
            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
              Inquiry #{{ activeInquiry.id }}
            </h2>
            <p class="text-[11px] text-slate-500 dark:text-zinc-400">
              Received {{ formatDate(activeInquiry.created_at) }}
            </p>
          </div>

          <button
            type="button"
            @click="closeDrawer"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
            <X class="h-4 w-4" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs">
          <div class="rounded-lg border border-slate-200/80 dark:border-zinc-800 p-3.5 space-y-2">
            <div class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
              Customer Details
            </div>
            <div class="font-semibold text-sm text-slate-900 dark:text-zinc-100">
              {{ activeInquiry.customer_name }}
            </div>
            <div class="flex flex-col gap-1 text-slate-600 dark:text-zinc-300">
              <a
                :href="`mailto:${activeInquiry.customer_email}?subject=Re: ${encodeURIComponent(activeInquiry.product?.name || 'GreatBuy Originals Inquiry')}`"
                class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400 hover:underline w-fit">
                <Mail class="h-3.5 w-3.5" />
                {{ activeInquiry.customer_email }}
              </a>
              <span v-if="activeInquiry.customer_phone" class="inline-flex items-center gap-1.5">
                <Phone class="h-3.5 w-3.5 text-slate-400" />
                {{ activeInquiry.customer_phone }}
              </span>
            </div>
          </div>

          <div class="rounded-lg border border-slate-200/80 dark:border-zinc-800 p-3.5 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Requested Item
              </span>
              <Link
                v-if="activeInquiry.product?.slug"
                :href="route('products.show', activeInquiry.product.slug)"
                class="inline-flex items-center gap-1 text-[11px] text-slate-500 hover:text-slate-900 dark:hover:text-zinc-100">
                <span>View Product</span>
                <ExternalLink class="h-3 w-3" />
              </Link>
            </div>

            <div class="font-medium text-slate-900 dark:text-zinc-100">
              {{ activeInquiry.product?.name ?? 'Deleted Product' }}
            </div>

            <div class="grid grid-cols-3 gap-2 pt-1 text-[11px]">
              <div>
                <span class="text-slate-400 block">Option</span>
                <span class="font-medium text-slate-700 dark:text-zinc-300">
                  {{ activeInquiry.variant?.variant_label ?? 'Standard' }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 block">Requested Qty</span>
                <span class="font-mono font-semibold text-slate-900 dark:text-zinc-100">
                  {{ activeInquiry.quantity }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 block">Live Stock</span>
                <span
                  :class="[
                    'font-mono font-semibold',
                    (activeInquiry.variant?.stock_qty ?? 0) >= activeInquiry.quantity
                      ? 'text-emerald-600 dark:text-emerald-400'
                      : 'text-rose-600 dark:text-rose-400',
                  ]">
                  {{ activeInquiry.variant?.stock_qty ?? 'N/A' }}
                </span>
              </div>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
              Customer Message
            </label>
            <div class="rounded-lg bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-800 p-3.5 text-slate-700 dark:text-zinc-200 whitespace-pre-line leading-relaxed">
              {{ activeInquiry.message }}
            </div>
          </div>

          <div
            v-if="activeInquiry.status !== 'resolved' && activeInquiry.variant"
            class="rounded-lg border border-slate-200/80 dark:border-zinc-800 bg-slate-50/70 dark:bg-zinc-800/40 p-3.5 space-y-2.5">
            <div class="flex items-center justify-between gap-2">
              <div>
                <div class="text-xs font-semibold text-slate-900 dark:text-zinc-100">
                  Fulfill &amp; Deduct Stock
                </div>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                  Deducts <span class="font-mono font-semibold">{{ activeInquiry.quantity }}</span> unit(s) from
                  <span class="font-mono">{{ activeInquiry.variant.sku }}</span>, logs a movement, and marks resolved.
                </p>
              </div>
            </div>

            <p v-if="fulfillError" class="text-[11px] text-rose-600 dark:text-rose-400">
              {{ fulfillError }}
            </p>

            <button
              type="button"
              @click="fulfillAndDeduct"
              :disabled="fulfilling || (activeInquiry.variant.stock_qty ?? 0) < activeInquiry.quantity"
              class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 dark:disabled:bg-zinc-800 text-white disabled:text-slate-500 px-3 py-2 text-xs font-medium transition-colors">
              <PackageCheck class="h-3.5 w-3.5" />
              <span v-if="(activeInquiry.variant.stock_qty ?? 0) < activeInquiry.quantity">
                Insufficient Stock ({{ activeInquiry.variant.stock_qty ?? 0 }} available)
              </span>
              <span v-else>
                {{ fulfilling ? 'Fulfilling...' : `Fulfill Order & Deduct ${activeInquiry.quantity} Unit(s)` }}
              </span>
            </button>
          </div>

          <form @submit.prevent="saveInquiryChanges" class="space-y-4 border-t border-slate-200/80 dark:border-zinc-800 pt-4">
            <div class="space-y-1.5">
              <label class="block text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Status
              </label>
              <select
                v-model="manageForm.status"
                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none">
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
                <option value="resolved">Resolved</option>
                <option value="archived">Archived</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Internal Notes
              </label>
              <textarea
                v-model="manageForm.admin_notes"
                rows="3"
                placeholder="Log follow-up details, quoted price, or reservation notes..."
                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none resize-none"
              ></textarea>
            </div>

            <div v-if="activeInquiry.resolved_at" class="text-[11px] text-slate-400">
              Resolved {{ formatDate(activeInquiry.resolved_at) }}
              <span v-if="activeInquiry.resolved_by"> by {{ activeInquiry.resolved_by.name }}</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                @click="closeDrawer"
                class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                Cancel
              </button>
              <button
                type="submit"
                :disabled="manageForm.processing"
                class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 disabled:opacity-50 transition-colors">
                {{ manageForm.processing ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </aside>
    </Teleport>
  </AdminLayout>
</template>