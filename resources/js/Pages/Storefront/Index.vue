<script setup>
import { onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AnnouncementBar from '@/Pages/Storefront/AnnouncementBar.vue';
import Header from '@/Pages/Storefront/Header/Index.vue';
import Hero from '@/Pages/Storefront/Hero.vue';
import NewArrivals from '@/Pages/Storefront/NewArrivals.vue';
import DiscoveryBanner from '@/Pages/Storefront/Partials/DiscoveryBanner.vue';
import ShopAll from '@/Pages/Storefront/ShopAll.vue';
import Footer from '@/Pages/Storefront/Footer.vue';

const props = defineProps({
    catalog: {
        type: Object,
        default: () => ({ items: [], total: 0, has_more: false }),
    },
    newArrivals: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const shouldScrollToCatalog = (f) =>
    window.location.hash === '#catalog' ||
    Boolean(f?.search || f?.category || f?.brand || f?.tag || f?.in_stock || (f?.sort && f.sort !== 'latest'));

onMounted(() => {
    if (shouldScrollToCatalog(props.filters)) {
        requestAnimationFrame(() => {
            document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
        });
    }
});

watch(
    () => props.filters,
    (next) => {
        if (shouldScrollToCatalog(next)) {
            document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
        }
    },
    { deep: true }
);
</script>

<template>
    <Head title="Storefront" />

    <div class="min-h-screen bg-white flex flex-col">
        <AnnouncementBar />

        <Header />

        <main class="flex-1">
            <Hero />

            <NewArrivals :items="newArrivals" />

            <DiscoveryBanner :categories="categories" :tags="tags" />

            <ShopAll
                :catalog="catalog"
                :categories="categories"
                :brands="brands"
                :tags="tags"
                :filters="filters"
            />
        </main>

        <Footer :categories="categories" />
    </div>
</template>