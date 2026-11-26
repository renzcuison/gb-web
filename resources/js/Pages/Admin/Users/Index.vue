<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    Search,
    ShieldCheck,
    ShieldOff,
    X,
} from 'lucide-vue-next'

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
const processing = ref(false)

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

const submitRoleChange = () => {
    if (!selectedUser.value || processing.value) return
    processing.value = true

    router.patch(
        route('users.update-role', selectedUser.value.id),
        { role: targetRole.value },
        {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => {
                processing.value = false
            },
        }
    )
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

    <Head title="Users & Roles" />

    <AdminLayout>
        <div class="w-full space-y-5">
            <!-- Page Header -->
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

            <!-- Flash Messages -->
            <div v-if="page.props.flash?.success"
                class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error"
                class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-rose-600 dark:text-rose-400">
                {{ page.props.flash.error }}
            </div>

            <!-- Divided KPI Strip -->
            <div
                class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
                <button type="button" @click="setRole('all')" :class="[
                    'px-4 py-3.5 text-left transition-colors',
                    role === 'all'
                        ? 'bg-slate-50 dark:bg-zinc-800/60'
                        : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
                ]">
                    <div class="text-xs text-slate-500 dark:text-zinc-400">Total Users</div>
                    <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
                        {{ stats.total }}
                    </div>
                </button>

                <button type="button" @click="setRole('admin')" :class="[
                    'px-4 py-3.5 text-left transition-colors',
                    role === 'admin'
                        ? 'bg-slate-50 dark:bg-zinc-800/60'
                        : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
                ]">
                    <div class="text-xs text-slate-500 dark:text-zinc-400">Administrators</div>
                    <div class="mt-1 font-mono text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ stats.admins }}
                    </div>
                </button>

                <button type="button" @click="setRole('user')" :class="[
                    'px-4 py-3.5 text-left transition-colors',
                    role === 'user'
                        ? 'bg-slate-50 dark:bg-zinc-800/60'
                        : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40',
                ]">
                    <div class="text-xs text-slate-500 dark:text-zinc-400">Members</div>
                    <div class="mt-1 font-mono text-lg font-semibold text-slate-900 dark:text-zinc-100">
                        {{ stats.members }}
                    </div>
                </button>
            </div>

            <!-- Segmented Tabs & Search Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div
                    class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 text-xs">
                    <button v-for="tab in [
                        { key: 'all', label: 'All' },
                        { key: 'admin', label: 'Admins' },
                        { key: 'user', label: 'Members' },
                    ]" :key="tab.key" type="button" @click="setRole(tab.key)" :class="[
                'rounded-md px-3 py-1 font-medium transition-colors',
                role === tab.key
                    ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                    : 'text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100',
            ]">
                        {{ tab.label }}
                    </button>
                </div>

                <div class="relative w-full sm:w-72">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />
                    <input v-model="search" type="text" placeholder="Search name or email..."
                        class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                </div>
            </div>

            <!-- Users Table -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-slate-200/80 dark:border-zinc-800 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Auth Provider</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
                            <tr v-for="user in users.data" :key="user.id"
                                class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-zinc-100">
                                        {{ user.name }}
                                        <span v-if="user.id === page.props.auth.user.id"
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
                                    <span :class="[
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
                                    <span v-if="user.id === page.props.auth.user.id"
                                        class="text-[11px] text-slate-400 dark:text-zinc-500">
                                        Current session
                                    </span>
                                    <div v-else class="inline-flex items-center justify-end gap-1.5">
                                        <button v-if="user.role !== 'admin'" type="button" @click="openRoleModal(user)"
                                            class="inline-flex items-center gap-1 rounded-md bg-slate-900 dark:bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors">
                                            <ShieldCheck class="h-3 w-3" />
                                            Make Admin
                                        </button>

                                        <button v-else type="button" @click="openRoleModal(user)"
                                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-zinc-700 px-2.5 py-1 text-[11px] font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                            <ShieldOff class="h-3 w-3" />
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

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3"
                    class="flex items-center justify-between border-t border-slate-200/80 dark:border-zinc-800 px-4 py-3 text-xs text-slate-500 dark:text-zinc-400">
                    <div>
                        Showing <span class="font-mono">{{ users.from ?? 0 }}</span>–<span class="font-mono">{{ users.to
                            ?? 0 }}</span> of
                        <span class="font-mono">{{ users.total }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, idx) in users.links" :key="idx">
                            <Link v-if="link.url" :href="link.url" preserve-scroll v-html="link.label" :class="[
                                'rounded-md px-2.5 py-1 transition-colors',
                                link.active
                                    ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-medium'
                                    : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400',
                            ]" />
                            <span v-else v-html="link.label" class="px-2.5 py-1 text-slate-300 dark:text-zinc-600" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Role Confirmation Modal -->
            <div v-if="isModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-xs p-4"
                @click.self="closeModal">
                <div
                    class="w-full max-w-md rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
                    <div
                        class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 px-5 py-3.5">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                            {{ targetRole === 'admin' ? 'Grant Admin Access' : 'Revoke Admin Access' }}
                        </h2>
                        <button type="button" @click="closeModal"
                            class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">
                            <template v-if="targetRole === 'admin'">
                                Promote <span class="font-semibold text-slate-900 dark:text-zinc-100">{{
                                    selectedUser?.name }}</span>
                                (<span class="font-mono">{{ selectedUser?.email }}</span>) to Admin? They will gain full
                                access to inventory, alerts, inquiries, announcements, and user roles.
                            </template>
                            <template v-else>
                                Remove admin privileges from <span
                                    class="font-semibold text-slate-900 dark:text-zinc-100">{{ selectedUser?.name
                                    }}</span>
                                (<span class="font-mono">{{ selectedUser?.email }}</span>)? They will lose access to the
                                admin panel immediately.
                            </template>
                        </p>

                        <div
                            class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
                            <button type="button" @click="closeModal"
                                class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
                                Cancel
                            </button>
                            <button type="button" :disabled="processing" @click="submitRoleChange" :class="[
                                'rounded-lg px-4 py-1.5 text-xs font-medium transition-colors disabled:opacity-50',
                                targetRole === 'admin'
                                    ? 'bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200'
                                    : 'bg-rose-600 text-white hover:bg-rose-700',
                            ]">
                                {{ processing ? 'Saving...' : targetRole === 'admin' ? 'Confirm Admin' : 'Revoke Access'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>