<script setup>
import { Link } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';

const suggestions = [
    { label: 'Heavyweight Tees', url: '#' },
    { label: 'Essential Runners', url: '#' },
    { label: 'Minimalist Accessories', url: '#' },
    { label: 'Studio Collection', url: '#' },
    { label: 'Summer Drops', url: '#' }
];
</script>

<template>
    <section
        class="relative w-full py-10 sm:py-12 overflow-hidden min-h-[500px] md:min-h-[550px] flex flex-col justify-between border-y border-zinc-900 px-5 sm:px-6 lg:px-10 bg-zinc-950">

        <div class="absolute inset-0 z-0">
            <img src="images/brands/banner.jpg" alt="GreatBuy Originals Lifestyle"
                class="w-full h-full object-cover object-center brightness-[0.75] contrast-125" />
            <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/20 to-transparent"></div>
        </div>

        <div class="relative z-10 flex flex-col space-y-5 sm:space-y-6 w-full self-start">
            <div class="flex flex-col space-y-1.5 sm:space-y-2 text-left">
                <h2 class="text-lg sm:text-2xl uppercase tracking-wider text-white font-bold leading-none">
                    SEARCH AND DISCOVER
                </h2>
                <p class="text-xs sm:text-sm text-white font-medium leading-relaxed max-w-[280px] sm:max-w-md">
                    Explore the GreatBuy Originals collection. Find exactly what you need.
                </p>
            </div>

            <div class="flex flex-col space-y-4 w-full md:max-w-[550px]">
                <SearchBar dark class="!w-full !md:w-full shadow-2xl shadow-black/80"
                    placeholder="Search for 'Studio Tee' or 'Orbit Sneaker'..." />

                <div class="flex flex-wrap gap-x-3.5 sm:gap-x-5 gap-y-2 items-center">
                    <span
                        class="text-[8px] sm:text-[10px] uppercase tracking-widest text-white/40 font-bold">Suggestions:</span>
                    <Link v-for="item in suggestions" :key="item.label" :href="item.url"
                        class="text-[10px] sm:text-[11px] text-white font-medium hover:text-zinc-400 transition-colors duration-300">
                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex flex-col space-y-3 sm:space-y-4 md:w-1/2 self-end text-right mt-10 md:mt-0">
            <div class="flex flex-col space-y-1.5 sm:space-y-2">
                <h2 class="text-lg sm:text-2xl uppercase tracking-wider text-white font-bold leading-none">
                    TRENDING NOW
                </h2>
            </div>

            <div class="flex flex-wrap gap-x-4 sm:gap-x-8 gap-y-2.5 sm:gap-y-4 justify-end">
                <Link v-for="item in suggestions" :key="item.label" :href="item.url"
                    class="animated-link text-xs sm:text-sm text-white font-medium relative py-1">
                    {{ item.label }}
                </Link>
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
</style>