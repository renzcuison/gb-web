<script setup>
defineProps({
  stats: { type: Object, required: true },
  status: { type: String, required: true },
})

const emit = defineEmits(['select-status'])
</script>

<template>
  <div
    class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
    <button
      type="button"
      @click="emit('select-status', 'new')"
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
      @click="emit('select-status', 'contacted')"
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
      @click="emit('select-status', 'resolved')"
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
      @click="emit('select-status', 'all')"
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
</template>