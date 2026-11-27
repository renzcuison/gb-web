<script setup>
import {
  ImsChevronLeftIcon,
  ImsChevronRightIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  direction: {
    type: String,
    required: true,
    validator: (value) => ['left', 'right'].includes(value),
  },
})

defineEmits(['click'])
</script>

<template>
  <button
    @click="$emit('click')"
    type="button"
    class="p-2 border border-zinc-200 hover:bg-zinc-50 transition-colors rounded-full group outline-none"
    :aria-label="`Scroll ${direction}`">
    <ImsChevronLeftIcon
      v-if="direction === 'left'"
      class="h-4 w-4 text-zinc-600 group-hover:text-black"
    />
    <ImsChevronRightIcon
      v-else
      class="h-4 w-4 text-zinc-600 group-hover:text-black"
    />
  </button>
</template>