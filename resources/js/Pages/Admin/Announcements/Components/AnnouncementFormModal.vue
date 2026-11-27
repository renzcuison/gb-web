<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ImsCloseIcon } from '@/Components/Icons/ImsIcons.vue'

const props = defineProps({
  open: { type: Boolean, required: true },
  announcement: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const form = useForm({
  content: '',
  priority: 0,
  is_active: true,
})

watch(
  () => [props.open, props.announcement],
  ([isOpen]) => {
    if (!isOpen) return
    form.clearErrors()
    if (props.announcement) {
      form.content = props.announcement.content
      form.priority = props.announcement.priority
      form.is_active = Boolean(props.announcement.is_active)
    } else {
      form.reset()
      form.priority = 0
      form.is_active = true
    }
  },
  { immediate: true }
)

const submitForm = () => {
  if (props.announcement) {
    form.patch(route('announcements.update', props.announcement.id), {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    })
  } else {
    form.post(route('announcements.store'), {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    })
  }
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
          {{ announcement ? 'Edit Announcement' : 'New Announcement' }}
        </h2>
        <button
          type="button"
          @click="emit('close')"
          class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
          <ImsCloseIcon class="h-4 w-4" />
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
            @click="emit('close')"
            class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 disabled:opacity-50">
            {{ announcement ? 'Save Changes' : 'Publish Notice' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>