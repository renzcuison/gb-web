<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'

const props = defineProps({
  open: Boolean,
  variant: Object,
})

const emit = defineEmits(['update:open'])

const form = useForm({
  variant_id: '',
  quantity: 1,
  type: 'in',
  reason: '',
})

watch(
  () => props.variant,
  (val) => {
    if (val) {
      form.clearErrors()
      form.variant_id = val.id
      form.quantity = 1
      form.type = 'in'
      form.reason = ''
    }
  }
)

const close = () => {
  emit('update:open', false)
  form.reset()
}

const submit = () => {
  form.post(route('inventory.adjust'), {
    preserveScroll: true,
    onSuccess: () => close(),
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="close">
    <DialogContent class="sm:max-w-[400px] p-5 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-100 shadow-none">
      <DialogHeader class="pb-2 border-b border-slate-100 dark:border-zinc-800/80">
        <DialogTitle class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
          Adjust Stock Levels
        </DialogTitle>
        <DialogDescription v-if="variant" class="text-xs font-normal text-slate-500 dark:text-zinc-400 mt-0.5">
          {{ variant.product?.name }} —
          <span class="font-mono text-slate-600 dark:text-zinc-300">{{ variant.variant_label }}</span>
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-3.5 pt-3">
        <div>
          <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1.5">
            Adjustment Type
          </label>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="form.type = 'in'"
              :class="[
                'py-2 px-3 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 transition-all shadow-none',
                form.type === 'in'
                  ? 'border-emerald-500/60 bg-emerald-50/60 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-700/60'
                  : 'border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400',
              ]">
              <span class="text-sm font-bold">＋</span>
              <span>Stock In</span>
            </button>
            <button
              type="button"
              @click="form.type = 'out'"
              :class="[
                'py-2 px-3 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 transition-all shadow-none',
                form.type === 'out'
                  ? 'border-rose-500/60 bg-rose-50/60 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-700/60'
                  : 'border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400',
              ]">
              <span class="text-sm font-bold">－</span>
              <span>Stock Out</span>
            </button>
          </div>
        </div>

        <div>
          <label for="quantity" class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1.5">
            Quantity
          </label>
          <Input
            v-model.number="form.quantity"
            type="number"
            id="quantity"
            min="1"
            class="h-8 text-xs font-mono font-medium dark:bg-zinc-800/60 dark:border-zinc-700/80 dark:text-zinc-100 focus-visible:ring-1 shadow-none"
          />
          <p v-if="form.errors.quantity" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
            {{ form.errors.quantity }}
          </p>
        </div>

        <div>
          <label for="reason" class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1.5">
            Reason
          </label>
          <textarea
            v-model="form.reason"
            id="reason"
            rows="2.5"
            placeholder="e.g., Weekly restocking, damaged item write-off"
            class="flex min-h-[70px] w-full rounded-md border border-slate-200 dark:border-zinc-800 bg-transparent px-2.5 py-1.5 text-xs text-slate-900 dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-400 dark:focus-visible:ring-zinc-600 shadow-none"
          />
          <p v-if="form.errors.reason" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
            {{ form.errors.reason }}
          </p>
        </div>

        <DialogFooter class="pt-3 border-t border-slate-100 dark:border-zinc-800/80 gap-2">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            @click="close"
            class="h-8 text-xs font-medium text-slate-600 dark:text-zinc-400 dark:hover:text-zinc-100 shadow-none">
            Cancel
          </Button>
          <Button
            type="submit"
            size="sm"
            :disabled="form.processing"
            class="h-8 text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-none">
            Confirm Adjustment
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>