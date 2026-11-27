<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AnnouncementStatsGrid from './Components/AnnouncementStatsGrid.vue'
import AnnouncementsTable from './Components/AnnouncementsTable.vue'
import AnnouncementFormModal from './Components/AnnouncementFormModal.vue'
import {
  ImsPlusIcon,
  ImsSearchIcon,
  ImsAnnouncementsIcon,
} from '@/Components/Icons/ImsIcons.vue'

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
  isModalOpen.value = true
}

const openEditModal = (item) => {
  editingAnnouncement.value = item
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingAnnouncement.value = null
}

const toggleStatus = (item) => {
  router.patch(route('announcements.toggle', item.id), {}, { preserveScroll: true })
}

const deleteAnnouncement = (item) => {
  if (!confirm('Delete this announcement?')) return
  router.delete(route('announcements.destroy', item.id), { preserveScroll: true })
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
          <ImsPlusIcon class="h-3.5 w-3.5" />
          New Announcement
        </button>
      </div>

      <AnnouncementStatsGrid
        :stats="stats"
        :status="status"
        @select-status="setStatus" />

      <div
        v-if="activePreview.length > 0"
        class="flex items-center gap-3 rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5">
        <span class="inline-flex items-center gap-1.5 shrink-0 text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
          <ImsAnnouncementsIcon class="h-3.5 w-3.5" />
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
          <ImsSearchIcon class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
          <input
            v-model="search"
            type="text"
            placeholder="Filter announcement text..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

      <AnnouncementsTable
        :announcements="announcements"
        @toggle-status="toggleStatus"
        @edit="openEditModal"
        @delete="deleteAnnouncement" />

      <AnnouncementFormModal
        :open="isModalOpen"
        :announcement="editingAnnouncement"
        @close="closeModal" />
    </div>
  </AdminLayout>
</template>