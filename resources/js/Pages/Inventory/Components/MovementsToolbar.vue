<script setup>
import { computed } from 'vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
  ImsSearchIcon,
  ImsCloseIcon,
  ImsChevronDownIcon,
  ImsCheckIcon,
} from '@/Components/Icons/ImsIcons.vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

const props = defineProps({
  search: { type: String, required: true },
  type: { type: String, required: true },
  user: { type: [Number, String, null], default: null },
  from: { type: String, required: true },
  to: { type: String, required: true },
  stats: { type: Object, required: true },
  actors: { type: Array, default: () => [] },
  hasActiveFilters: { type: Boolean, default: false },
})

const emit = defineEmits([
  'update:search',
  'update:type',
  'toggle-user',
  'update:from',
  'update:to',
  'reset',
])

const typeTabs = computed(() => [
  { key: 'all', label: 'All Movements', count: props.stats.total },
  { key: 'in', label: 'Stock In', count: props.stats.in_count },
  { key: 'out', label: 'Stock Out', count: props.stats.out_count },
])

const selectedActorName = computed(
  () => props.actors.find((a) => a.id === props.user)?.name || 'Recorded By'
)
</script>

<template>
  <div class="space-y-3">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2">
      <div class="relative flex-1 max-w-xs">
        <ImsSearchIcon
          class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <Input
          :model-value="search"
          @update:model-value="emit('update:search', $event)"
          type="text"
          placeholder="Search item, SKU, or reason..."
          class="h-8 pl-8 pr-7 text-xs bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:ring-1 shadow-none" />
        <button
          v-if="search"
          type="button"
          @click="emit('update:search', '')"
          class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
          <ImsCloseIcon class="h-3.5 w-3.5" />
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              :class="[
                'h-8 text-xs font-medium gap-1.5 px-2.5 shadow-none bg-white dark:bg-zinc-900',
                user
                  ? 'border-zinc-900 text-zinc-900 dark:border-zinc-200 dark:text-zinc-100'
                  : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300',
              ]">
              <span>{{ selectedActorName }}</span>
              <ImsChevronDownIcon class="h-3 w-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-48 dark:bg-zinc-900 dark:border-zinc-800 shadow-none">
            <template v-if="actors.length > 0">
              <DropdownMenuItem
                v-for="actor in actors"
                :key="actor.id"
                @click="emit('toggle-user', actor.id)"
                class="text-xs flex items-center justify-between cursor-pointer">
                <span>{{ actor.name }}</span>
                <ImsCheckIcon v-if="user === actor.id" class="h-3.5 w-3.5" />
              </DropdownMenuItem>
            </template>
            <div v-else class="px-2.5 py-2 text-xs text-zinc-400 dark:text-zinc-500">
              No recorded users
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <div class="flex items-center gap-1.5">
          <input
            :value="from"
            @change="emit('update:from', $event.target.value)"
            type="date"
            aria-label="From date"
            class="h-8 rounded-md border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-2.5 text-xs text-zinc-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-400" />
          <span class="text-xs text-zinc-400">to</span>
          <input
            :value="to"
            @change="emit('update:to', $event.target.value)"
            type="date"
            aria-label="To date"
            class="h-8 rounded-md border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-2.5 text-xs text-zinc-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-400" />
        </div>

        <button
          v-if="hasActiveFilters"
          type="button"
          @click="emit('reset')"
          class="text-xs text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 px-1.5">
          Reset
        </button>
      </div>
    </div>

    <div class="flex items-center gap-6 border-b border-zinc-200 dark:border-zinc-800 text-xs">
      <button
        v-for="tab in typeTabs"
        :key="tab.key"
        type="button"
        @click="emit('update:type', tab.key)"
        :class="[
          '-mb-px pb-2.5 border-b-2 transition-colors flex items-center gap-1.5',
          type === tab.key
            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-medium'
            : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200',
        ]">
        <span>{{ tab.label }}</span>
        <span class="font-mono text-[11px] text-zinc-400">{{ tab.count }}</span>
      </button>
    </div>
  </div>
</template>