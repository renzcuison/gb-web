<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useStorefrontMenu } from '@/Composables/useStorefrontMenu';
import { resolveProductImages } from '@/Composables/useProductImage';

const props = defineProps({
    isActive: Boolean,
    item: Object,
    isVisible: Boolean,
});

const emit = defineEmits(['close']);
const { menuData, buildMenuColumns } = useStorefrontMenu();

const arrivalProducts = computed(() =>
    (menuData.value.featured_products || []).slice(0, 4).map((product) => ({
        ...product,
        _thumb: resolveProductImages(product).primaryImg,
    }))
);
const columns = computed(() => buildMenuColumns(props.item));
</script>

<template>
    <Transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-[1400px]" leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 max-h-[1400px]" leave-to-class="opacity-0 max-h-0">
        <div v-if="isActive" class="mt-6 flex flex-col space-y-6 overflow-hidden">
            <div v-if="item.slug === 'new-arrivals' && arrivalProducts.length > 0" class="relative w-screen -ml-8 pb-2">
                <div class="flex overflow-x-auto gap-x-1 px-8 snap-x snap-mandatory scroll-pl-8 no-scrollbar transition-all duration-1000 ease-out"
                    :style="{
                        transitionDelay: '200ms',
                        transform: isVisible ? 'translateY(0)' : 'translateY(20px)',
                        opacity: isVisible ? 1 : 0
                    }">
                    <Link
                        v-for="product in arrivalProducts"
                        :key="product.id"
                        :href="route('products.show', product.slug)"
                        @click="emit('close')"
                        class="flex-none w-[68vw] snap-start flex flex-col space-y-2.5">

                        <div class="aspect-[3/4] w-full bg-zinc-100 overflow-hidden flex items-center justify-center">
                            <img
                                v-if="product._thumb"
                                :src="product._thumb"
                                :alt="product.name"
                                class="w-full h-full object-cover transition-all duration-500"
                            />
                            <span
                                v-else
                                class="text-[9px] uppercase tracking-widest text-zinc-400 select-none">
                                No Image
                            </span>
                        </div>

                        <div class="flex flex-col space-y-0.5 pr-2">
                            <span class="text-[14px] font-normal text-zinc-900 tracking-tight leading-snug">
                                {{ product.name }}
                            </span>
                            <p v-if="product.description" class="text-[13px] text-zinc-500 font-normal leading-relaxed line-clamp-1">
                                {{ product.description }}
                            </p>
                            <span class="text-[13px] text-zinc-900 font-bold mt-1 price-tag">
                                ₱{{ Number(product.price ?? 0).toLocaleString('en-PH') }}
                            </span>
                        </div>
                    </Link>
                    <div class="flex-none w-4"></div>
                </div>
            </div>

            <div v-for="(col, colIdx) in columns" :key="col.title" class="flex flex-col space-y-3.5">
                <span
                    class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold transition-all duration-700 ease-out"
                    :style="{
                        transitionDelay: `${(colIdx * 80) + 250}ms`,
                        transform: isVisible ? 'translateY(0)' : 'translateY(12px)',
                        opacity: isVisible ? 1 : 0
                    }">
                    {{ col.title }}
                </span>

                <Link
                    v-for="(link, index) in col.links"
                    :key="`${col.title}-${link.label}`"
                    :href="link.href"
                    @click="emit('close')"
                    class="text-sm font-normal text-zinc-500 transition-all duration-700 ease-out hover:text-black"
                    :style="{
                        transitionDelay: `${((colIdx * 4 + index) * 45) + 300}ms`,
                        transform: isVisible ? 'translateY(0)' : 'translateY(15px)',
                        opacity: isVisible ? 1 : 0
                    }">
                    {{ link.label }}
                </Link>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>