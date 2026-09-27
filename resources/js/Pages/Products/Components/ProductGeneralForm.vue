<script setup>
import { Package } from 'lucide-vue-next'
import { Input } from '@/Components/ui/input'

defineProps({
  form: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  },
  brands: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
    <div class="flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-zinc-800">
      <Package class="h-5 w-5 text-slate-500 dark:text-zinc-400" />
      <h2 class="text-base font-semibold text-slate-900 dark:text-zinc-100">General Information</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Product Name -->
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Item Name *
        </label>
        <Input
          v-model="form.name"
          type="text"
          placeholder="e.g., Ergonomic Executive Desk"
          class="w-full dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
        />
        <p v-if="form.errors.name" class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ form.errors.name }}</p>
      </div>

      <!-- Category Select -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Category *
        </label>
        <select
          v-model="form.category_id"
          class="flex h-10 w-full rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-slate-950 dark:focus:ring-zinc-400"
        >
          <option value="" disabled>Select Category</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <p v-if="form.errors.category_id" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
          {{ form.errors.category_id }}
        </p>
      </div>

      <!-- Brand Select -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Brand *
        </label>
        <select
          v-model="form.brand_id"
          class="flex h-10 w-full rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-slate-950 dark:focus:ring-zinc-400"
        >
          <option value="" disabled>Select Brand</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.id">
            {{ brand.name }}
          </option>
        </select>
        <p v-if="form.errors.brand_id" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
          {{ form.errors.brand_id }}
        </p>
      </div>

      <!-- Base Selling Price -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Base Selling Price ($) *
        </label>
        <Input
          v-model.number="form.price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          class="w-full font-semibold dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
        />
        <p v-if="form.errors.price" class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ form.errors.price }}</p>
      </div>

      <!-- Base Cost Price -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Cost Price ($)
        </label>
        <Input
          v-model.number="form.cost_price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          class="w-full font-semibold dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
        />
        <p v-if="form.errors.cost_price" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
          {{ form.errors.cost_price }}
        </p>
      </div>

      <!-- Description -->
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Description
        </label>
        <textarea
          v-model="form.description"
          rows="3"
          placeholder="Provide details regarding materials, dimensions, or features..."
          class="flex min-h-[80px] w-full rounded-md border border-slate-200 dark:border-zinc-700 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-slate-950 dark:focus:ring-zinc-400"
        />
        <p v-if="form.errors.description" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
          {{ form.errors.description }}
        </p>
      </div>
    </div>
  </div>
</template>