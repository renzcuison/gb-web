<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ImsShieldCheckIcon,
  ImsShieldOffIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  users: { type: Object, required: true },
  currentUserId: { type: Number, required: true },
})

const emit = defineEmits(['change-role'])

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
            <th class="px-4 py-3">User</th>
            <th class="px-4 py-3">Auth Provider</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Joined</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
          <tr
            v-for="user in users.data"
            :key="user.id"
            class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
            <td class="px-4 py-3">
              <div class="font-medium text-slate-900 dark:text-zinc-100">
                {{ user.name }}
                <span
                  v-if="user.id === currentUserId"
                  class="ml-1 font-mono text-[11px] font-normal text-slate-400 dark:text-zinc-500">
                  (you)
                </span>
              </div>
              <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                {{ user.email }}
              </div>
            </td>

            <td class="px-4 py-3 text-slate-600 dark:text-zinc-300 capitalize">
              {{ user.provider || 'Email' }}
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <span
                :class="[
                  'text-xs font-medium',
                  user.role === 'admin'
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-slate-500 dark:text-zinc-400',
                ]">
                {{ user.role === 'admin' ? 'Admin' : 'Member' }}
              </span>
            </td>

            <td class="px-4 py-3 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
              {{ formatDate(user.created_at) }}
            </td>

            <td class="px-4 py-3 text-right whitespace-nowrap">
              <span
                v-if="user.id === currentUserId"
                class="text-[11px] text-slate-400 dark:text-zinc-500">
                Current session
              </span>
              <div v-else class="inline-flex items-center justify-end gap-1.5">
                <button
                  v-if="user.role !== 'admin'"
                  type="button"
                  @click="emit('change-role', user)"
                  class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                  <ImsShieldCheckIcon class="h-3 w-3" />
                  Make Admin
                </button>

                <button
                  v-else
                  type="button"
                  @click="emit('change-role', user)"
                  class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2.5 py-1 text-[11px] font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                  <ImsShieldOffIcon class="h-3 w-3" />
                  Revoke Admin
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="users.data.length === 0">
            <td colspan="5" class="py-12 text-center text-xs text-slate-400 dark:text-zinc-500">
              No users match the current filter.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="users.links && users.links.length > 3"
      class="flex items-center justify-between border-t border-slate-200/80 dark:border-zinc-800 px-4 py-3 text-xs text-slate-500 dark:text-zinc-400">
      <div>
        Showing <span class="font-mono">{{ users.from ?? 0 }}</span>–<span class="font-mono">{{ users.to ?? 0 }}</span> of
        <span class="font-mono">{{ users.total }}</span>
      </div>
      <div class="flex items-center gap-1">
        <template v-for="(link, idx) in users.links" :key="idx">
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