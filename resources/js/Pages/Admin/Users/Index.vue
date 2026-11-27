<script setup>
import { ref, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UserStatsGrid from './Components/UserStatsGrid.vue'
import UsersTable from './Components/UsersTable.vue'
import UserRoleModal from './Components/UserRoleModal.vue'
import { ImsSearchIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  users: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      admins: 0,
      members: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      role: 'all',
    }),
  },
})

const page = usePage()
const search = ref(props.filters.search ?? '')
const role = ref(props.filters.role ?? 'all')

const isModalOpen = ref(false)
const selectedUser = ref(null)
const targetRole = ref('admin')

let searchTimeout = null
const applyFilters = () => {
  router.get(
    route('users.index'),
    {
      search: search.value || undefined,
      role: role.value !== 'all' ? role.value : undefined,
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

const setRole = (val) => {
  role.value = val
  applyFilters()
}

const openRoleModal = (user) => {
  if (user.id === page.props.auth.user.id) return
  selectedUser.value = user
  targetRole.value = user.role === 'admin' ? 'user' : 'admin'
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  selectedUser.value = null
}
</script>

<template>
  <Head title="Users & Roles" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
            Users &amp; Roles
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Manage registered accounts and assign administrative access permissions.
          </p>
        </div>
      </div>

      <div
        v-if="page.props.flash?.success"
        class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
        {{ page.props.flash.success }}
      </div>
      <div
        v-if="page.props.flash?.error"
        class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-rose-600 dark:text-rose-400">
        {{ page.props.flash.error }}
      </div>

      <UserStatsGrid
        :stats="stats"
        :role="role"
        @select-role="setRole" />

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 text-xs">
          <button
            v-for="tab in [
              { key: 'all', label: 'All' },
              { key: 'admin', label: 'Admins' },
              { key: 'user', label: 'Members' },
            ]"
            :key="tab.key"
            type="button"
            @click="setRole(tab.key)"
            :class="[
              'rounded-md px-3 py-1 font-medium transition-colors',
              role === tab.key
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
            placeholder="Search name or email..."
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
        </div>
      </div>

      <UsersTable
        :users="users"
        :current-user-id="page.props.auth.user.id"
        @change-role="openRoleModal" />

      <UserRoleModal
        :open="isModalOpen"
        :user="selectedUser"
        :target-role="targetRole"
        @close="closeModal" />
    </div>
  </AdminLayout>
</template>