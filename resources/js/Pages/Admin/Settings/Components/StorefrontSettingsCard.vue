<script setup>
import { ImsStoreIcon } from '@/Components/Icons/ImsIcons.vue'
import SettingToggleRow from './SettingToggleRow.vue'

defineProps({
  form: { type: Object, required: true },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          Storefront &amp; Inquiry Fulfillment
        </h2>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Manage storefront stock visibility and how customer inquiries affect inventory.
        </p>
      </div>
      <ImsStoreIcon class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
    </div>

    <div>
      <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
        Inquiry Notification Email
      </label>
      <input
        v-model="form.inquiry_recipient_email"
        type="email"
        required
        class="w-full sm:max-w-md rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
      <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
        Receives email notifications whenever a customer submits a product inquiry.
      </p>
      <p v-if="form.errors.inquiry_recipient_email" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
        {{ form.errors.inquiry_recipient_email }}
      </p>
    </div>

    <div class="space-y-3.5">
      <SettingToggleRow
        v-model="form.auto_deduct_stock_on_fulfill"
        title="Auto-Deduct Stock When Fulfilling Inquiries"
        description="Automatically subtract requested quantity and log a movement when an inquiry is fulfilled." />

      <SettingToggleRow
        v-model="form.storefront_inquiries_enabled"
        title="Accept Storefront Product Inquiries"
        description="Allow customers to submit reservation or stock inquiries from product pages." />

      <SettingToggleRow
        v-model="form.hide_out_of_stock_storefront"
        title="Hide Out-of-Stock Products on Storefront"
        description="Automatically conceal items from the public catalog when all variants reach 0 stock." />
    </div>
  </div>
</template>