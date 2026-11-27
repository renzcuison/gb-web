<script setup>
import { ref, watch } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import {
  ImsMailIcon,
  ImsPhoneIcon,
  ImsCloseIcon,
  ImsExternalLinkIcon,
  ImsPackageCheckIcon,
} from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  inquiry: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const fulfilling = ref(false)
const fulfillError = ref('')

const manageForm = useForm({
  status: 'new',
  admin_notes: '',
})

watch(
  () => props.inquiry,
  (val) => {
    if (!val) return
    manageForm.status = val.status
    manageForm.admin_notes = val.admin_notes || ''
    manageForm.clearErrors()
    fulfillError.value = ''
  },
  { immediate: true }
)

const fulfillAndDeduct = () => {
  if (!props.inquiry || fulfilling.value) return
  fulfillError.value = ''
  fulfilling.value = true

  router.post(
    route('inquiries.fulfill', props.inquiry.id),
    { admin_notes: manageForm.admin_notes },
    {
      preserveScroll: true,
      onSuccess: () => {
        fulfilling.value = false
        emit('close')
      },
      onError: (errors) => {
        fulfilling.value = false
        fulfillError.value = errors.fulfill || 'Unable to fulfill inquiry.'
      },
    }
  )
}

const saveInquiryChanges = () => {
  if (!props.inquiry) return

  manageForm.patch(route('inquiries.update', props.inquiry.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
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
</script>

<template>
  <Teleport to="body">
    <div
      v-if="inquiry"
      @click="emit('close')"
      class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-40"></div>

    <aside
      v-if="inquiry"
      class="fixed top-14 bottom-0 right-0 z-50 w-full max-w-md bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 shadow-2xl flex flex-col">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
            Inquiry #{{ inquiry.id }}
          </h2>
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Received {{ formatDate(inquiry.created_at) }}
          </p>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
          <ImsCloseIcon class="h-4 w-4" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs">
        <div class="rounded-lg border border-slate-200/80 dark:border-zinc-800 p-3.5 space-y-2">
          <div class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Customer Details
          </div>
          <div class="font-semibold text-sm text-slate-900 dark:text-zinc-100">
            {{ inquiry.customer_name }}
          </div>
          <div class="flex flex-col gap-1 text-slate-600 dark:text-zinc-300">
            <a
              :href="`mailto:${inquiry.customer_email}?subject=Re: ${encodeURIComponent(inquiry.product?.name || 'GreatBuy Originals Inquiry')}`"
              class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400 hover:underline w-fit">
              <ImsMailIcon class="h-3.5 w-3.5" />
              {{ inquiry.customer_email }}
            </a>
            <span v-if="inquiry.customer_phone" class="inline-flex items-center gap-1.5">
              <ImsPhoneIcon class="h-3.5 w-3.5 text-slate-400" />
              {{ inquiry.customer_phone }}
            </span>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200/80 dark:border-zinc-800 p-3.5 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
              Requested Item
            </span>
            <Link
              v-if="inquiry.product?.slug"
              :href="route('products.show', inquiry.product.slug)"
              class="inline-flex items-center gap-1 text-[11px] text-slate-500 hover:text-slate-900 dark:hover:text-zinc-100">
              <span>View Product</span>
              <ImsExternalLinkIcon class="h-3 w-3" />
            </Link>
          </div>

          <div class="font-medium text-slate-900 dark:text-zinc-100">
            {{ inquiry.product?.name ?? 'Deleted Product' }}
          </div>

          <div class="grid grid-cols-3 gap-2 pt-1 text-[11px]">
            <div>
              <span class="text-slate-400 block">Option</span>
              <span class="font-medium text-slate-700 dark:text-zinc-300">
                {{ inquiry.variant?.variant_label ?? 'Standard' }}
              </span>
            </div>
            <div>
              <span class="text-slate-400 block">Requested Qty</span>
              <span class="font-mono font-semibold text-slate-900 dark:text-zinc-100">
                {{ inquiry.quantity }}
              </span>
            </div>
            <div>
              <span class="text-slate-400 block">Live Stock</span>
              <span
                :class="[
                  'font-mono font-semibold',
                  (inquiry.variant?.stock_qty ?? 0) >= inquiry.quantity
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-rose-600 dark:text-rose-400',
                ]">
                {{ inquiry.variant?.stock_qty ?? 'N/A' }}
              </span>
            </div>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="block text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Customer Message
          </label>
          <div class="rounded-lg bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-800 p-3.5 text-slate-700 dark:text-zinc-200 whitespace-pre-line leading-relaxed">
            {{ inquiry.message }}
          </div>
        </div>

        <div
          v-if="inquiry.status !== 'resolved' && inquiry.variant"
          class="rounded-lg border border-slate-200/80 dark:border-zinc-800 bg-slate-50/70 dark:bg-zinc-800/40 p-3.5 space-y-2.5">
          <div class="flex items-center justify-between gap-2">
            <div>
              <div class="text-xs font-semibold text-slate-900 dark:text-zinc-100">
                Fulfill &amp; Deduct Stock
              </div>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                Deducts <span class="font-mono font-semibold">{{ inquiry.quantity }}</span> unit(s) from
                <span class="font-mono">{{ inquiry.variant.sku }}</span>, logs a movement, and marks resolved.
              </p>
            </div>
          </div>

          <p v-if="fulfillError" class="text-[11px] text-rose-600 dark:text-rose-400">
            {{ fulfillError }}
          </p>

          <button
            type="button"
            @click="fulfillAndDeduct"
            :disabled="fulfilling || (inquiry.variant.stock_qty ?? 0) < inquiry.quantity"
            class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 dark:disabled:bg-zinc-800 text-white disabled:text-slate-500 px-3 py-2 text-xs font-medium transition-colors">
            <ImsPackageCheckIcon class="h-3.5 w-3.5" />
            <span v-if="(inquiry.variant.stock_qty ?? 0) < inquiry.quantity">
              Insufficient Stock ({{ inquiry.variant.stock_qty ?? 0 }} available)
            </span>
            <span v-else>
              {{ fulfilling ? 'Fulfilling...' : `Fulfill Order & Deduct ${inquiry.quantity} Unit(s)` }}
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

          <div v-if="inquiry.resolved_at" class="text-[11px] text-slate-400">
            Resolved {{ formatDate(inquiry.resolved_at) }}
            <span v-if="inquiry.resolved_by"> by {{ inquiry.resolved_by.name }}</span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              type="button"
              @click="emit('close')"
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
</template>