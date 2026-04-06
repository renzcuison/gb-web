<script setup>
import { Link } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    item: Object
});

const getProductData = (slug, index) => {
    const products = {
        'men': [
            { label: 'Featured', name: 'Essential Runner', description: 'Midnight Blue / Mesh Upper', price: '4500', img: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600' },
            { label: 'Trending', name: 'Heavyweight Tee', description: 'Boxy Fit / Organic Cotton', price: '1200', img: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?q=80&w=600' },
            { label: 'Collections', name: 'Utility Jacket', description: 'Water Repellent / Sand', price: '5800', img: 'https://images.unsplash.com/photo-1505022610485-0249ba5b3675?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?q=80&w=600' }
        ],
        'women': [
            { label: 'Featured', name: 'Classic Heel', description: 'Patent Leather / Noir', price: '3200', img: 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600' },
            { label: 'Trending', name: 'Silk Blouse', description: 'Relaxed Silhouette / Ivory', price: '2800', img: 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1485462537746-965f33f7f6a7?q=80&w=600' },
            { label: 'Collections', name: 'Pleated Dress', description: 'A-Line / Forest Green', price: '4100', img: 'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?q=80&w=600' }
        ],
        'accessories': [
            { label: 'Featured', name: 'Minimalist Watch', description: 'Silver Case / Black Strap', price: '8500', img: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?q=80&w=600' },
            { label: 'Trending', name: 'Leather Tote', description: 'Full Grain / Cognac', price: '12000', img: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=600' },
            { label: 'Collections', name: 'Horizon Shades', description: 'Polarized / Matte Black', price: '6400', img: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=600' }
        ],
        'new-arrivals': [
            { label: 'Featured', name: 'Orbit Sneaker', description: 'Triple White / Eco-Leather', price: '5200', img: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600' },
            { label: 'Trending', name: 'Canvas Hi-Top', description: 'Raw Indigo / Vulcanized Sole', price: '3800', img: 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=600', imgHover: 'https://images.unsplash.com/photo-1539185441755-769473a23570?q=80&w=600' },
            {
                label: 'Collections',
                name: 'Studio Tee',
                description: 'Carbon Black / Oversized',
                price: '1500',
                img: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600',
                imgHover: 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?q=80&w=600'
            }
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
                        <div v-for="i in 3" :key="i" class="flex flex-col space-y-4 w-[200px]">
                            <h3 class="text-[10px] uppercase tracking-widest text-zinc-400 font-semibold">
                                {{ getProductData(item.slug, i).label }}
                            </h3>
                            <ProductCard :product="getProductData(item.slug, i)" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>