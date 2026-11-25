<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';

const curatedSuggestions = [
    { label: 'Fragrance Mists', query: 'Mist' },
    { label: 'Crossbody Bags', query: 'Crossbody' },
    { label: 'Wallets & Cardholders', query: 'Wallet' },
    { label: 'Classic Polos', query: 'Polo' },
    { label: 'Body Care', query: 'Lotion' },
];

const trendingProducts = ref([]);

const fetchTrendingProducts = async () => {
    try {
        const response = await fetch(route('storefront.search.suggestions'), {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const data = await response.json();
            trendingProducts.value = (data.trending_products || []).slice(0, 5);
        }
    } catch {
    }
};

onMounted(() => {
    fetchTrendingProducts();
});

const filterCatalog = (query) => {
    router.get(
        route('storefront.index'),
        { search: query },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
            },
        }
    );
};

const goToProduct = (slug) => {
    router.visit(route('products.show', slug));
};
</script>

<template>
    <section
        class="relative w-full py-10 sm:py-12 overflow-hidden min-h-[500px] md:min-h-[550px] flex flex-col justify-between border-y border-zinc-900 px-5 sm:px-6 lg:px-10 bg-zinc-950">

        <div class="absolute inset-0 z-0">
            <img src="/images/brands/banner.jpg" alt="GreatBuy Originals Lifestyle"
                class="w-full h-full object-cover object-center brightness-[0.75] contrast-125" />
            <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/20 to-transparent"></div>
        </div>

        <div class="relative z-10 flex flex-col space-y-5 sm:space-y-6 w-full self-start">
            <div class="text-left">
                <h2 class="text-lg sm:text-2xl uppercase tracking-wider text-white font-bold leading-none">
                    SEARCH AND DISCOVER
                </h2>
            </div>

            <div class="flex flex-col space-y-4 w-full md:max-w-[550px]">
                <SearchBar
                    dark
                    :show-recommendations="false"
                    class="!w-full !md:w-full shadow-2xl shadow-black/80"
                    placeholder="Search 'BBW Mist', 'Bag', or 'Polo'..."
                />

                <div class="flex flex-wrap gap-x-3.5 sm:gap-x-5 gap-y-2 items-center">
                    <span
                        class="text-[8px] sm:text-[10px] uppercase tracking-widest text-white/40 font-bold">Suggestions:</span>
                    <button
                        v-for="item in curatedSuggestions"
                        :key="item.label"
                        type="button"
                        @click="filterCatalog(item.query)"
                        class="text-[10px] sm:text-[11px] text-white font-medium hover:text-zinc-400 transition-colors duration-300 cursor-pointer">
                        {{ item.label }}
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="trendingProducts.length > 0"
            class="relative z-10 flex flex-col space-y-3 sm:space-y-4 w-full self-end text-left md:text-right mt-10 md:mt-0">
            <div>
                <h2 class="text-lg sm:text-2xl uppercase tracking-wider text-white font-bold leading-none">
                    TRENDING NOW
                </h2>
            </div>

            <div class="flex flex-nowrap overflow-x-auto no-scrollbar gap-x-5 sm:gap-x-8 justify-start md:justify-end whitespace-nowrap">
                <button
                    v-for="product in trendingProducts"
                    :key="product.id"
                    type="button"
                    @click="goToProduct(product.slug)"
                    class="animated-link text-xs sm:text-sm text-white font-medium relative py-1 cursor-pointer shrink-0">
                    {{ product.name }}
                </button>
            </div>
        </div>

    </section>
</template>

<style scoped>
.animated-link::after {
    content: '';
    position: absolute;
    width: 100%;
    transform: scaleX(0);
    height: 1px;
    bottom: 0;
    left: 0;
    background-color: white;
    transform-origin: bottom right;
    transition: transform 0.4s cubic-bezier(0.86, 0, 0.07, 1);
}

.animated-link:hover::after {
    transform: scaleX(1);
    transform-origin: bottom left;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>