<script setup>
import { computed } from 'vue'
import { ImsLoaderIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  size: {
    type: String,
    default: 'sm',
  },
  label: {
    type: String,
    default: 'Loading',
  },
})

const sizeClasses = computed(() => ({
  xs: 'w-3.5 h-3.5',
  sm: 'w-4 h-4',
  md: 'w-6 h-6',
  lg: 'w-8 h-8',
}[props.size] || 'w-4 h-4'))
</script>

<template>
  <span
    role="status"
    :aria-label="label"
    class="inline-flex items-center justify-center shrink-0">
    <ImsLoaderIcon :class="['animate-spin', sizeClasses]" />
    <span class="sr-only">{{ label }}</span>
  </span>
</template>