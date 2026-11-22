<script setup>
import { Package, Check } from 'lucide-vue-next'
import { Input } from '@/Components/ui/input'

const props = defineProps({
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
  },
  tags: {
    type: Array,
    default: () => []
  }
})

const toggleTag = (tagId) => {
  const index = props.form.tags.indexOf(tagId)
  if (index === -1) {
    props.form.tags.push(tagId)
  } else {
    props.form.tags.splice(index, 1)
  }
}
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-zinc-800">
      <div class="flex items-center gap-2">
        <Package class="h-5 w-5 text-slate-500 dark:text-zinc-400" />
        <h2 class="text-base font-semibold text-slate-900 dark:text-zinc-100">General Information</h2>
      </div>

      <label class="inline-flex items-center gap-2 cursor-pointer select-none">
        <input
          type="checkbox"
          v-model="form.is_active"
          class="rounded border-slate-300 dark:border-zinc-700 text-slate-900 focus:ring-slate-900 dark:bg-zinc-800"
        />
        <span class="text-xs font-medium text-slate-700 dark:text-zinc-300">
          Active on Storefront
        </span>
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Base Selling Price (PHP) *
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

      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Cost Price (PHP)
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

      <div v-if="tags.length > 0" class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mb-2">
          Tags
        </label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tag in tags"
            :key="tag.id"
            type="button"
            @click="toggleTag(tag.id)"
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-all"
            :class="form.tags.includes(tag.id)
              ? 'ring-2 ring-offset-1 ring-slate-900 dark:ring-zinc-100 dark:ring-offset-zinc-900'
              : 'opacity-70 hover:opacity-100'"
            :style="{
              backgroundColor: `${tag.color_code || '#64748b'}18`,
              borderColor: `${tag.color_code || '#64748b'}50`,
              color: tag.color_code || '#64748b'
            }"
          >
            <Check v-if="form.tags.includes(tag.id)" class="h-3 w-3 shrink-0" />
            <span>{{ tag.name }}</span>
          </button>
        </div>
        <p v-if="form.errors.tags" class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ form.errors.tags }}</p>
      </div>

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