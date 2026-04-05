<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: Object
});

const getProductData = (slug, index) => {
    const products = {
        'men': [
            { label: 'Featured', name: 'Essential Runner', price: '4500', img: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600' },
            { label: 'Trending', name: 'Heavyweight Tee', price: '1200', img: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600' },
            { label: 'Collections', name: 'Utility Jacket', price: '5800', img: 'https://images.unsplash.com/photo-1505022610485-0249ba5b3675?q=80&w=600' }
        ],
        'women': [
            { label: 'Featured', name: 'Classic Heel', price: '3200', img: 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=600' },
            { label: 'Trending', name: 'Silk Blouse', price: '2800', img: 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?q=80&w=600' },
            { label: 'Collections', name: 'Pleated Dress', price: '4100', img: 'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?q=80&w=600' }
        ],
        'accessories': [
            { label: 'Featured', name: 'Minimalist Watch', price: '8500', img: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=600' },
            { label: 'Trending', name: 'Leather Tote', price: '12000', img: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600' },
            { label: 'Collections', name: 'Horizon Shades', price: '6400', img: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?q=80&w=600' }
        ],
        'new-arrivals': [
            { label: 'Featured', name: 'Orbit Sneaker', price: '5200', img: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600' },
            { label: 'Trending', name: 'Canvas Hi-Top', price: '3800', img: 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=600' },
            { label: 'Collections', name: 'Studio Tee', price: '1500', img: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=600' }
        ]
    };
    return products[slug] ? products[slug][index - 1] : products['new-arrivals'][index - 1];
};
</script>

<template>
    <div class="absolute top-full left-0 w-full hidden group-hover:block">
        <div class="fixed inset-0 bg-black/[0.01] backdrop-blur-md pointer-events-none -z-10"
            style="clip-path: inset(104px 0 0 0);"></div>

        <div class="bg-white border-b border-zinc-100 shadow-sm relative z-50 w-full">
            <div class="max-w-full mx-auto px-6 lg:px-10 py-12">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col space-y-5 text-left">
                        <h3 class="text-[10px] uppercase tracking-widest text-zinc-400 font-semibold">Categories</h3>
                        <div class="flex flex-col space-y-3.5">
                            <Link v-for="child in item.children" :key="child" href="#"
                                class="text-sm font-normal text-zinc-600 hover:text-black transition-colors w-fit">
                                {{ child }}
                            </Link>
                        </div>
                    </div>

                    <div class="flex gap-10">
                        <div v-for="i in 3" :key="i" class="flex flex-col space-y-4 w-[240px] group/item">
                            <h3 class="text-[10px] uppercase tracking-widest text-zinc-400 font-semibold">
                                {{ getProductData(item.slug, i).label }}
                            </h3>
                            <div class="bg-zinc-100 aspect-[4/5] w-full rounded-sm overflow-hidden relative">
                                <img :src="getProductData(item.slug, i).img" :alt="getProductData(item.slug, i).name"
                                    class="w-full h-full object-cover transition-transform duration-[1.5s] ease-out group-hover/item:scale-105" />
                            </div>
                            <div class="flex flex-col space-y-1">
                                <span class="text-[11px] font-medium text-zinc-900 tracking-tight">
                                    {{ getProductData(item.slug, i).name }}
                                </span>
                                <span class="text-[10px] text-zinc-500 font-normal">
                                    ₱{{ getProductData(item.slug, i).price }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>