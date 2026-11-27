<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ImsPlusIcon } from '@/Components/Icons/ImsIcons.vue'

const page = usePage()

const firstName = computed(() => {
  const fullName = page.props.auth?.user?.name?.trim()
  return fullName ? fullName.split(' ')[0] : ''
})

const greetingText = computed(() => {
  const hour = new Date().getHours()
  const prefix = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening'
  return firstName.value ? `${prefix}, ${firstName.value}` : `${prefix}, welcome back`
})

const formattedToday = computed(() =>
  new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
)
</script>

<template>
  <div class="flex items-center justify-between pt-1">
    <div class="space-y-0.5">
      <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
        {{ greetingText }}
      </h1>
      <p class="text-xs text-zinc-500 dark:text-zinc-400">
        {{ formattedToday }}
      </p>
    </div>

    <Link
      :href="route('products.create')"
      class="inline-flex items-center gap-1.5 h-8 px-3 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 text-xs font-medium"
    >
      <ImsPlusIcon class="h-3.5 w-3.5" />
      <span>Add Item Group</span>
    </Link>
  </div>
</template>