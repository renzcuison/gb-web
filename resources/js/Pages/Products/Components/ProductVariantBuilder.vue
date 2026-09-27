<script setup>
import { watch } from 'vue'
import { Layers, Plus, Trash2 } from 'lucide-vue-next'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  form: {
    type: Object,
    required: true
  }
})

// Auto-generate SKU helper
const generateSku = (productName, label) => {
  if (!productName) return ''
  const cleanName = productName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 4)
  const cleanLabel = label ? label.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 3) : 'STD'
  const randomNum = Math.floor(100 + Math.random() * 900)
  return `${cleanName}-${cleanLabel}-${randomNum}`
}

// Add Variant Row
const addVariant = () => {
  const count = props.form.variants.length + 1
  const label = `Variant ${count}`
  props.form.variants.push({
    variant_label: label,
    sku: generateSku(props.form.name, label),
    price_override: null,
    stock_qty: 0,
    low_stock_threshold: 10
  })
}

// Remove Variant Row
const removeVariant = (index) => {
  if (props.form.variants.length > 1) {
    props.form.variants.splice(index, 1)
  }
}

// Auto-fill initial SKUs when Product Name is typed
watch(() => props.form.name, (newName) => {
  if (newName && props.form.variants.length > 0) {
    props.form.variants.forEach((variant) => {
      if (!variant.sku) {
        variant.sku = generateSku(newName, variant.variant_label)
      }
    })
  }
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-zinc-800">
      <div class="flex items-center gap-2">
        <Layers class="h-5 w-5 text-slate-500 dark:text-zinc-400" />
        <div>
          <h2 class="text-base font-semibold text-slate-900 dark:text-zinc-100">Item Variants & Stock</h2>
          <p class="text-xs text-slate-500 dark:text-zinc-400">Configure distinct SKUs, prices, and initial quantities.</p>
        </div>
      </div>
      <Button
        type="button"
        variant="outline"
        size="sm"
        @click="addVariant"
        class="gap-1.5 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
      >
        <Plus class="h-4 w-4" />
        <span>Add Variant</span>
      </Button>
    </div>

    <div class="space-y-4">
      <div
        v-for="(variant, index) in form.variants"
        :key="index"
        class="p-4 border border-slate-200 dark:border-zinc-800 rounded-xl bg-slate-50/50 dark:bg-zinc-800/30 space-y-4"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
            Variant #{{ index + 1 }}
          </span>
          <button
            v-if="form.variants.length > 1"
            type="button"
            @click="removeVariant(index)"
            class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 p-1 transition-colors"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Label -->
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1">Variant Name</label>
            <Input
              v-model="variant.variant_label"
              placeholder="e.g., Red / Large"
              class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
            />
            <p v-if="form.errors[`variants.${index}.variant_label`]" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
              Required
            </p>
          </div>

          <!-- SKU Code -->
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1">SKU Code</label>
            <Input
              v-model="variant.sku"
              placeholder="DESK-RED-001"
              class="font-mono text-xs dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
            />
            <p v-if="form.errors[`variants.${index}.sku`]" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
              {{ form.errors[`variants.${index}.sku`] }}
            </p>
          </div>

          <!-- Price Override -->
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1">Price Override ($)</label>
            <Input
              v-model.number="variant.price_override"
              type="number"
              step="0.01"
              min="0"
              placeholder="Optional"
              class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
            />
          </div>

          <!-- Opening Stock -->
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1">Opening Stock</label>
            <Input
              v-model.number="variant.stock_qty"
              type="number"
              min="0"
              class="font-semibold text-emerald-600 dark:text-emerald-400 dark:bg-zinc-800 dark:border-zinc-700"
            />
          </div>

          <!-- Alert Threshold -->
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1">Alert Threshold</label>
            <Input
              v-model.number="variant.low_stock_threshold"
              type="number"
              min="0"
              class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>