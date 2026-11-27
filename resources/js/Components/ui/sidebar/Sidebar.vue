<script setup>
import { inject, computed } from 'vue'

const props = defineProps({
  collapsible: {
    type: String,
    default: 'offcanvas',
  },
})

const sidebar = inject('sidebar')

const isCollapsed = computed(() => !sidebar.isMobile.value && !sidebar.open.value)
</script>

<template>
  <div
    v-if="sidebar.isMobile.value && sidebar.openMobile.value"
    @click="sidebar.setOpenMobile(false)"
    class="fixed inset-0 z-40 bg-black/40 md:hidden"
  />

  <aside
    :data-state="isCollapsed ? 'collapsed' : 'expanded'"
    :data-collapsible="isCollapsed ? collapsible : ''"
    class="group fixed inset-y-0 left-0 z-50 flex h-full shrink-0 flex-col border-r bg-white dark:bg-zinc-900 md:sticky md:top-0 overflow-hidden"
    :class="[
      sidebar.isMobile.value
        ? sidebar.openMobile.value
          ? 'w-64 translate-x-0'
          : 'w-64 -translate-x-full'
        : sidebar.open.value
          ? 'w-64'
          : collapsible === 'icon'
            ? 'w-14'
            : 'w-0 border-r-0',
    ]"
  >
    <slot />
  </aside>
</template>