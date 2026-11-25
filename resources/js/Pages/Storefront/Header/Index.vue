<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';
import Logo from '@/Components/Logo.vue';
import Hamburger from '@/Pages/Storefront/Header/Mobile/Hamburger.vue';
import MobileMenu from './Mobile/Index.vue';
import MegaMenu from './MegaMenu.vue';

const headerRef = ref(null);
const isMobileMenuOpen = ref(false);
const activeMenuSlug = ref(null);

const navItems = [
    { name: 'New Arrivals', slug: 'new-arrivals' },
    { name: 'Apparel', slug: 'apparel-footwear' },
    { name: 'Accessories', slug: 'bags-accessories' },
    { name: 'Beauty', slug: 'beauty-fragrance' },
    { name: 'Home', slug: 'home-kitchen' },
];

const closeMegaMenu = () => {
    activeMenuSlug.value = null;
};

const handleClickOutside = (event) => {
    if (headerRef.value && !headerRef.value.contains(event.target)) {
        closeMegaMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <header ref="headerRef" class="sticky top-0 bg-white text-zinc-900 z-50 transition-all duration-300">
        <div class="max-w-full mx-auto px-6 lg:px-10">
            <div class="grid grid-cols-2 md:grid-cols-3 items-center h-16">
                <div class="flex justify-start items-center">
                    <Logo />
                </div>

                <nav class="hidden md:flex justify-center items-center space-x-1 lg:space-x-3 h-full">
                    <div
                        v-for="item in navItems"
                        :key="item.slug"
                        @mouseenter="activeMenuSlug = item.slug"
                        @mouseleave="activeMenuSlug = null"
                        class="h-full flex items-center">
                        <button
                            type="button"
                            @click="activeMenuSlug = item.slug"
                            :aria-expanded="activeMenuSlug === item.slug"
                            :class="[
                                'text-xs font-normal tracking-normal whitespace-nowrap transition-colors duration-300 h-full px-2.5 lg:px-3.5 flex items-center focus:outline-none',
                                activeMenuSlug === item.slug
                                    ? 'text-black'
                                    : 'text-zinc-700 hover:text-black',
                            ]">
                            {{ item.name }}
                        </button>
                        <MegaMenu
                            v-show="activeMenuSlug === item.slug"
                            :item="item"
                            @navigate="closeMegaMenu"
                        />
                    </div>
                </nav>

                <div class="flex items-center justify-end">
                    <div class="flex items-center space-x-5 md:space-x-6">
                        <div class="hidden md:block">
                            <SearchBar />
                        </div>
                        <Link
                            :href="$page.props.auth?.user ? route('dashboard') : route('login')"
                            :title="$page.props.auth?.user ? 'Dashboard' : 'Sign in'"
                            class="hidden md:block text-zinc-700 hover:text-black transition-colors duration-200">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="8.5" r="3" stroke="currentColor" stroke-width="1" />
                                <path d="M17.5 21C17.5 18.5147 15.0376 16.5 12 16.5C8.96243 16.5 6.5 18.5147 6.5 21"
                                    stroke="currentColor" stroke-width="1" stroke-linecap="square" />
                            </svg>
                        </Link>
                    </div>
                    <Hamburger :is-open="isMobileMenuOpen" @toggle="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden ml-5 -mr-2 text-zinc-900" />
                </div>
            </div>
        </div>
    </header>

    <MobileMenu v-if="isMobileMenuOpen" :is-open="isMobileMenuOpen" :nav-items="navItems"
        @close="isMobileMenuOpen = false" />
</template>