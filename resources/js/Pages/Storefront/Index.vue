<script setup>
import SearchBar from '@/Components/SearchBar.vue';
import Logo from '@/Components/Logo.vue';
import { Link, Head } from '@inertiajs/vue3';

defineProps({
    products: Object
});
</script>

<template>

    <Head title="Storefront" />

    <div class="min-h-screen bg-white">
        <header class="sticky top-0 bg-transparent z-50">
            <div class="max-w-full mx-auto px-6 lg:px-10">
                <div class="grid grid-cols-3 items-center h-16">

                    <div class="flex justify-start items-center">
                        <Logo />
                    </div>

                    <nav class="hidden md:flex justify-center items-center space-x-8">
                        <Link href="#"
                            class="text-xs font-regular text-black/80 hover:text-black transition-colors duration-300">
                            Men
                        </Link>
                        <Link href="#"
                            class="text-xs font-regular text-black/80 hover:text-black transition-colors duration-300">
                            Women
                        </Link>
                        <Link href="#"
                            class="text-xs font-regular text-black/80 hover:text-black transition-colors duration-300">
                            Accessories
                        </Link>
                    </nav>

                    <div class="flex items-center justify-end space-x-6">
                        <SearchBar />

                        <template v-if="!$page.props.auth.user">
                            <Link :href="route('login')"
                                class="text-black/80 hover:text-black transition-colors duration-200 ease-out">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="8.5" r="3" stroke="currentColor" stroke-width="1" />
                                    <path d="M17.5 21C17.5 18.5147 15.0376 16.5 12 16.5C8.96243 16.5 6.5 18.5147 6.5 21"
                                        stroke="currentColor" stroke-width="1" stroke-linecap="square" />
                                </svg>
                            </Link>
                        </template>

                        <Link v-else :href="route('profile.edit')"
                            class="text-xs font-regular uppercase tracking-[0.1em] text-black/80 hover:text-black transition-colors duration-200 ease-out">
                            {{ $page.props.auth.user.name }}
                        </Link>
                    </div>

                </div>
            </div>
        </header>

        <main class="max-w-full px-6 lg:px-10 py-12">
        </main>
    </div>
</template>