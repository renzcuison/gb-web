<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['navigate']);
const page = usePage();

const menuData = computed(() => page.props.storefront_menu || { categories: [], tags: [], featured_products: [] });

const resolveChildHref = (childLabel) => {
    const normalizedChild = childLabel.toLowerCase();
    const parentKeywords = props.item.slug === 'kids' ? ['kids', 'kid'] : [props.item.slug];

    const parentCategory = menuData.value.categories?.find((c) =>
        parentKeywords.includes(c.slug?.toLowerCase()) || parentKeywords.includes(c.name?.toLowerCase())
    );
    const parentTag = menuData.value.tags?.find((t) =>
        parentKeywords.includes(t.slug?.toLowerCase()) || parentKeywords.includes(t.name?.toLowerCase())
    );

    if (normalizedChild === 'new arrivals' || normalizedChild === 'this week') {
        return route('storefront.index', {
            sort: 'latest',
            category: parentCategory?.slug || undefined,
            tag: !parentCategory && parentTag ? parentTag.slug : undefined,
        });
    }

    const childCategory = menuData.value.categories?.find(
        (c) => c.slug?.toLowerCase() === normalizedChild || c.name?.toLowerCase() === normalizedChild
    );
    const childTag = menuData.value.tags?.find(
        (t) => t.slug?.toLowerCase() === normalizedChild || t.name?.toLowerCase() === normalizedChild
    );

    if (childCategory) {
        return route('storefront.index', {
            category: childCategory.slug,
            tag: parentTag?.slug || undefined,
        });
    }

    if (childTag) {
        return route('storefront.index', {
            tag: childTag.slug,
            category: parentCategory?.slug || undefined,
        });
    }

    return route('storefront.index', {
        search: childLabel,
        category: parentCategory?.slug || undefined,
    });
};

const showcaseSlots = computed(() => {
    const labels = ['Featured', 'Trending', 'Collections'];
    const allProducts = menuData.value.featured_products || [];
    const keywords = props.item.slug === 'kids' ? ['kids', 'kid'] : [props.item.slug];

    const sectionMatches = props.item.slug === 'new-arrivals'
        ? allProducts
        : allProducts.filter((p) => {
            const cat = p.category?.slug?.toLowerCase() || '';
            const tags = (p.tags || []).map((t) => t.slug?.toLowerCase());
            const text = `${p.name || ''} ${p.description || ''}`.toLowerCase();
            return keywords.some((kw) => cat.includes(kw) || tags.includes(kw) || text.includes(kw));
        });

    const pool = [...sectionMatches];
    for (const p of allProducts) {
        if (pool.length >= 3) break;
        if (!pool.some((existing) => existing.id === p.id)) {
            pool.push(p);
        }
    }

    return labels.map((label, idx) => ({
        label,
        product: pool[idx] || null,
    })).filter((slot) => slot.product !== null);
});
</script>

<template>
    <div class="absolute top-full left-0 w-full">
        <div class="fixed inset-0 bg-black/[0.01] backdrop-blur-md pointer-events-none -z-10"
            style="clip-path: inset(104px 0 0 0);"></div>

        <div class="bg-white border-b border-zinc-100 shadow-sm relative z-50 w-full">
            <div class="max-w-full mx-auto px-6 lg:px-10 py-12">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col space-y-5 text-left">
                        <h3 class="text-[10px] uppercase tracking-widest text-zinc-400 font-semibold">Categories</h3>
                        <div class="flex flex-col space-y-3.5">
                            <Link
                                v-for="child in item.children"
                                :key="child"
                                :href="resolveChildHref(child)"
                                @click="emit('navigate')"
                                class="text-sm font-normal text-zinc-600 hover:text-black transition-colors w-fit">
                                {{ child }}
                            </Link>
                        </div>
                    </div>

                    <div v-if="showcaseSlots.length > 0" class="flex gap-10">
                        <div
                            v-for="slot in showcaseSlots"
                            :key="slot.label"
                            @click="emit('navigate')"
                            class="flex flex-col space-y-4 w-[200px]">
                            <h3 class="text-[10px] uppercase tracking-widest text-zinc-400 font-semibold">
                                {{ slot.label }}
                            </h3>
                            <ProductCard :product="slot.product" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>