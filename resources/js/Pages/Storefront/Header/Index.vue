<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';
import Logo from '@/Components/Logo.vue';
import Hamburger from '@/Pages/Storefront/Header/Mobile/Hamburger.vue';
import MobileMenu from './Mobile/Index.vue';
import MegaMenu from './MegaMenu.vue';

const page = usePage();
const isMobileMenuOpen = ref(false);
const activeMenuSlug = ref(null);

const menuData = computed(() => page.props.storefront_menu || { categories: [], tags: [], featured_products: [] });

const navItems = [
    { name: 'Women', slug: 'women', children: ['New Arrivals', 'Shoes', 'Clothing'] },
    { name: 'Men', slug: 'men', children: ['New Arrivals', 'Shoes', 'Clothing'] },
    { name: 'Kids', slug: 'kids', children: ['New Arrivals', 'Shoes', 'Clothing'] },
    { name: 'New Arrivals', slug: 'new-arrivals', children: ['This Week', 'Sustainability', 'Collaborations'] }
];

const resolveTopLevelHref = (item) => {
    if (item.slug === 'new-arrivals') {
        return route('storefront.index', { sort: 'latest' });
    }

    const keywords = item.slug === 'kids' ? ['kids', 'kid'] : [item.slug];
    const matchedCat = menuData.value.categories?.find((c) =>
        keywords.includes(c.slug?.toLowerCase()) || keywords.includes(c.name?.toLowerCase())
    );
    if (matchedCat) {
        return route('storefront.index', { category: matchedCat.slug });
    }

    const matchedTag = menuData.value.tags?.find((t) =>
        keywords.includes(t.slug?.toLowerCase()) || keywords.includes(t.name?.toLowerCase())
    );
    if (matchedTag) {
        return route('storefront.index', { tag: matchedTag.slug });
    }

    return route('storefront.index', { search: item.name });
};

const closeMegaMenu = () => {
    activeMenuSlug.value = null;
};
</script>

<template>
    <header class="sticky top-0 bg-white text-zinc-900 z-50 transition-all duration-300">
        <div class="max-w-full mx-auto px-6 lg:px-10">
            <div class="grid grid-cols-2 md:grid-cols-3 items-center h-16">
                <div class="flex justify-start items-center">
                    <Logo />
                </div>

                <nav class="hidden md:flex justify-center items-center space-x-4 h-full">
                    <div
                        v-for="item in navItems"
                        :key="item.slug"
                        @mouseenter="activeMenuSlug = item.slug"
                        @mouseleave="activeMenuSlug = null"
                        class="h-full flex items-center">
                        <Link
                            :href="resolveTopLevelHref(item)"
                            @click="closeMegaMenu"
                            class="text-xs font-normal tracking-normal text-zinc-700 hover:text-black transition-colors duration-300 h-full px-3 flex items-center">
                            {{ item.name }}
                        </Link>
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