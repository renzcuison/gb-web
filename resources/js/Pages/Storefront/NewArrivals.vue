<script setup>
import ProductSlider from '@/Components/ProductSlider.vue';

const arrivals = [
    {
        name: 'Vanguard Leather Tote',
        description: 'Hot Red / Silver Hardware',
        price: '12500',
        img: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=600'
    },
    {
        name: 'Chelsea Burnished Boot',
        description: 'Antique Walnut / Gum Sole',
        price: '9800',
        img: 'https://images.unsplash.com/photo-1638247025967-b4e38f787b76?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1605034313761-73ea4a0cfbf3?q=80&w=600'
    },
    {
        name: 'Horizon Aviator Sunglasses',
        description: 'Dark Black',
        price: '4200',
        img: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=600'
    },
    {
        name: 'Urban Tech Backpack',
        description: 'Matte Graphite',
        price: '7600',
        img: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?q=80&w=600'
    },
    {
        name: 'Suede Heritage Bomber',
        description: 'Desert Sand / Ribbed Trim',
        price: '18500',
        img: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1559551409-dadc959f76b8?q=80&w=600'
    },
    {
        name: 'Pebble Slim Cardholder',
        description: 'Cognac Brown',
        price: '2200',
        img: 'https://images.unsplash.com/photo-1627123424574-724758594e93?q=80&w=600',
        imgHover: 'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=600'
    }
];
</script>

<template>
    <ProductSlider title="New Arrivals" subtitle="Shop Now" :items="arrivals" />
</template>