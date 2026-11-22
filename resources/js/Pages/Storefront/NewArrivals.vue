<script setup>
import ProductSlider from '@/Components/ProductSlider.vue';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <ProductSlider
        v-if="items.length > 0"
        title="New Arrivals"
        subtitle="Shop Now"
        :items="items"
        view-all-href="#catalog"
    />
</template>