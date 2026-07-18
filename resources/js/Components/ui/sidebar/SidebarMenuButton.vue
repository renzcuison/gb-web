<script setup>
defineProps({
  isActive: Boolean
})
</script>

<template>
  <div
    class="w-full rounded-md transition-colors duration-200"
    :class="[isActive ? 'bg-slate-200 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900']"
  >
    <slot />
  </div>
</template>