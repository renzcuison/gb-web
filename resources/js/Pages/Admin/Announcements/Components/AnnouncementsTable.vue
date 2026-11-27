<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ImsPencilIcon,
  ImsTrashIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  announcements: { type: Object, required: true },
})

const emit = defineEmits(['toggle-status', 'edit', 'delete'])

const formatDate = (iso) => {
  if (!iso) return '—'
  return new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(iso))
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-slate-200/80 dark:border-zinc-800 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            <th class="px-4 py-3">Announcement Content</th>
            <th class="px-4 py-3">Priority</th>
            <th class="px-4 py-3">Visibility</th>
            <th class="px-4 py-3">Created</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
          <tr
            v-for="item in announcements.data"
            :key="item.id"
            class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
            <td class="px-4 py-3 max-w-xl">
              <p class="text-slate-800 dark:text-zinc-200 leading-relaxed">
                {{ item.content }}
              </p>
            </td>

            <td class="px-4 py-3 font-mono text-slate-600 dark:text-zinc-300">
              {{ item.priority }}
            </td>

            <td class="px-4 py-3">
              <button
                type="button"
                @click="emit('toggle-status', item)"
                :class="[
                  'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-medium transition-colors',
                  item.is_active
                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-400 dark:hover:bg-emerald-950'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700',
                ]">
                <span
                  :class="[
                    'h-1.5 w-1.5 rounded-full',
                    item.is_active ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-zinc-500',
                  ]" />
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </button>
            </td>

            <td class="px-4 py-3 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
              {{ formatDate(item.created_at) }}
            </td>

            <td class="px-4 py-3 text-right whitespace-nowrap">
              <div class="inline-flex items-center justify-end gap-1.5">
                <button
                  type="button"
                  @click="emit('edit', item)"
                  title="Edit announcement"
                  class="rounded-md border border-slate-200 dark:border-zinc-700 p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                  <ImsPencilIcon class="h-3.5 w-3.5" />
                </button>

                <button
                  type="button"
                  @click="emit('delete', item)"
                  title="Delete announcement"
                  class="rounded-md border border-slate-200 dark:border-zinc-700 p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                  <ImsTrashIcon class="h-3.5 w-3.5" />
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="announcements.data.length === 0">
            <td colspan="5" class="py-12 text-center text-xs text-slate-400 dark:text-zinc-500">
              No announcements found. Create one to broadcast a notice.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="announcements.links && announcements.links.length > 3"
      class="flex items-center justify-between border-t border-slate-200/80 dark:border-zinc-800 px-4 py-3 text-xs text-slate-500 dark:text-zinc-400">
      <div>
        Showing <span class="font-mono">{{ announcements.from ?? 0 }}</span>–<span class="font-mono">{{ announcements.to ?? 0 }}</span> of
        <span class="font-mono">{{ announcements.total }}</span>
      </div>
      <div class="flex items-center gap-1">
        <template v-for="(link, idx) in announcements.links" :key="idx">
          <Link
            v-if="link.url"
            :href="link.url"
            preserve-scroll
            v-html="link.label"
            :class="[
              'rounded-md px-2.5 py-1 transition-colors',
              link.active
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-medium'
                : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400',
            ]" />
          <span
            v-else
            v-html="link.label"
            class="px-2.5 py-1 text-slate-300 dark:text-zinc-600" />
        </template>
      </div>
    </div>
  </div>
</template>