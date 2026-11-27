import { ref, computed, watch } from 'vue';
import { router, useRemember } from '@inertiajs/vue3';

export function useShopAllCatalog(props) {
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

    const toggleInStock = (val) => {
        inStock.value = Boolean(val);
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

    return {
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
    };
}