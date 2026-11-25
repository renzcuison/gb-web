<script setup>
import { ref, computed, watch } from 'vue';
import { router, useRemember } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    catalog: {
        type: Object,
        default: () => ({ items: [], total: 0, has_more: false }),
    },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            category: '',
            brand: '',
            tag: '',
            in_stock: false,
            sort: 'latest',
        }),
    },
});

const catalogState = useRemember(
    {
        items: [...(props.catalog?.items || [])],
        hasMore: Boolean(props.catalog?.has_more),
        filterKey: JSON.stringify(props.filters || {}),
    },
    'storefront-shop-all'
);

const isLoadingMore = ref(false);
const isMobileFilterOpen = ref(false);
let activeAbortController = null;

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const brand = ref(props.filters.brand || '');
const tag = ref(props.filters.tag || '');
const inStock = ref(Boolean(props.filters.in_stock));
const sort = ref(props.filters.sort || 'latest');

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

watch(
    () => props.catalog,
    (next) => {
        if (activeAbortController) {
            activeAbortController.abort();
            activeAbortController = null;
            isLoadingMore.value = false;
        }
        catalogState.value.items = [...(next?.items || [])];
        catalogState.value.hasMore = Boolean(next?.has_more);
        catalogState.value.filterKey = JSON.stringify(props.filters || {});
    }
);

watch(
    () => props.filters,
    (next) => {
        search.value = next.search || '';
        category.value = next.category || '';
        brand.value = next.brand || '';
        tag.value = next.tag || '';
        inStock.value = Boolean(next.in_stock);
        sort.value = next.sort || 'latest';
    },
    { deep: true }
);

const activeFilterCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (category.value) count++;
    if (brand.value) count++;
    if (tag.value) count++;
    if (inStock.value) count++;
    if (sort.value !== 'latest') count++;
    return count;
});

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const buildFilterParams = () => ({
    search: search.value || undefined,
    category: category.value || undefined,
    brand: brand.value || undefined,
    tag: tag.value || undefined,
    in_stock: inStock.value ? 1 : undefined,
    sort: sort.value !== 'latest' ? sort.value : undefined,
});

const applyFilters = () => {
    router.get(route('storefront.index'), buildFilterParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const loadMore = async () => {
    if (!catalogState.value.hasMore || isLoadingMore.value) return;

    isLoadingMore.value = true;
    activeAbortController = new AbortController();

    try {
        const url = new URL(route('storefront.index'), window.location.origin);
        const params = {
            ...buildFilterParams(),
            offset: catalogState.value.items.length,
        };

        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });

        const response = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: activeAbortController.signal,
        });

        if (!response.ok) return;

        const data = await response.json();
        const existingIds = new Set(catalogState.value.items.map((p) => p.id));
        const freshItems = (data.items || []).filter((p) => !existingIds.has(p.id));

        catalogState.value.items.push(...freshItems);
        catalogState.value.hasMore = Boolean(data.has_more);
    } catch (error) {
        if (error?.name !== 'AbortError') {
            console.error('Failed to load more products:', error);
        }
    } finally {
        activeAbortController = null;
        isLoadingMore.value = false;
    }
};

let timer = null;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(applyFilters, 300);
});

const selectSort = (value) => {
    sort.value = value;
    applyFilters();
};

const selectCategory = (slug) => {
    category.value = category.value === slug ? '' : slug;
    applyFilters();
};

const selectBrand = (slug) => {
    brand.value = brand.value === slug ? '' : slug;
    applyFilters();
};

const selectTag = (slug) => {
    tag.value = tag.value === slug ? '' : slug;
    applyFilters();
};

const resetFilters = () => {
    search.value = '';
    category.value = '';
    brand.value = '';
    tag.value = '';
    inStock.value = false;
    sort.value = 'latest';
    applyFilters();
};
</script>

<template>
    <section id="catalog" class="max-w-full mx-auto px-6 lg:px-10 pt-14 pb-20 bg-white scroll-mt-16">
        <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-10">
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
                            {{ catalog?.total ?? 0 }} {{ (catalog?.total ?? 0) === 1 ? 'Piece' : 'Pieces' }}
                        </span>
                    </div>
                </div>

                <div class="lg:hidden flex items-center justify-between">
                    <button
                        type="button"
                        @click="isMobileFilterOpen = !isMobileFilterOpen"
                        class="text-sm font-medium text-zinc-900 cursor-pointer">
                        Refine
                        <span v-if="activeFilterCount > 0" class="text-zinc-400 font-normal ml-1">
                            ({{ activeFilterCount }})
                        </span>
                    </button>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetFilters"
                        class="text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer">
                        Clear All
                    </button>
                </div>

                <div
                    :class="[
                        'flex-col space-y-6',
                        isMobileFilterOpen ? 'flex' : 'hidden lg:flex',
                    ]">
                    <div class="space-y-2.5">
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search catalog..."
                                class="w-full py-2.5 pl-3.5 pr-14 text-sm border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0"
                            />
                            <button
                                v-if="search"
                                type="button"
                                @click="search = ''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer">
                                Clear
                            </button>
                        </div>

                        <div v-if="hasActiveFilters" class="hidden lg:flex justify-end">
                            <button
                                type="button"
                                @click="resetFilters"
                                class="text-sm text-zinc-400 hover:text-zinc-900 cursor-pointer">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button
                            type="button"
                            @click="toggleSection('sort')"
                            class="w-full flex items-center justify-between text-left cursor-pointer">
                            <span class="text-sm font-medium text-zinc-900">
                                Sort By
                            </span>
                            <svg
                                :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', openSections.sort ? 'rotate-180' : '']"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" stroke-linecap="square" />
                            </svg>
                        </button>

                        <div v-show="openSections.sort" class="space-y-2.5 pt-0.5">
                            <button
                                v-for="option in sortOptions"
                                :key="option.value"
                                type="button"
                                @click="selectSort(option.value)"
                                class="w-full flex items-center gap-3 text-left group cursor-pointer">
                                <span
                                    :class="[
                                        'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                        sort === option.value
                                            ? 'border-zinc-900'
                                            : 'border-zinc-300 group-hover:border-zinc-900',
                                    ]">
                                    <span
                                        v-if="sort === option.value"
                                        class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                </span>
                                <span
                                    :class="[
                                        'text-sm',
                                        sort === option.value
                                            ? 'text-zinc-900 font-medium'
                                            : 'text-zinc-500 group-hover:text-zinc-900',
                                    ]">
                                    {{ option.label }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div v-if="categories.length > 0" class="space-y-3">
                        <button
                            type="button"
                            @click="toggleSection('category')"
                            class="w-full flex items-center justify-between text-left cursor-pointer">
                            <span class="text-sm font-medium text-zinc-900">
                                Category
                            </span>
                            <svg
                                :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', openSections.category ? 'rotate-180' : '']"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" stroke-linecap="square" />
                            </svg>
                        </button>

                        <div v-show="openSections.category" class="space-y-2.5 pt-0.5">
                            <button
                                type="button"
                                @click="selectCategory('')"
                                class="w-full flex items-center gap-3 text-left group cursor-pointer">
                                <span
                                    :class="[
                                        'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                        !category
                                            ? 'border-zinc-900'
                                            : 'border-zinc-300 group-hover:border-zinc-900',
                                    ]">
                                    <span
                                        v-if="!category"
                                        class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                </span>
                                <span
                                    :class="[
                                        'text-sm',
                                        !category
                                            ? 'text-zinc-900 font-medium'
                                            : 'text-zinc-500 group-hover:text-zinc-900',
                                    ]">
                                    All Categories
                                </span>
                            </button>

                            <button
                                v-for="cat in categories"
                                :key="cat.id"
                                type="button"
                                @click="selectCategory(cat.slug)"
                                class="w-full flex items-center gap-3 text-left group cursor-pointer">
                                <span
                                    :class="[
                                        'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                        category === cat.slug
                                            ? 'border-zinc-900'
                                            : 'border-zinc-300 group-hover:border-zinc-900',
                                    ]">
                                    <span
                                        v-if="category === cat.slug"
                                        class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                </span>
                                <span
                                    :class="[
                                        'text-sm',
                                        category === cat.slug
                                            ? 'text-zinc-900 font-medium'
                                            : 'text-zinc-500 group-hover:text-zinc-900',
                                    ]">
                                    {{ cat.name }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div v-if="brands.length > 0" class="space-y-3">
                        <button
                            type="button"
                            @click="toggleSection('brand')"
                            class="w-full flex items-center justify-between text-left cursor-pointer">
                            <span class="text-sm font-medium text-zinc-900">
                                Brand
                            </span>
                            <svg
                                :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', openSections.brand ? 'rotate-180' : '']"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" stroke-linecap="square" />
                            </svg>
                        </button>

                        <div v-show="openSections.brand" class="space-y-2.5 pt-0.5 max-h-60 overflow-y-auto">
                            <button
                                type="button"
                                @click="selectBrand('')"
                                class="w-full flex items-center gap-3 text-left group cursor-pointer">
                                <span
                                    :class="[
                                        'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                        !brand
                                            ? 'border-zinc-900'
                                            : 'border-zinc-300 group-hover:border-zinc-900',
                                    ]">
                                    <span
                                        v-if="!brand"
                                        class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                </span>
                                <span
                                    :class="[
                                        'text-sm',
                                        !brand
                                            ? 'text-zinc-900 font-medium'
                                            : 'text-zinc-500 group-hover:text-zinc-900',
                                    ]">
                                    All Brands
                                </span>
                            </button>

                            <button
                                v-for="b in brands"
                                :key="b.id"
                                type="button"
                                @click="selectBrand(b.slug)"
                                class="w-full flex items-center gap-3 text-left group cursor-pointer">
                                <span
                                    :class="[
                                        'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                        brand === b.slug
                                            ? 'border-zinc-900'
                                            : 'border-zinc-300 group-hover:border-zinc-900',
                                    ]">
                                    <span
                                        v-if="brand === b.slug"
                                        class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                </span>
                                <span
                                    :class="[
                                        'text-sm',
                                        brand === b.slug
                                            ? 'text-zinc-900 font-medium'
                                            : 'text-zinc-500 group-hover:text-zinc-900',
                                    ]">
                                    {{ b.name }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button
                            type="button"
                            @click="toggleSection('availability')"
                            class="w-full flex items-center justify-between text-left cursor-pointer">
                            <span class="text-sm font-medium text-zinc-900">
                                Availability
                            </span>
                            <svg
                                :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', openSections.availability ? 'rotate-180' : '']"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" stroke-linecap="square" />
                            </svg>
                        </button>

                        <div v-show="openSections.availability" class="pt-0.5">
                            <label class="inline-flex items-center gap-3 text-sm text-zinc-600 hover:text-zinc-900 cursor-pointer select-none">
                                <input
                                    v-model="inStock"
                                    type="checkbox"
                                    @change="applyFilters"
                                    class="w-4 h-4 rounded-none border-zinc-300 text-zinc-900 focus:ring-0"
                                />
                                <span>In stock only</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="tags.length > 0" class="space-y-3">
                        <button
                            type="button"
                            @click="toggleSection('tags')"
                            class="w-full flex items-center justify-between text-left cursor-pointer">
                            <span class="text-sm font-medium text-zinc-900">
                                Collection &amp; Tags
                            </span>
                            <svg
                                :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', openSections.tags ? 'rotate-180' : '']"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" stroke-linecap="square" />
                            </svg>
                        </button>

                        <div v-show="openSections.tags" class="pt-0.5 flex flex-wrap gap-2">
                            <button
                                v-for="t in tags"
                                :key="t.id"
                                type="button"
                                @click="selectTag(t.slug)"
                                :class="[
                                    'px-3.5 py-1.5 text-sm border border-solid rounded-none cursor-pointer',
                                    tag === t.slug
                                        ? 'border-zinc-900 bg-zinc-900 text-white font-medium'
                                        : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-900 hover:text-zinc-900',
                                ]">
                                {{ t.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex-1 w-full min-w-0">
                <div
                    v-if="catalogState.items.length > 0"
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-1 gap-y-10 lg:gap-x-1.5 lg:gap-y-12">
                    <ProductCard
                        v-for="product in catalogState.items"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <div v-else class="py-24 text-center space-y-3">
                    <p class="text-sm text-zinc-500 font-normal">
                        No products match your current selection.
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetFilters"
                        class="text-sm text-zinc-900 font-medium hover:text-zinc-500 cursor-pointer">
                        Clear all filters
                    </button>
                </div>

                <div
                    v-if="catalogState.items.length > 0"
                    class="flex flex-col items-center justify-center pt-12 space-y-4">
                    <span class="text-xs text-zinc-400">
                        Showing {{ catalogState.items.length }} of {{ catalog?.total ?? catalogState.items.length }}
                    </span>

                    <button
                        v-if="catalogState.hasMore"
                        type="button"
                        :disabled="isLoadingMore"
                        @click="loadMore"
                        class="px-8 py-3.5 text-xs uppercase tracking-[0.25em] font-medium border border-solid border-zinc-900 rounded-none bg-white text-zinc-900 hover:bg-zinc-900 hover:text-white disabled:opacity-50 cursor-pointer">
                        {{ isLoadingMore ? 'Loading...' : 'Show more' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>