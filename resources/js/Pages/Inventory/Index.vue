<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Plus } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  inventory: Object
})

const isModalOpen = ref(false)
const selectedVariant = ref(null)

const form = useForm({
  variant_id: '',
  quantity: 1,
  type: 'in',
  reason: ''
})

const openAdjustmentModal = (variant) => {
  selectedVariant.value = variant
  form.variant_id = variant.id
  form.quantity = 1
  form.type = 'in'
  form.reason = ''
  isModalOpen.value = true
}

const closeAdjustmentModal = () => {
  isModalOpen.value = false
  selectedVariant.value = null
  form.reset()
}

const handleSubmit = () => {
  form.post(route('inventory.adjust'), {
    onSuccess: () => closeAdjustmentModal()
  })
}
</script>

<template>
  <div class="max-w-7xl mx-auto">

    <Head title="Inventory Management" />

    <!-- Top Header & Primary Action Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">
          Inventory Stock
        </h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-zinc-400">
          Monitor variant quantities, track threshold alerts, and adjust stock levels.
        </p>
      </div>

      <!-- Button leading to the creation page -->
      <div>
        <Button as-child
          class="gap-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
          <Link :href="route('products.create')">
            <Plus class="h-4 w-4" />
            <span>Add Item Group</span>
          </Link>
        </Button>
      </div>
    </div>

    <!-- Inventory Table -->
    <div
      class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden transition-colors">
      <Table>
        <TableHeader>
          <TableRow class="border-slate-200 dark:border-zinc-800 dark:hover:bg-zinc-800/50">
            <TableHead class="dark:text-zinc-300 font-semibold">Product / Variant</TableHead>
            <TableHead class="dark:text-zinc-300 font-semibold">SKU</TableHead>
            <TableHead class="dark:text-zinc-300 font-semibold">Category & Brand</TableHead>
            <TableHead class="dark:text-zinc-300 font-semibold">Stock Status</TableHead>
            <TableHead class="text-right dark:text-zinc-300 font-semibold">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="variant in inventory.data" :key="variant.id"
            class="border-slate-100 dark:border-zinc-800 dark:hover:bg-zinc-800/50 transition-colors">
            <TableCell>
              <div class="font-semibold text-slate-900 dark:text-zinc-100">{{ variant.product.name }}</div>
              <div class="text-xs text-slate-500 dark:text-zinc-400 font-medium mt-0.5">{{ variant.variant_label }}
              </div>
            </TableCell>
            <TableCell class="font-mono text-slate-600 dark:text-zinc-400 text-sm">
              {{ variant.sku }}
            </TableCell>
            <TableCell>
              <div class="flex gap-2">
                <Badge variant="secondary" class="dark:bg-zinc-800 dark:text-zinc-200">{{ variant.product.category?.name
                  }}</Badge>
                <Badge variant="secondary" class="dark:bg-zinc-800 dark:text-zinc-200">{{ variant.product.brand?.name }}
                </Badge>
              </div>
            </TableCell>
            <TableCell>
              <div class="flex items-center space-x-2">
                <span
                  :class="['text-sm font-bold', variant.stock_qty <= variant.low_stock_threshold ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400']">
                  {{ variant.stock_qty }} units
                </span>
                <Badge v-if="variant.stock_qty <= variant.low_stock_threshold" variant="destructive">
                  Low Stock
                </Badge>
              </div>
            </TableCell>
            <TableCell class="text-right">
              <Button variant="outline" size="sm" @click="openAdjustmentModal(variant)"
                class="dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Adjust Stock
              </Button>
            </TableCell>
          </TableRow>
          <TableRow v-if="!inventory.data || inventory.data.length === 0" class="dark:border-zinc-800">
            <TableCell colspan="5" class="h-32 text-center text-slate-400 dark:text-zinc-500 text-sm">
              No inventory records logged yet.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Adjustment Modal -->
    <Dialog :open="isModalOpen" @update:open="closeAdjustmentModal">
      <DialogContent class="sm:max-w-[425px] dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-100">
        <DialogHeader>
          <DialogTitle class="dark:text-zinc-100">Adjust Stock Levels</DialogTitle>
          <DialogDescription v-if="selectedVariant" class="dark:text-zinc-400">
            {{ selectedVariant.product.name }} ({{ selectedVariant.variant_label }})
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
              Adjustment Type
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" @click="form.type = 'in'" :class="[
                'py-3 px-4 rounded-xl border text-sm font-bold flex flex-col items-center justify-center transition',
                form.type === 'in'
                  ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 ring-2 ring-emerald-500/20'
                  : 'border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300'
              ]">
                <span class="text-lg">＋</span>
                <span>Stock In</span>
              </button>
              <button type="button" @click="form.type = 'out'" :class="[
                'py-3 px-4 rounded-xl border text-sm font-bold flex flex-col items-center justify-center transition',
                form.type === 'out'
                  ? 'border-rose-500 bg-rose-50/50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 ring-2 ring-rose-500/20'
                  : 'border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300'
              ]">
                <span class="text-lg">－</span>
                <span>Stock Out</span>
              </button>
            </div>
          </div>

          <div>
            <label for="quantity"
              class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
              Quantity
            </label>
            <Input v-model.number="form.quantity" type="number" id="quantity" min="1"
              class="w-full font-semibold dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100" />
            <p v-if="form.errors.quantity" class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ form.errors.quantity
              }}</p>
          </div>

          <div>
            <label for="reason"
              class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
              Reason
            </label>
            <textarea v-model="form.reason" id="reason" rows="3"
              placeholder="e.g., Weekly restocking, damaged item write-off"
              class="flex min-h-[80px] w-full rounded-md border border-slate-200 dark:border-zinc-800 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-zinc-100 placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 dark:focus-visible:ring-zinc-400" />
            <p v-if="form.errors.reason" class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ form.errors.reason }}
            </p>
          </div>

          <DialogFooter class="pt-4 border-t border-slate-100 dark:border-zinc-800">
            <Button type="button" variant="ghost" @click="closeAdjustmentModal"
              class="dark:text-zinc-400 dark:hover:text-zinc-100">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing" class="dark:bg-zinc-100 dark:text-zinc-900">
              Confirm Adjustment
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>