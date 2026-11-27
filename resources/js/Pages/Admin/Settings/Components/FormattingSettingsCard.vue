<script setup>
import { ImsSpreadsheetIcon } from '@/Components/Icons/ImsIcons.vue'

defineProps({
  form: { type: Object, required: true },
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          SKU &amp; Export Formatting
        </h2>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Standardize auto-generated SKU codes and CSV/Excel audit log exports.
        </p>
      </div>
      <ImsSpreadsheetIcon class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
          Default SKU Prefix
        </label>
        <input
          v-model="form.sku_prefix"
          type="text"
          maxlength="20"
          placeholder="SKU-"
          class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
          Prepended when auto-generating variant SKUs in the product builder.
        </p>
        <p v-if="form.errors.sku_prefix" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
          {{ form.errors.sku_prefix }}
        </p>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
          Export Date Format
        </label>
        <select
          v-model="form.export_date_format"
          class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none">
          <option value="Y-m-d">YYYY-MM-DD (2026-11-26)</option>
          <option value="m/d/Y">MM/DD/YYYY (11/26/2026)</option>
          <option value="d/m/Y">DD/MM/YYYY (26/11/2026)</option>
        </select>
        <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
          Applied to timestamps in Inventory and Movement Log exports.
        </p>
      </div>
    </div>

    <div class="pt-2 flex items-center justify-end gap-3">
      <span v-if="form.recentlySuccessful" class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
        Saved.
      </span>
      <button
        type="submit"
        :disabled="form.processing"
        class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-2 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50">
        {{ form.processing ? 'Saving...' : 'Save Preferences' }}
      </button>
    </div>
  </div>
</template>