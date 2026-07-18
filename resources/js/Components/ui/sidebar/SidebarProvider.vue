<script setup>
import { ref, provide, computed } from 'vue'
import { useIsMobile } from '@/hooks/use-mobile'

const isMobile = useIsMobile()
const openMobile = ref(false)
const open = ref(true)

const toggleSidebar = () => {
  if (isMobile.value) {
    openMobile.value = !openMobile.value
  } else {
    open.value = !open.value
  }
}

provide('sidebar', {
  open: computed(() => open.value),
  openMobile: computed(() => openMobile.value),
  isMobile: computed(() => isMobile.value),
  toggleSidebar,
  setOpen: (val) => (open.value = val),
  setOpenMobile: (val) => (openMobile.value = val),
})
</script>

<template>
  <div class="flex min-h-screen w-full bg-background text-foreground">
    <slot />
  </div>
</template>