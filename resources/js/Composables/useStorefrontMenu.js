import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const VERTICAL_FALLBACKS = {
    'apparel-footwear': [
        { label: 'Shoes & Sneakers', search: 'Shoes' },
        { label: 'Caps & Headwear', search: 'Cap' },
        { label: 'Clothing', search: 'Shirt' },
    ],
    'bags-accessories': [
        { label: 'Bags & Totes', search: 'Bag' },
        { label: 'Wallets', search: 'Wallet' },
        { label: 'Watches', search: 'Watch' },
    ],
    'beauty-fragrance': [
        { label: 'Perfumes & Mists', search: 'Perfume' },
        { label: 'Lotions & Body Care', search: 'Lotion' },
        { label: 'Skincare', search: 'Skincare' },
    ],
    'home-kitchen': [
        { label: 'Cookware & Frying Pans', search: 'Frying Pan' },
        { label: 'Dinnerware & Plates', search: 'Plate' },
        { label: 'Kitchen & Home', search: 'Kitchen' },
    ],
};

export function useStorefrontMenu() {
    const page = usePage();

    const menuData = computed(() => ({
        categories: page.props.storefront_menu?.categories || [],
        brands: page.props.storefront_menu?.brands || [],
        tags: page.props.storefront_menu?.tags || [],
        verticals: page.props.storefront_menu?.verticals || {},
        featured_products: page.props.storefront_menu?.featured_products || [],
    }));

    const catalogHref = (params = {}) => {
        const cleanParams = Object.fromEntries(
            Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== '')
        );
        return `${route('storefront.index', cleanParams)}#catalog`;
    };

    const buildMenuColumns = (navItem) => {
        const slug = navItem.slug;

        if (slug === 'new-arrivals') {
            const discoverLinks = [
                { label: 'Just Dropped', href: catalogHref({ sort: 'latest' }) },
                { label: 'Ready to Ship (In Stock)', href: catalogHref({ in_stock: 1 }) },
                { label: 'Shop All Imports', href: catalogHref({}) },
                { label: 'Price: Low to High', href: catalogHref({ sort: 'price_asc' }) },
            ];

            const topCategories = menuData.value.categories.slice(0, 5).map((cat) => ({
                label: cat.name,
                href: catalogHref({ category: cat.slug, sort: 'latest' }),
            }));

            const topBrands = menuData.value.brands.slice(0, 5).map((brand) => ({
                label: brand.name,
                href: catalogHref({ brand: brand.slug, sort: 'latest' }),
            }));

            const columns = [{ title: 'Discover', links: discoverLinks }];
            if (topCategories.length > 0) {
                columns.push({ title: 'Departments', links: topCategories });
            }
            if (topBrands.length > 0) {
                columns.push({ title: 'Top Brands', links: topBrands });
            }

            return columns;
        }

        const verticalData = menuData.value.verticals[slug] || { categories: [], brands: [] };
        const matchedCategories = verticalData.categories || [];
        const matchedBrands = verticalData.brands || [];

        const categoryLinks =
            matchedCategories.length > 0
                ? matchedCategories.slice(0, 6).map((cat) => ({
                      label: cat.name,
                      href: catalogHref({ category: cat.slug }),
                  }))
                : (VERTICAL_FALLBACKS[slug] || []).map((item) => ({
                      label: item.label,
                      href: catalogHref({ search: item.search }),
                  }));

        const columns = [{ title: 'Categories', links: categoryLinks }];

        if (matchedBrands.length > 0) {
            columns.push({
                title: 'Brands',
                links: matchedBrands.map((brand) => ({
                    label: brand.name,
                    href: catalogHref({ brand: brand.slug }),
                })),
            });
        } else if (menuData.value.tags.length > 0) {
            columns.push({
                title: 'Collections',
                links: menuData.value.tags.slice(0, 4).map((tag) => ({
                    label: tag.name,
                    href: catalogHref({ tag: tag.slug }),
                })),
            });
        }

        return columns;
    };

    const getShowcaseSlots = (navItem) => {
        const labels = ['Featured', 'Trending', 'Collections'];
        const allProducts = menuData.value.featured_products;

        if (navItem.slug === 'new-arrivals') {
            return labels
                .map((label, idx) => ({ label, product: allProducts[idx] || null }))
                .filter((slot) => slot.product !== null);
        }

        const verticalCategoryIds = new Set(
            (menuData.value.verticals[navItem.slug]?.categories || []).map((c) => c.id)
        );

        const sectionMatches = allProducts.filter((p) => verticalCategoryIds.has(p.category_id));

        const pool = [...sectionMatches];
        for (const p of allProducts) {
            if (pool.length >= 3) break;
            if (!pool.some((existing) => existing.id === p.id)) {
                pool.push(p);
            }
        }

        return labels
            .map((label, idx) => ({
                label,
                product: pool[idx] || null,
            }))
            .filter((slot) => slot.product !== null);
    };

    return {
        menuData,
        buildMenuColumns,
        getShowcaseSlots,
    };
}