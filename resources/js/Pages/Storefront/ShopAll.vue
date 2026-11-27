<script setup>
import { useShopAllCatalog } from './ShopAll/useShopAllCatalog';
import FilterSidebar from './ShopAll/FilterSidebar.vue';
import ProductGrid from './ShopAll/ProductGrid.vue';

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

const {
    catalogState,
    isLoadingMore,
    search,
    category,
    brand,
    tag,
    inStock,
    sort,
    activeFilterCount,
    hasActiveFilters,
    selectSort,
    selectCategory,
    selectBrand,
    selectTag,
    toggleInStock,
    resetFilters,
    loadMore,
} = useShopAllCatalog(props);
</script>

<template>
    <section id="catalog" class="max-w-full mx-auto px-6 lg:px-10 pt-14 pb-20 bg-white scroll-mt-16">
        <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-10">
            <FilterSidebar
                :total="catalog?.total ?? 0"
                :search="search"
                :category="category"
                :brand="brand"
                :tag="tag"
                :in-stock="inStock"
                :sort="sort"
                :categories="categories"
                :brands="brands"
                :tags="tags"
                :active-filter-count="activeFilterCount"
                :has-active-filters="hasActiveFilters"
                @update:search="search = $event"
                @select-sort="selectSort"
                @select-category="selectCategory"
                @select-brand="selectBrand"
                @select-tag="selectTag"
                @toggle-in-stock="toggleInStock"
                @reset="resetFilters"
            />

            <ProductGrid
                :items="catalogState.items"
                :total="catalog?.total ?? catalogState.items.length"
                :has-more="catalogState.hasMore"
                :is-loading-more="isLoadingMore"
                :has-active-filters="hasActiveFilters"
                @load-more="loadMore"
                @reset="resetFilters"
            />
        </div>
    </section>
</template>