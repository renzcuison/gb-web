<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    isActive: Boolean,
    item: Object,
    isVisible: Boolean,
});

const emit = defineEmits(['close']);
const page = usePage();

const menuData = computed(() => page.props.storefront_menu || { categories: [], tags: [], featured_products: [] });

const arrivalProducts = computed(() => (menuData.value.featured_products || []).slice(0, 4));

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
</script>

<template>
    <Transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-[1200px]" leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 max-h-[1200px]" leave-to-class="opacity-0 max-h-0">
        <div v-if="isActive" class="mt-6 flex flex-col space-y-6 overflow-hidden">
            <div v-if="item.slug === 'new-arrivals' && arrivalProducts.length > 0" class="relative w-screen -ml-8 pb-2">
                <div class="flex overflow-x-auto gap-4 px-8 snap-x snap-mandatory scroll-pl-8 no-scrollbar transition-all duration-1000 ease-out"
                    :style="{
                        transitionDelay: '200ms',
                        transform: isVisible ? 'translateY(0)' : 'translateY(20px)',
                        opacity: isVisible ? 1 : 0
                    }">
                    <Link
                        v-for="product in arrivalProducts"
                        :key="product.id"
                        :href="route('products.show', product.slug)"
                        @click="emit('close')"
                        class="flex-none w-[72vw] snap-start flex flex-col space-y-3">

                        <div class="aspect-[16/9] w-full bg-zinc-100 overflow-hidden">
                            <img
                                v-if="product.media?.[0]?.url"
                                :src="product.media[0].url"
                                :alt="product.name"
                                class="w-full h-full object-cover transition-all duration-500"
                            />
                        </div>

                        <div class="flex flex-col space-y-0.5">
                            <span class="text-[14px] font-normal text-zinc-900 tracking-tight leading-snug">
                                {{ product.name }}
                            </span>
                            <p v-if="product.description" class="text-[13px] text-zinc-500 font-normal leading-relaxed line-clamp-1">
                                {{ product.description }}
                            </p>
                            <span class="text-[13px] text-zinc-900 font-bold mt-1 price-tag">
                                ₱{{ Number(product.price ?? 0).toLocaleString('en-PH') }}
                            </span>
                        </div>
                    </Link>
                    <div class="flex-none w-4"></div>
                </div>
            </div>

            <Link
                v-for="(child, index) in item.children"
                :key="child"
                :href="resolveChildHref(child)"
                @click="emit('close')"
                class="text-sm font-normal text-zinc-400 transition-all duration-1000 ease-out hover:text-black"
                :style="{
                    transitionDelay: `${(index * 50) + 300}ms`,
                    transform: isVisible ? 'translateY(0)' : 'translateY(15px)',
                    opacity: isVisible ? 1 : 0
                }">
                {{ child }}
            </Link>
        </div>
    </Transition>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>