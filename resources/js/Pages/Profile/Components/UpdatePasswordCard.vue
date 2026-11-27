<script setup>
import { useForm } from '@inertiajs/vue3'
import { ImsKeyIcon } from '@/Components/Icons/ImsIcons.vue'

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submitPassword = () => {
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
    onError: () => {
      if (passwordForm.errors.password) {
        passwordForm.reset('password', 'password_confirmation')
      }
      if (passwordForm.errors.current_password) {
        passwordForm.reset('current_password')
      }
    },
  })
}
</script>

<template>
  <form
    @submit.prevent="submitPassword"
    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          Password &amp; Security
        </h2>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Use a strong, unique password to protect administrative access.
        </p>
      </div>
      <ImsKeyIcon class="h-4 w-4 text-slate-400 dark:text-zinc-500" />
    </div>

    <div class="p-5 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label for="current_password" class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
            Current Password
          </label>
          <input
            id="current_password"
            v-model="passwordForm.current_password"
            type="password"
            autocomplete="current-password"
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
          <p v-if="passwordForm.errors.current_password" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
            {{ passwordForm.errors.current_password }}
          </p>
        </div>

        <div>
          <label for="password" class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
            New Password
          </label>
          <input
            id="password"
            v-model="passwordForm.password"
            type="password"
            autocomplete="new-password"
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
          <p v-if="passwordForm.errors.password" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
            {{ passwordForm.errors.password }}
          </p>
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
            Confirm Password
          </label>
          <input
            id="password_confirmation"
            v-model="passwordForm.password_confirmation"
            type="password"
            autocomplete="new-password"
            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
          <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
            {{ passwordForm.errors.password_confirmation }}
          </p>
        </div>
      </div>
    </div>

    <div
      class="px-5 py-3 bg-slate-50/60 dark:bg-zinc-950/40 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-end gap-3">
      <span v-if="passwordForm.recentlySuccessful" class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
        Password updated.
      </span>
      <button
        type="submit"
        :disabled="passwordForm.processing"
        class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50">
        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
      </button>
    </div>
  </form>
</template>