<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    brands: {
        type: Array,
        default: () => [],
    },
});

const curatedTiles = [
    {
        name: 'Guess',
        fallbackSlug: 'guess',
        aliases: ['guess'],
        color: 'bg-zinc-100',
        image: '/images/brands/guess.jpg',
        objectPosition: 'center center',
        layout: 'col-span-1 aspect-[2/3] md:aspect-auto md:col-span-1 md:row-span-2',
    },
    {
        name: 'Bath & Body Works',
        fallbackSlug: 'bath-body-works',
        aliases: ['bath & body works', 'bath and body works', 'bbw'],
        color: 'bg-zinc-100',
        image: '/images/brands/bath-and-body-works.jpg',
        objectPosition: 'center 35%',
        layout: 'col-span-1 aspect-[4/5] md:aspect-auto md:col-span-1 md:row-span-1',
    },
    {
        name: "Victoria's Secret",
        fallbackSlug: 'victorias-secret',
        aliases: ["victoria's secret", 'victorias secret', 'vs'],
        color: 'bg-zinc-900',
        image: '/images/brands/victorias-secret.jpg',
        objectPosition: 'center center',
        layout: 'col-span-2 aspect-[4/3] md:aspect-auto md:col-span-1 md:row-span-1',
    },
    {
        name: 'Lacoste',
        fallbackSlug: 'lacoste',
        aliases: ['lacoste'],
        color: 'bg-zinc-100',
        image: '/images/brands/lacoste.jpg',
        objectPosition: 'center center',
        layout: 'col-span-1 aspect-[2/3] md:aspect-auto md:col-span-1 md:row-span-2',
    },
    {
        name: 'Karl Lagerfeld',
        fallbackSlug: 'karl-lagerfeld',
        aliases: ['karl lagerfeld'],
        color: 'bg-zinc-100',
        image: '/images/brands/karl-lagerfeld.jpg',
        objectPosition: 'left center',
        layout: 'col-span-1 aspect-[4/5] md:aspect-auto md:col-span-1 md:row-span-1',
    },
    {
        name: 'Michael Kors',
        fallbackSlug: 'michael-kors',
        aliases: ['michael kors', 'mk'],
        color: 'bg-zinc-100',
        image: '/images/brands/michael-kors.jpg',
        objectPosition: 'center 55%',
        layout: 'col-span-2 aspect-[4/3] sm:col-span-1 md:aspect-auto md:col-span-1 md:row-span-1',
    },
];

const tiles = computed(() =>
    curatedTiles.map((tile) => {
        const matchedBrand = props.brands.find((b) => {
            const lowerName = (b.name || '').trim().toLowerCase();
            return tile.aliases.includes(lowerName) || b.slug === tile.fallbackSlug;
        });

        return {
            ...tile,
            slug: matchedBrand?.slug || tile.fallbackSlug,
        };
    })
);

const shopBrand = (slug) => {
    router.get(
        route('storefront.index'),
        { brand: slug },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
            },
        }
    );
};
</script>

<template>
    <section class="w-full bg-white border-b border-zinc-200 overflow-hidden">
        <div
            class="grid grid-cols-2 grid-flow-dense md:grid-cols-4 md:grid-rows-2 md:h-[calc(100vh-6.5rem)] w-full gap-0">
            <button
                v-for="tile in tiles"
                :key="tile.name"
                type="button"
                @click="shopBrand(tile.slug)"
                :class="[
                    tile.layout,
                    tile.color,
                    'group relative overflow-hidden border-r border-b border-zinc-200 text-left cursor-pointer'
                ]">
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <img
                        :src="tile.image"
                        :alt="tile.name"
                        :style="{ objectPosition: tile.objectPosition }"
                        class="w-full h-full object-cover transition-transform duration-[1500ms] ease-out group-hover:scale-105"
                    />
                </div>

                <div
                    class="absolute inset-x-0 bottom-0 h-24 z-10 bg-gradient-to-t from-black/55 via-black/15 to-transparent pointer-events-none">
                </div>

                <div
                    class="absolute inset-0 z-10 opacity-0 group-hover:opacity-100 backdrop-blur-[2px] bg-black/25 transition-opacity duration-500 pointer-events-none">
                </div>

                <div class="absolute inset-x-0 bottom-0 z-20 p-5 md:p-6 flex items-end">
                    <h2 class="text-[11px] md:text-xs uppercase tracking-[0.3em] text-white font-semibold">
                        <span class="group-hover:hidden">{{ tile.name }}</span>
                        <span class="hidden group-hover:inline">Shop {{ tile.name }}</span>
                    </h2>
                </div>
            </button>
        </div>
    </section>
</template>