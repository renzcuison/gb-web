<script setup>
defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, required: true },
  description: { type: String, required: true },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex items-center justify-between gap-4">
    <div>
      <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
        {{ title }}
      </div>
      <div class="text-[11px] text-slate-500 dark:text-zinc-400">
        {{ description }}
      </div>
    </div>

    <button
      type="button"
      @click="emit('update:modelValue', !modelValue)"
      class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
      :class="modelValue ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
      <span
        class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
        :class="modelValue ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
    </button>
  </div>
</template>