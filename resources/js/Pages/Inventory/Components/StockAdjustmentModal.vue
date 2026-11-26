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
    <DialogContent
      class="sm:max-w-[380px] p-5 bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-none">
      <DialogHeader class="pb-3 border-b border-zinc-100 dark:border-zinc-800">
        <DialogTitle class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
          Adjust Stock
        </DialogTitle>
        <DialogDescription v-if="variant" class="text-xs font-normal text-zinc-500 dark:text-zinc-400 mt-0.5">
          {{ variant.product?.name }} —
          <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ variant.variant_label || 'Standard' }}</span>
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-3.5 pt-2">
        <div>
          <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
            Type
          </label>
          <div class="grid grid-cols-2 border border-zinc-200 dark:border-zinc-800 rounded-md overflow-hidden">
            <button type="button" @click="form.type = 'in'" :class="[
              'py-1.5 px-3 text-xs font-medium transition-colors',
              form.type === 'in'
                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800',
            ]">
              + Stock In
            </button>
            <button type="button" @click="form.type = 'out'" :class="[
              'py-1.5 px-3 text-xs font-medium border-l border-zinc-200 dark:border-zinc-800 transition-colors',
              form.type === 'out'
                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800',
            ]">
              − Stock Out
            </button>
          </div>
        </div>

        <div>
          <label for="quantity" class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
            Quantity
          </label>
          <Input v-model.number="form.quantity" type="number" id="quantity" min="1"
            class="h-8 text-xs font-mono font-medium bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 focus-visible:ring-1 shadow-none" />
          <p v-if="form.errors.quantity" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
            {{ form.errors.quantity }}
          </p>
        </div>

        <div>
          <label for="reason" class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
            Reason
          </label>
          <textarea v-model="form.reason" id="reason" rows="2" placeholder="e.g., Weekly restock, damaged item"
            class="flex min-h-[64px] w-full rounded-md border border-zinc-200 dark:border-zinc-800 bg-transparent px-2.5 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-400 shadow-none" />
          <p v-if="form.errors.reason" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
            {{ form.errors.reason }}
          </p>
        </div>

        <DialogFooter class="pt-3 border-t border-zinc-100 dark:border-zinc-800 gap-2">
          <Button type="button" variant="ghost" size="sm" @click="close"
            class="h-8 text-xs font-medium text-zinc-600 dark:text-zinc-400 shadow-none">
            Cancel
          </Button>
          <Button type="submit" size="sm" :disabled="form.processing"
            class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-none">
            Save
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>