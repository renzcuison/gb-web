<script setup>
defineProps({
  stats: { type: Object, required: true },
  status: { type: String, required: true },
  severity: { type: String, required: true },
})

const emit = defineEmits(['select-severity', 'select-status'])
</script>

<template>
  <div
    class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
    <button
      type="button"
      @click="emit('select-severity', 'all')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        status === 'unresolved' && severity === 'all'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Open Alerts</div>
      <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
        {{ stats.unresolved }}
      </div>
    </button>

    <button
      type="button"
      @click="emit('select-severity', 'out_of_stock')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        status === 'unresolved' && severity === 'out_of_stock'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Out of Stock</div>
      <div
        :class="[
          'mt-1 font-mono text-lg font-semibold',
          stats.out_of_stock > 0
            ? 'text-rose-600 dark:text-rose-400'
            : 'text-slate-900 dark:text-zinc-100',
        ]">
        {{ stats.out_of_stock }}
      </div>
    </button>

    <button
      type="button"
      @click="emit('select-severity', 'low_stock')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        status === 'unresolved' && severity === 'low_stock'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Low Stock</div>
      <div
        :class="[
          'mt-1 font-mono text-lg font-semibold',
          stats.low_stock > 0
            ? 'text-amber-600 dark:text-amber-400'
            : 'text-slate-900 dark:text-zinc-100',
        ]">
        {{ stats.low_stock }}
      </div>
    </button>

    <button
      type="button"
      @click="emit('select-status', 'resolved')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        status === 'resolved'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Resolved History</div>
      <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
        {{ stats.resolved }}
      </div>
    </button>
  </div>
</template>