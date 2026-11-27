<script setup>
import { ref } from 'vue';
import FilterAccordion from './FilterAccordion.vue';

defineProps({
    total: { type: Number, default: 0 },
    search: { type: String, default: '' },
    category: { type: String, default: '' },
    brand: { type: String, default: '' },
    tag: { type: String, default: '' },
    inStock: { type: Boolean, default: false },
    sort: { type: String, default: 'latest' },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
    activeFilterCount: { type: Number, default: 0 },
    hasActiveFilters: { type: Boolean, default: false },
});

const emit = defineEmits([
    'update:search',
    'select-sort',
    'select-category',
    'select-brand',
    'select-tag',
    'toggle-in-stock',
    'reset',
]);

const isMobileFilterOpen = ref(false);

const openSections = ref({
    sort: true,
    category: true,
    brand: false,
    availability: false,
    tags: false,
});

const toggleSection = (key) => {
    openSections.value[key] = !openSections.value[key];
};

const sortOptions = [
    { value: 'latest', label: 'Newest' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'name_asc', label: 'Alphabetical' },
];
</script>

<template>
    <aside class="w-full lg:w-64 xl:w-72 shrink-0 lg:sticky lg:top-24 space-y-6">
        <div class="flex flex-col space-y-2">
            <span class="text-xs uppercase tracking-[0.35em] text-zinc-400 font-bold leading-none">
                Catalog
            </span>
            <div class="flex items-baseline justify-between gap-3 pt-1">
                <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                    Shop All
                </h2>
                <span class="text-sm text-zinc-400 font-normal">
                    {{ total }} {{ total === 1 ? 'Piece' : 'Pieces' }}
                </span>
            </div>
        </div>

        <div class="lg:hidden flex items-center justify-between">
            <button
                type="button"
                @click="isMobileFilterOpen = !isMobileFilterOpen"
                class="text-sm font-medium text-zinc-900 cursor-pointer"
            >
                Refine
                <span v-if="activeFilterCount > 0" class="text-zinc-400 font-normal ml-1">
                    ({{ activeFilterCount }})
                </span>
            </button>

            <button
                v-if="hasActiveFilters"
                type="button"
                @click="emit('reset')"
                class="text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer"
            >
                Clear All
            </button>
        </div>

        <div :class="['flex-col space-y-6', isMobileFilterOpen ? 'flex' : 'hidden lg:flex']">
            <div class="space-y-2.5">
                <div class="relative">
                    <input
                        :value="search"
                        @input="emit('update:search', $event.target.value)"
                        type="text"
                        placeholder="Search catalog..."
                        class="w-full py-2.5 pl-3.5 pr-14 text-sm border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0"
                    />
                    <button
                        v-if="search"
                        type="button"
                        @click="emit('update:search', '')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer"
                    >
                        Clear
                    </button>
                </div>

                <div v-if="hasActiveFilters" class="hidden lg:flex justify-end">
                    <button
                        type="button"
                        @click="emit('reset')"
                        class="text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer"
                    >
                        Clear All
                    </button>
                </div>
            </div>

            <FilterAccordion title="Sort By" :open="openSections.sort" @toggle="toggleSection('sort')">
                <div class="space-y-2.5">
                    <button
                        v-for="option in sortOptions"
                        :key="option.value"
                        type="button"
                        @click="emit('select-sort', option.value)"
                        class="w-full flex items-center gap-3 text-left group cursor-pointer"
                    >
                        <span
                            :class="[
                                'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                sort === option.value
                                    ? 'border-zinc-900'
                                    : 'border-zinc-300 group-hover:border-zinc-900',
                            ]"
                        >
                            <span v-if="sort === option.value" class="w-2 h-2 rounded-full bg-zinc-900" />
                        </span>
                        <span
                            :class="[
                                'text-sm',
                                sort === option.value
                                    ? 'text-zinc-900 font-medium'
                                    : 'text-zinc-500 group-hover:text-zinc-900',
                            ]"
                        >
                            {{ option.label }}
                        </span>
                    </button>
                </div>
            </FilterAccordion>

            <FilterAccordion
                v-if="categories.length > 0"
                title="Category"
                :open="openSections.category"
                @toggle="toggleSection('category')"
            >
                <div class="space-y-2.5">
                    <button
                        type="button"
                        @click="emit('select-category', '')"
                        class="w-full flex items-center gap-3 text-left group cursor-pointer"
                    >
                        <span
                            :class="[
                                'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                !category ? 'border-zinc-900' : 'border-zinc-300 group-hover:border-zinc-900',
                            ]"
                        >
                            <span v-if="!category" class="w-2 h-2 rounded-full bg-zinc-900" />
                        </span>
                        <span
                            :class="[
                                'text-sm',
                                !category
                                    ? 'text-zinc-900 font-medium'
                                    : 'text-zinc-500 group-hover:text-zinc-900',
                            ]"
                        >
                            All Categories
                        </span>
                    </button>

                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="emit('select-category', cat.slug)"
                        class="w-full flex items-center gap-3 text-left group cursor-pointer"
                    >
                        <span
                            :class="[
                                'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                category === cat.slug
                                    ? 'border-zinc-900'
                                    : 'border-zinc-300 group-hover:border-zinc-900',
                            ]"
                        >
                            <span v-if="category === cat.slug" class="w-2 h-2 rounded-full bg-zinc-900" />
                        </span>
                        <span
                            :class="[
                                'text-sm',
                                category === cat.slug
                                    ? 'text-zinc-900 font-medium'
                                    : 'text-zinc-500 group-hover:text-zinc-900',
                            ]"
                        >
                            {{ cat.name }}
                        </span>
                    </button>
                </div>
            </FilterAccordion>

            <FilterAccordion
                v-if="brands.length > 0"
                title="Brand"
                :open="openSections.brand"
                @toggle="toggleSection('brand')"
            >
                <div class="space-y-2.5 max-h-60 overflow-y-auto">
                    <button
                        type="button"
                        @click="emit('select-brand', '')"
                        class="w-full flex items-center gap-3 text-left group cursor-pointer"
                    >
                        <span
                            :class="[
                                'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                !brand ? 'border-zinc-900' : 'border-zinc-300 group-hover:border-zinc-900',
                            ]"
                        >
                            <span v-if="!brand" class="w-2 h-2 rounded-full bg-zinc-900" />
                        </span>
                        <span
                            :class="[
                                'text-sm',
                                !brand ? 'text-zinc-900 font-medium' : 'text-zinc-500 group-hover:text-zinc-900',
                            ]"
                        >
                            All Brands
                        </span>
                    </button>

                    <button
                        v-for="b in brands"
                        :key="b.id"
                        type="button"
                        @click="emit('select-brand', b.slug)"
                        class="w-full flex items-center gap-3 text-left group cursor-pointer"
                    >
                        <span
                            :class="[
                                'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                brand === b.slug
                                    ? 'border-zinc-900'
                                    : 'border-zinc-300 group-hover:border-zinc-900',
                            ]"
                        >
                            <span v-if="brand === b.slug" class="w-2 h-2 rounded-full bg-zinc-900" />
                        </span>
                        <span
                            :class="[
                                'text-sm',
                                brand === b.slug
                                    ? 'text-zinc-900 font-medium'
                                    : 'text-zinc-500 group-hover:text-zinc-900',
                            ]"
                        >
                            {{ b.name }}
                        </span>
                    </button>
                </div>
            </FilterAccordion>

            <FilterAccordion
                title="Availability"
                :open="openSections.availability"
                @toggle="toggleSection('availability')"
            >
                <label
                    class="inline-flex items-center gap-3 text-sm text-zinc-600 hover:text-zinc-900 cursor-pointer select-none"
                >
                    <input
                        :checked="inStock"
                        type="checkbox"
                        @change="emit('toggle-in-stock', $event.target.checked)"
                        class="w-4 h-4 rounded-none border-zinc-300 text-zinc-900 focus:ring-0"
                    />
                    <span>In stock only</span>
                </label>
            </FilterAccordion>

            <FilterAccordion
                v-if="tags.length > 0"
                title="Collection & Tags"
                :open="openSections.tags"
                @toggle="toggleSection('tags')"
            >
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="t in tags"
                        :key="t.id"
                        type="button"
                        @click="emit('select-tag', t.slug)"
                        :class="[
                            'px-3.5 py-1.5 text-sm border border-solid rounded-none cursor-pointer',
                            tag === t.slug
                                ? 'border-zinc-900 bg-zinc-900 text-white font-medium'
                                : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-900 hover:text-zinc-900',
                        ]"
                    >
                        {{ t.name }}
                    </button>
                </div>
            </FilterAccordion>
        </div>
    </aside>
</template>