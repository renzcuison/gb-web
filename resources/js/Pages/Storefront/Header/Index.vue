<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';
import Logo from '@/Components/Logo.vue';
import Hamburger from '@/Pages/Storefront/Header/Mobile/Hamburger.vue';
import MobileMenu from './Mobile/Index.vue';
import MegaMenu from './MegaMenu.vue';

const isMobileMenuOpen = ref(false);

const navItems = [
    { name: 'New Arrivals', slug: 'new-arrivals', children: ['This Week', 'Sustainability', 'Collaborations'] },
    { name: 'Women', slug: 'women', children: ['New Arrivals', 'Shoes', 'Clothing'] },
    { name: 'Men', slug: 'men', children: ['New Arrivals', 'Shoes', 'Clothing'] },
    { name: 'Accessories', slug: 'accessories', children: ['Bags', 'Hats', 'Watches'] },
];
</script>

<template>
    <header class="sticky top-0 bg-white z-50 border-b border-zinc-100">
        <div class="max-w-full mx-auto px-6 lg:px-10">
            <div class="grid grid-cols-2 md:grid-cols-3 items-center h-16">
                <div class="flex justify-start items-center">
                    <Logo />
                </div>

                <nav class="hidden md:flex justify-center items-center space-x-8 h-full">
                    <div v-for="item in navItems" :key="item.slug" class="group h-full flex items-center">
                        <button
                            class="text-sm font-normal tracking-normal hover:text-black transition-colors duration-300 h-full px-2">
                            {{ item.name }}
                        </button>
                        <MegaMenu :item="item" />
                    </div>
                </nav>

                <div class="flex items-center justify-end">
                    <div class="flex items-center space-x-5 md:space-x-6">
                        <div class="hidden md:block">
                            <SearchBar />
                        </div>
                        <Link :href="route('login')"
                            class="hidden md:block hover:text-black transition-colors duration-200">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="8.5" r="3" stroke="currentColor" stroke-width="1" />
                                <path d="M17.5 21C17.5 18.5147 15.0376 16.5 12 16.5C8.96243 16.5 6.5 18.5147 6.5 21"
                                    stroke="currentColor" stroke-width="1" stroke-linecap="square" />
                            </svg>
                        </Link>
                    </div>
                    <Hamburger :is-open="isMobileMenuOpen" @toggle="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden ml-5 -mr-2" />
                </div>
            </div>
        </div>
    </header>

    <MobileMenu v-if="isMobileMenuOpen" :is-open="isMobileMenuOpen" :nav-items="navItems"
        @close="isMobileMenuOpen = false" />
</template>