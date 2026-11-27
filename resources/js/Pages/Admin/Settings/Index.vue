<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StockControlSettingsCard from './Components/StockControlSettingsCard.vue'
import AlertAutomationSettingsCard from './Components/AlertAutomationSettingsCard.vue'
import StorefrontSettingsCard from './Components/StorefrontSettingsCard.vue'
import FormattingSettingsCard from './Components/FormattingSettingsCard.vue'

const props = defineProps({
  settings: { type: Object, required: true },
  variantsCount: { type: Number, default: 0 },
})

const page = usePage()

const form = useForm({
  default_low_stock_threshold: props.settings.default_low_stock_threshold ?? 5,
  allow_negative_stock: Boolean(props.settings.allow_negative_stock),
  require_adjustment_reason: Boolean(props.settings.require_adjustment_reason),
  currency_symbol: props.settings.currency_symbol ?? '₱',

  auto_resolve_alerts_on_restock: Boolean(props.settings.auto_resolve_alerts_on_restock),
  alert_on_zero_stock: Boolean(props.settings.alert_on_zero_stock),
  alert_on_low_stock: Boolean(props.settings.alert_on_low_stock),

  storefront_inquiries_enabled: Boolean(props.settings.storefront_inquiries_enabled),
  auto_deduct_stock_on_fulfill: Boolean(props.settings.auto_deduct_stock_on_fulfill),
  hide_out_of_stock_storefront: Boolean(props.settings.hide_out_of_stock_storefront),
  inquiry_recipient_email: props.settings.inquiry_recipient_email ?? '',

  sku_prefix: props.settings.sku_prefix ?? 'SKU-',
  export_date_format: props.settings.export_date_format ?? 'Y-m-d',
  apply_threshold_to_all: false,
})

const submit = () => {
  form.patch(route('settings.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.apply_threshold_to_all = false
    },
  })
}
</script>

<template>
  <Head title="System Preferences" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
            System Preferences
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Configure stock rules, alert automation, inquiry fulfillment, and export defaults.
          </p>
        </div>
      </div>

      <div
        v-if="page.props.flash?.success"
        class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
        {{ page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <StockControlSettingsCard :form="form" :variants-count="variantsCount" />
        <AlertAutomationSettingsCard :form="form" />
        <StorefrontSettingsCard :form="form" />
        <FormattingSettingsCard :form="form" />
      </form>
    </div>
  </AdminLayout>
</template>