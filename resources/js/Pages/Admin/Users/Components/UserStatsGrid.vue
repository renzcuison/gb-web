<script setup>
defineProps({
  stats: { type: Object, required: true },
  role: { type: String, required: true },
})

const emit = defineEmits(['select-role'])
</script>

<template>
  <div
    class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
    <button
      type="button"
      @click="emit('select-role', 'all')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        role === 'all'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Total Users</div>
      <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
        {{ stats.total }}
      </div>
    </button>

    <button
      type="button"
      @click="emit('select-role', 'admin')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        role === 'admin'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Administrators</div>
      <div class="mt-1 font-mono text-lg font-semibold text-emerald-600 dark:text-emerald-400">
        {{ stats.admins }}
      </div>
    </button>

    <button
      type="button"
      @click="emit('select-role', 'user')"
      :class="[
        'px-4 py-3.5 text-left transition-colors',
        role === 'user'
          ? 'bg-slate-50 dark:bg-zinc-800/60'
          : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
      ]">
      <div class="text-xs text-slate-500 dark:text-zinc-400">Members</div>
      <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
        {{ stats.members }}
      </div>
    </button>
  </div>
</template>