<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ImsCloseIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  user: { type: Object, default: null },
  targetRole: { type: String, required: true },
})

const emit = defineEmits(['close'])
const processing = ref(false)

const submitRoleChange = () => {
  if (!props.user || processing.value) return
  processing.value = true

  router.patch(
    route('users.update-role', props.user.id),
    { role: props.targetRole },
    {
      preserveScroll: true,
      onSuccess: () => emit('close'),
      onFinish: () => {
        processing.value = false
      },
    }
  )
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-xs p-4"
    @click.self="emit('close')">
    <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
      <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 px-5 py-3.5">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          {{ targetRole === 'admin' ? 'Grant Admin Access' : 'Revoke Admin Access' }}
        </h2>
        <button
          type="button"
          @click="emit('close')"
          class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
          <ImsCloseIcon class="h-4 w-4" />
        </button>
      </div>

      <div class="p-5 space-y-4">
        <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">
          <template v-if="targetRole === 'admin'">
            Promote
            <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ user?.name }}</span>
            (<span class="font-mono">{{ user?.email }}</span>) to Admin? They will gain full access to inventory, alerts, inquiries, announcements, and user roles.
          </template>
          <template v-else>
            Remove admin privileges from
            <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ user?.name }}</span>
            (<span class="font-mono">{{ user?.email }}</span>)? They will lose access to the admin panel immediately.
          </template>
        </p>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="emit('close')"
            class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
            Cancel
          </button>
          <button
            type="button"
            :disabled="processing"
            @click="submitRoleChange"
            :class="[
              'rounded-lg px-4 py-1.5 text-xs font-medium transition-colors disabled:opacity-50',
              targetRole === 'admin'
                ? 'bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200'
                : 'bg-rose-600 text-white hover:bg-rose-700',
            ]">
            {{ processing ? 'Saving...' : targetRole === 'admin' ? 'Confirm Admin' : 'Revoke Access' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>