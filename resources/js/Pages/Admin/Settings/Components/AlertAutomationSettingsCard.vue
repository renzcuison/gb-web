<script setup>
import { ImsBellRingIcon } from '@/Components/Icons/ImsIcons.vue'
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
          Alert Automation
        </h2>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Choose which inventory events generate alerts and when they clear.
        </p>
      </div>
      <ImsBellRingIcon class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
    </div>

    <div class="space-y-3.5">
      <SettingToggleRow
        v-model="form.alert_on_low_stock"
        title="Trigger Low-Stock Alerts"
        description="Create an alert when a variant drops at or below its low-stock threshold." />

      <SettingToggleRow
        v-model="form.alert_on_zero_stock"
        title="Trigger Out-of-Stock Alerts"
        description="Flag high-priority alerts immediately when any variant hits 0 units." />

      <SettingToggleRow
        v-model="form.auto_resolve_alerts_on_restock"
        title="Auto-Resolve Alerts on Restock"
        description="Automatically resolve open alerts once stock is replenished above threshold." />
    </div>
  </div>
</template>