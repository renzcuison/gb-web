<script setup>
import { ImsPackageCheckIcon } from '@/Components/Icons/ImsIcons.vue'
import SettingToggleRow from './SettingToggleRow.vue'

defineProps({
  form: { type: Object, required: true },
  variantsCount: { type: Number, default: 0 },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          Stock Control &amp; Valuation
        </h2>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Control reorder thresholds, stock floor limits, and inventory movement accountability.
        </p>
      </div>
      <ImsPackageCheckIcon class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
          Default Low-Stock Threshold (units)
        </label>
        <input
          v-model.number="form.default_low_stock_threshold"
          type="number"
          min="0"
          max="10000"
          required
          class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
          Applied automatically when creating new product variants.
        </p>
        <p v-if="form.errors.default_low_stock_threshold" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
          {{ form.errors.default_low_stock_threshold }}
        </p>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
          Valuation Currency Symbol
        </label>
        <input
          v-model="form.currency_symbol"
          type="text"
          maxlength="5"
          required
          class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
          Used across dashboard stock valuation, product pricing, and exports.
        </p>
        <p v-if="form.errors.currency_symbol" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
          {{ form.errors.currency_symbol }}
        </p>
      </div>
    </div>

    <label class="flex items-center gap-2.5 text-xs text-slate-600 dark:text-zinc-300 cursor-pointer select-none">
      <input
        v-model="form.apply_threshold_to_all"
        type="checkbox"
        class="rounded border-slate-300 dark:border-zinc-700 text-slate-900 focus:ring-0" />
      <span>
        Overwrite low-stock threshold on all
        <span class="font-mono font-semibold">{{ variantsCount }}</span> existing variants
      </span>
    </label>

    <div class="space-y-3.5 pt-1">
      <SettingToggleRow
        v-model="form.allow_negative_stock"
        title="Allow Negative Stock Balances"
        description="Permit outbound stock deductions even when available quantity reaches 0." />

      <SettingToggleRow
        v-model="form.require_adjustment_reason"
        title="Require Reason on Manual Stock Adjustments"
        description="Enforce an audit note whenever staff log manual restocks, damage, or shrinkage." />
    </div>
  </div>
</template>