<script setup>
import { Head } from '@inertiajs/vue3';
import AnnouncementBar from '@/Pages/Storefront/AnnouncementBar.vue';
import Header from '@/Pages/Storefront/Header/Index.vue';
import Hero from '@/Pages/Storefront/Hero.vue';
import onDevelopment from '@/Pages/Storefront/onDevelopment.vue';
import NewArrivals from '@/Pages/Storefront/NewArrivals.vue';

defineProps({
    products: Object
});
</script>

<template>

    <Head title="Storefront" />

    <div class="min-h-screen bg-white">
        <AnnouncementBar />

        <Header />

        <main>
            <Hero />

            <NewArrivals />

            <onDevelopment />
        </main>
    </div>
</template>