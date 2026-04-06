<script setup>
import { ref } from 'vue';
import SliderArrow from './SliderArrow.vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
    title: String,
    subtitle: String,
    items: Array
});

const scrollContainer = ref(null);

const scroll = (direction) => {
    if (scrollContainer.value) {
        const scrollAmount = scrollContainer.value.clientWidth * 0.8;
        scrollContainer.value.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    }
};
</script>

<template>
    <section class="max-w-full mx-auto px-6 lg:px-10 pt-14 pb-10">
        <div class="flex flex-col space-y-6">
            <div class="flex flex-col space-y-0.5">
                <span v-if="subtitle"
                    class="text-[12px] uppercase tracking-[0.25em] text-zinc-400 font-bold leading-none">{{ subtitle
                    }}</span>

                <div class="flex items-baseline justify-between">
                    <div class="flex items-baseline space-x-6">
                        <h2 class="text-xl sm:text-2xl uppercase tracking-wider text-zinc-900 font-bold leading-none">
                            {{ title }}
                        </h2>
                        <a href="#"
                            class="hidden md:inline-block text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 whitespace-nowrap">
                            View all
                        </a>
                    </div>

                    <div class="flex items-baseline">
                        <a href="#"
                            class="md:hidden text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 whitespace-nowrap">
                            View all
                        </a>

                        <div class="hidden md:flex items-center space-x-2 ml-6">
                            <SliderArrow direction="left" @click="scroll('left')" />
                            <SliderArrow direction="right" @click="scroll('right')" />
                        </div>
                    </div>
                </div>
            </div>

            <div ref="scrollContainer"
                class="flex overflow-x-auto gap-0.5 lg:gap-1 scrollbar-hide snap-x snap-mandatory pb-4">
                <ProductCard v-for="product in items" :key="product.name" :product="product"
                    class="flex-none w-[calc(85%-0.125rem)] lg:w-[calc(25%-0.1875rem)]" />
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>