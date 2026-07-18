<script setup>
import { inject } from 'vue'

const sidebar = inject('sidebar')
</script>

<template>
  <aside
    class="fixed inset-y-0 left-0 z-50 flex h-full w-64 flex-col border-r bg-card transition-transform duration-300 md:sticky md:top-0"
    :class="[
      sidebar.open.value ? 'translate-x-0' : '-translate-x-full md:translate-x-0 md:w-0 md:overflow-hidden'
    ]"
  >
    <slot />
  </aside>
</template>