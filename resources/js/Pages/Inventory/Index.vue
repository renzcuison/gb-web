<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

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

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Inventory Stock</h1>
        <p class="mt-2 text-sm text-slate-600">
          Monitor variant quantities, track threshold alerts, and adjust stock levels.
        </p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product / Variant</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Category & Brand</TableHead>
            <TableHead>Stock Status</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="variant in inventory.data" :key="variant.id">
            <TableCell>
              <div class="font-semibold text-slate-900">{{ variant.product.name }}</div>
              <div class="text-xs text-slate-500 font-medium mt-0.5">{{ variant.variant_label }}</div>
            </TableCell>
            <TableCell class="font-mono text-slate-600 text-sm">
              {{ variant.sku }}
            </TableCell>
            <TableCell>
              <div class="flex gap-2">
                <Badge variant="secondary">{{ variant.product.category?.name }}</Badge>
                <Badge variant="secondary">{{ variant.product.brand?.name }}</Badge>
              </div>
            </TableCell>
            <TableCell>
              <div class="flex items-center space-x-2">
                <span :class="['text-sm font-bold', variant.stock_qty <= variant.low_stock_threshold ? 'text-rose-600' : 'text-emerald-600']">
                  {{ variant.stock_qty }} units
                </span>
                <Badge v-if="variant.stock_qty <= variant.low_stock_threshold" variant="destructive">
                  Low Stock
                </Badge>
              </div>
            </TableCell>
            <TableCell class="text-right">
              <Button variant="outline" size="sm" @click="openAdjustmentModal(variant)">
                Adjust Stock
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <Dialog :open="isModalOpen" @update:open="closeAdjustmentModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Adjust Stock Levels</DialogTitle>
          <DialogDescription v-if="selectedVariant">
            {{ selectedVariant.product.name }} ({{ selectedVariant.variant_label }})
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Adjustment Type</label>
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="form.type = 'in'"
                :class="['py-3 px-4 rounded-xl border text-sm font-bold flex flex-col items-center justify-center transition', form.type === 'in' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-600']"
              >
                <span class="text-lg">＋</span>
                <span>Stock In</span>
              </button>
              <button
                type="button"
                @click="form.type = 'out'"
                :class="['py-3 px-4 rounded-xl border text-sm font-bold flex flex-col items-center justify-center transition', form.type === 'out' ? 'border-rose-500 bg-rose-50/50 text-rose-700 ring-2 ring-rose-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-600']"
              >
                <span class="text-lg">－</span>
                <span>Stock Out</span>
              </button>
            </div>
          </div>

          <div>
            <label for="quantity" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Quantity</label>
            <Input
              v-model.number="form.quantity"
              type="number"
              id="quantity"
              min="1"
              class="w-full font-semibold"
            />
            <p v-if="form.errors.quantity" class="text-xs text-rose-500 mt-1">{{ form.errors.quantity }}</p>
          </div>

          <div>
            <label for="reason" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Reason</label>
            <textarea
              v-model="form.reason"
              id="reason"
              rows="3"
              placeholder="e.g., Weekly restocking, damaged item write-off"
              class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            />
            <p v-if="form.errors.reason" class="text-xs text-rose-500 mt-1">{{ form.errors.reason }}</p>
          </div>

          <DialogFooter class="pt-4 border-t border-slate-100">
            <Button type="button" variant="ghost" @click="closeAdjustmentModal">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              Confirm Adjustment
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>