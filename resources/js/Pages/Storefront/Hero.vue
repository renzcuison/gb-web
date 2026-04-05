<script setup>
import { Link } from '@inertiajs/vue3';

const brands = [
    { name: 'Adidas', slug: 'adidas', color: 'bg-zinc-100', image: '/images/brands/adidas.jpg', featured: true },
    { name: 'Asics', slug: 'asics', color: 'bg-zinc-50', image: '/images/brands/asics.jpg' },
    { name: 'New Balance', slug: 'new-balance', color: 'bg-zinc-200', image: '/images/brands/new-balance.jpg', tall: true },
    { name: 'Nike', slug: 'nike', color: 'bg-zinc-50', image: '/images/brands/nike.jpg' },
    { name: 'Veja', slug: 'veja', color: 'bg-zinc-200', image: '/images/brands/veja.jpg' },
    { name: 'Puma', slug: 'puma', color: 'bg-zinc-100', image: '/images/brands/puma.jpg' },
];

const getDynamicClasses = (brand, index) => {
    const isLast = index === brands.length - 1;

    let desktop = 'md:col-span-1 md:row-span-1';
    if (brand.featured) desktop = 'md:col-span-2 md:row-span-2';
    if (brand.tall) desktop = 'md:col-span-1 md:row-span-2';

    if (isLast) desktop = 'md:col-span-3 md:row-span-1';

    let mobile = 'col-span-1 row-span-1';
    if (brand.featured) mobile = 'col-span-2 row-span-2';
    if (brand.tall) mobile = 'col-span-1 row-span-2';

    if (isLast && index % 2 === 0 && !brand.featured) {
        mobile = 'col-span-2 row-span-1';
    }

    return `${mobile} ${desktop}`;
};
</script>

<template>
    <section class="w-full border-b border-zinc-200 overflow-hidden">
        <div class="grid grid-cols-2 grid-flow-dense md:grid-cols-4 md:grid-rows-3 h-[calc(100vh-6.5rem)] w-full gap-0">

            <Link v-for="(brand, index) in brands" :key="brand.slug" href="#" :class="[
                getDynamicClasses(brand, index),
                brand.color,
                'group relative overflow-hidden border-r border-b border-zinc-200 last:border-r-0'
            ]">
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <img :src="brand.image" :alt="brand.name"
                        class="w-full h-full object-cover object-center transition-transform duration-[1500ms] cubic-bezier(0.165, 0.84, 0.44, 1) group-hover:scale-105" />
                </div>

                <div
                    class="absolute inset-0 z-10 opacity-0 group-hover:opacity-100 backdrop-blur-sm bg-black/20 transition-opacity duration-700 pointer-events-none">
                </div>

                <div
                    class="absolute inset-0 z-20 p-6 md:p-10 flex flex-col justify-end opacity-0 group-hover:opacity-100 transition-all duration-500 ease-out">
                    <h2
                        class="text-[10px] md:text-xs uppercase tracking-[0.4em] text-white font-bold whitespace-nowrap transform translate-y-2 group-hover:translate-y-0 transition-transform duration-500">
                        Shop {{ brand.name }}
                    </h2>
                </div>
            </Link>

        </div>
    </section>
</template>