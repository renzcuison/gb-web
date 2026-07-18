<script setup>
import { inject } from 'vue'
import { Menu } from 'lucide-vue-next'

const sidebar = inject('sidebar')
</script>

<template>
  <button
    @click="sidebar.toggleSidebar"
    type="button"
    class="inline-flex h-9 w-9 items-center justify-center rounded-md border text-slate-500 hover:bg-slate-50 md:hidden"
  >
    <Menu class="h-5 w-5" />
  </button>
</template>