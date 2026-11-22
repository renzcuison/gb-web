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
let activeAbortController = null;

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const brand = ref(props.filters.brand || '');
const tag = ref(props.filters.tag || '');
const inStock = ref(Boolean(props.filters.in_stock));
const sort = ref(props.filters.sort || 'latest');

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

const hasActiveFilters = computed(
    () =>
        Boolean(search.value) ||
        Boolean(category.value) ||
        Boolean(brand.value) ||
        Boolean(tag.value) ||
        inStock.value ||
        sort.value !== 'latest'
);

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

const selectCategory = (slug) => {
    category.value = category.value === slug ? '' : slug;
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
    <section id="catalog" class="max-w-full mx-auto px-6 lg:px-10 pt-14 pb-16 bg-white scroll-mt-16">
        <div class="flex flex-col space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="flex flex-col space-y-[18px]">
                    <span class="text-[12px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-none">
                        Catalog
                    </span>
                    <div class="flex items-baseline space-x-6">
                        <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                            Shop All
                        </h2>
                        <span class="text-[13px] text-zinc-400 font-normal">
                            ({{ catalog?.total ?? 0 }})
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Filter catalog..."
                            class="w-full py-2.5 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-zinc-400 hover:text-zinc-900">
                            Clear
                        </button>
                    </div>

                    <select
                        v-model="brand"
                        @change="applyFilters"
                        aria-label="Filter by brand"
                        class="py-2.5 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-700 focus:border-zinc-900 focus:outline-none focus:ring-0">
                        <option value="">All Brands</option>
                        <option v-for="b in brands" :key="b.id" :value="b.slug">
                            {{ b.name }}
                        </option>
                    </select>

                    <select
                        v-model="sort"
                        @change="applyFilters"
                        aria-label="Sort products"
                        class="py-2.5 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-700 focus:border-zinc-900 focus:outline-none focus:ring-0">
                        <option value="latest">Newest</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Alphabetical</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-4">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <button
                        type="button"
                        @click="selectCategory('')"
                        :class="[
                            'text-xs tracking-normal pb-1 transition-colors',
                            !category
                                ? 'text-zinc-900 font-medium border-b border-black'
                                : 'text-zinc-500 hover:text-zinc-900',
                        ]">
                        All
                    </button>

                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="selectCategory(cat.slug)"
                        :class="[
                            'text-xs tracking-normal pb-1 transition-colors',
                            category === cat.slug
                                ? 'text-zinc-900 font-medium border-b border-black'
                                : 'text-zinc-500 hover:text-zinc-900',
                        ]">
                        {{ cat.name }}
                    </button>
                </div>

                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-xs text-zinc-600 cursor-pointer select-none">
                        <input
                            v-model="inStock"
                            type="checkbox"
                            @change="applyFilters"
                            class="rounded-none border-zinc-300 text-zinc-900 focus:ring-0"
                        />
                        <span>In stock only</span>
                    </label>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetFilters"
                        class="text-xs text-zinc-900 border-b border-black pb-0.5">
                        Reset
                    </button>
                </div>
            </div>

            <div v-if="tags.length > 0" class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                    Tags:
                </span>
                <button
                    v-for="t in tags"
                    :key="t.id"
                    type="button"
                    @click="selectTag(t.slug)"
                    :class="[
                        'text-xs pb-0.5 transition-colors',
                        tag === t.slug
                            ? 'text-zinc-900 font-medium border-b border-black'
                            : 'text-zinc-400 hover:text-zinc-900',
                    ]">
                    {{ t.name }}
                </button>
            </div>

            <div
                v-if="catalogState.items.length > 0"
                class="grid grid-cols-2 lg:grid-cols-4 gap-x-1 gap-y-10 lg:gap-x-1.5 lg:gap-y-12">
                <ProductCard
                    v-for="product in catalogState.items"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <div v-else class="py-16 text-center space-y-3">
                <p class="text-sm text-zinc-500 font-normal">
                    No products match your current selection.
                </p>
                <button
                    v-if="hasActiveFilters"
                    type="button"
                    @click="resetFilters"
                    class="text-xs text-zinc-900 font-medium border-b border-black pb-0.5">
                    View all products
                </button>
            </div>

            <div
                v-if="catalogState.items.length > 0"
                class="flex flex-col items-center justify-center pt-6 space-y-4">
                <span class="text-xs text-zinc-400">
                    Showing {{ catalogState.items.length }} of {{ catalog?.total ?? catalogState.items.length }}
                </span>

                <button
                    v-if="catalogState.hasMore"
                    type="button"
                    :disabled="isLoadingMore"
                    @click="loadMore"
                    class="px-8 py-3.5 text-xs uppercase tracking-[0.25em] font-medium border border-solid border-zinc-900 rounded-none bg-white text-zinc-900 hover:bg-zinc-900 hover:text-white disabled:opacity-50 transition-colors">
                    {{ isLoadingMore ? 'Loading...' : 'Show more' }}
                </button>
            </div>
        </div>
    </section>
</template>