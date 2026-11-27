<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  ImsTrashIcon,
  ImsCloseIcon,
} from '@/Components/Icons/ImsIcons.vue'

defineProps({
  email: { type: String, required: true },
})

const isDeleteModalOpen = ref(false)
const deleteForm = useForm({
  password: '',
})

const openDeleteModal = () => {
  deleteForm.reset()
  deleteForm.clearErrors()
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  deleteForm.reset()
  deleteForm.clearErrors()
}

const submitDeleteAccount = () => {
  deleteForm.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeDeleteModal(),
    onFinish: () => deleteForm.reset(),
  })
}
</script>

<template>
  <div
    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 flex flex-wrap items-center justify-between gap-4">
    <div>
      <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
        Delete Account
      </h2>
      <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
        Permanently remove your account and revoke all active sessions. This cannot be undone.
      </p>
    </div>

    <button
      type="button"
      @click="openDeleteModal"
      class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50/60 dark:bg-rose-950/30 px-3.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-100/80 dark:hover:bg-rose-950/60 transition-colors">
      <ImsTrashIcon class="h-3.5 w-3.5" />
      Delete Account
    </button>

    <div
      v-if="isDeleteModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-xs p-4"
      @click.self="closeDeleteModal">
      <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 px-5 py-3.5">
          <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
            Confirm Account Deletion
          </h2>
          <button
            type="button"
            @click="closeDeleteModal"
            class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
            <ImsCloseIcon class="h-4 w-4" />
          </button>
        </div>

        <form @submit.prevent="submitDeleteAccount" class="p-5 space-y-4">
          <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">
            Enter your current password to permanently delete <span class="font-mono font-semibold">{{ email }}</span>.
          </p>

          <div>
            <input
              v-model="deleteForm.password"
              type="password"
              placeholder="Current password"
              required
              class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
            <p v-if="deleteForm.errors.password" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
              {{ deleteForm.errors.password }}
            </p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
            <button
              type="button"
              @click="closeDeleteModal"
              class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="deleteForm.processing"
              class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-rose-700 transition-colors disabled:opacity-50">
              {{ deleteForm.processing ? 'Deleting...' : 'Delete Permanently' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>