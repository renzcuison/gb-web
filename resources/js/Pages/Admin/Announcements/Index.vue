<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Plus,
  Search,
  Megaphone,
  Pencil,
  Trash2,
  X,
} from 'lucide-vue-next'

const props = defineProps({
  announcements: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      active: 0,
      inactive: 0,
      high_priority: 0,
    }),
  },
  activePreview: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      status: 'all',
    }),
  },
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')

const isModalOpen = ref(false)
const editingAnnouncement = ref(null)

const form = useForm({
  content: '',
  priority: 0,
  is_active: true,
})

let searchTimeout = null
const applyFilters = () => {
  router.get(
    route('announcements.index'),
    {
      search: search.value || undefined,
      status: status.value !== 'all' ? status.value : undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 300)
})

const setStatus = (val) => {
  status.value = val
  applyFilters()
}

const openCreateModal = () => {
  editingAnnouncement.value = null
  form.reset()
  form.clearErrors()
  form.priority = 0
  form.is_active = true
  isModalOpen.value = true
}

const openEditModal = (item) => {
  editingAnnouncement.value = item
  form.clearErrors()
  form.content = item.content
  form.priority = item.priority
  form.is_active = Boolean(item.is_active)
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingAnnouncement.value = null
}

const submitForm = () => {
  if (editingAnnouncement.value) {
    form.patch(route('announcements.update', editingAnnouncement.value.id), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  } else {
    form.post(route('announcements.store'), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  }
}

const toggleStatus = (item) => {
  router.patch(route('announcements.toggle', item.id), {}, { preserveScroll: true })
}

const deleteAnnouncement = (item) => {
  if (!confirm('Delete this announcement?')) return
  router.delete(route('announcements.destroy', item.id), { preserveScroll: true })
}

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
  <Head title="Announcements" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
            Announcements
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Manage active storefront banners and dashboard notices ordered by priority.
          </p>
        </div>

        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 dark:bg-zinc-100 px-3.5 py-2 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
          <Plus class="h-3.5 w-3.5" />
          New Announcement
        </button>
      </div>

      <div
        class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
        <button
          type="button"
          @click="setStatus('all')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'all'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Total Notices</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.total }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('active')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'active'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Active (Live)</div>
          <div class="mt-1 font-mono text-lg font-semibold text-emerald-600 dark:text-emerald-400">
            {{ stats.active }}
          </div>
        </button>

        <button
          type="button"
          @click="setStatus('inactive')"
          :class="[
            'px-4 py-3.5 text-left transition-colors',
            status === 'inactive'
              ? 'bg-slate-50 dark:bg-zinc-800/60'
              : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
          ]">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Inactive</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.inactive }}
          </div>
        </button>

        <div class="px-4 py-3.5">
          <div class="text-xs text-slate-500 dark:text-zinc-400">Pinned Priority (&gt; 0)</div>
          <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
            {{ stats.high_priority }}
          </div>
        </div>
      </div>

      <div
        v-if="activePreview.length > 0"
        class="flex items-center gap-3 rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5">
        <span class="inline-flex items-center gap-1.5 shrink-0 text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
          <Megaphone class="h-3.5 w-3.5" />
          Live Top Notice
        </span>
        <span class="text-slate-300 dark:text-zinc-700">|</span>
        <p class="text-xs text-slate-700 dark:text-zinc-300 truncate">
          {{ activePreview[0].content }}
        </p>
        <span class="ml-auto shrink-0 font-mono text-[11px] text-slate-400 dark:text-zinc-500">
          Priority {{ activePreview[0].priority }}
        </span>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 text-xs">
          <button
            v-for="tab in [
              { key: 'all', label: 'All' },
              { key: 'active', label: 'Active' },
              { key: 'inactive', label: 'Inactive' },
            ]"
            :key="tab.key"
            type="button"
            @click="setStatus(tab.key)"
            :class="[
              'rounded-md px-3 py-1 font-medium transition-colors',
              status === tab.key
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                : 'text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100',
            ]">
            {{ tab.label }}
          </button>
        </div>

        <div class="relative w-full sm:w-72">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Filter announcement text..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

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
                    @click="toggleStatus(item)"
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
                      @click="openEditModal(item)"
                      title="Edit announcement"
                      class="rounded-md border border-slate-200 dark:border-zinc-700 p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                      <Pencil class="h-3.5 w-3.5" />
                    </button>

                    <button
                      type="button"
                      @click="deleteAnnouncement(item)"
                      title="Delete announcement"
                      class="rounded-md border border-slate-200 dark:border-zinc-700 p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                      <Trash2 class="h-3.5 w-3.5" />
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

      <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-xs p-4"
        @click.self="closeModal">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
          <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 px-5 py-3.5">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
              {{ editingAnnouncement ? 'Edit Announcement' : 'New Announcement' }}
            </h2>
            <button
              type="button"
              @click="closeModal"
              class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
              <X class="h-4 w-4" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="p-5 space-y-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300">
                  Message Content
                </label>
                <span class="font-mono text-[11px] text-slate-400 dark:text-zinc-500">
                  {{ form.content.length }}/255
                </span>
              </div>
              <textarea
                v-model="form.content"
                rows="3"
                maxlength="255"
                required
                placeholder="e.g., Free Metro Manila shipping on orders over ₱2,500 this weekend!"
                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-600 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
              <p v-if="form.errors.content" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                {{ form.errors.content }}
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1">
                  Priority (0–100)
                </label>
                <input
                  v-model.number="form.priority"
                  type="number"
                  min="0"
                  max="100"
                  required
                  class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-1.5 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                  Higher numbers display first.
                </p>
                <p v-if="form.errors.priority" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                  {{ form.errors.priority }}
                </p>
              </div>

              <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1">
                  Status
                </label>
                <label class="mt-2 inline-flex items-center gap-2 cursor-pointer select-none">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 dark:border-zinc-700 text-slate-900 focus:ring-0" />
                  <span class="text-xs text-slate-700 dark:text-zinc-300">
                    Active immediately
                  </span>
                </label>
              </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
              <button
                type="button"
                @click="closeModal"
                class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 disabled:opacity-50">
                {{ editingAnnouncement ? 'Save Changes' : 'Publish Notice' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>