<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProfileInformationCard from './Components/ProfileInformationCard.vue'
import UpdatePasswordCard from './Components/UpdatePasswordCard.vue'
import DeleteAccountCard from './Components/DeleteAccountCard.vue'

defineProps({
  mustVerifyEmail: { type: Boolean, default: false },
  status: { type: String, default: null },
})

const page = usePage()
const user = computed(() => page.props.auth?.user || {})

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
  <Head title="Profile Settings" />

  <AdminLayout>
    <div class="w-full space-y-5">
      <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
          Profile Settings
        </h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Manage your personal account details, profile photo, and security credentials.
        </p>
      </div>

      <div
        class="grid grid-cols-2 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3">
          <div class="text-[11px] text-slate-500 dark:text-zinc-400">Role</div>
          <div class="mt-1 text-xs font-semibold capitalize text-emerald-600 dark:text-emerald-400">
            {{ user.role || 'Member' }}
          </div>
        </div>

        <div class="px-4 py-3">
          <div class="text-[11px] text-slate-500 dark:text-zinc-400">Sign-in Method</div>
          <div class="mt-1 text-xs font-medium capitalize text-slate-900 dark:text-zinc-100">
            {{ user.provider || 'Email & Password' }}
          </div>
        </div>

        <div class="px-4 py-3">
          <div class="text-[11px] text-slate-500 dark:text-zinc-400">Verification</div>
          <div
            class="mt-1 text-xs font-medium"
            :class="user.email_verified_at ? 'text-slate-900 dark:text-zinc-100' : 'text-amber-600 dark:text-amber-400'">
            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
          </div>
        </div>

        <div class="px-4 py-3">
          <div class="text-[11px] text-slate-500 dark:text-zinc-400">Member Since</div>
          <div class="mt-1 font-mono text-xs font-medium text-slate-900 dark:text-zinc-100">
            {{ formatDate(user.created_at) }}
          </div>
        </div>
      </div>

      <ProfileInformationCard
        :user="user"
        :must-verify-email="mustVerifyEmail"
        :status="status"
      />

      <UpdatePasswordCard />

      <DeleteAccountCard :email="user.email || ''" />
    </div>
  </AdminLayout>
</template>