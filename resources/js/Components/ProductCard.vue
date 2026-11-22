<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const primaryImg = computed(() => props.product.img || props.product.media?.[0]?.url || null);
const hoverImg = computed(() => props.product.imgHover || props.product.media?.[1]?.url || null);

const formattedPrice = computed(() => {
    const raw = props.product.price;
    if (typeof raw === 'string' && raw.includes('–')) {
        return raw;
    }
    const num = Number(raw ?? 0);
    return Number.isNaN(num) ? raw : num.toLocaleString('en-PH');
});
</script>

<template>
    <component
        :is="product.slug ? Link : 'div'"
        :href="product.slug ? route('products.show', product.slug) : undefined"
        class="flex flex-col space-y-3 group/item cursor-pointer snap-start">
        <div class="bg-zinc-100 aspect-[4/5] w-full rounded-[1px] overflow-hidden relative">
            <img
                v-if="primaryImg"
                :src="primaryImg"
                :alt="product.name"
                class="w-full h-full object-cover"
            />
            <div
                v-else
                class="w-full h-full flex items-center justify-center text-[11px] uppercase tracking-widest text-zinc-400">
                No Image
            </div>

            <img
                v-if="hoverImg"
                :src="hoverImg"
                :alt="product.name"
                class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover/item:opacity-100"
            />
        </div>

        <div class="flex flex-col space-y-0.5">
            <span class="text-[14px] font-normal text-zinc-900 tracking-tight leading-snug">
                {{ product.name }}
            </span>
            <p v-if="product.description" class="text-[14px] text-zinc-500 font-normal leading-relaxed line-clamp-1">
                {{ product.description }}
            </p>
            <span class="text-[13px] text-zinc-900 font-bold mt-1.5 price-tag">
                ₱{{ formattedPrice }}
            </span>
        </div>
    </component>
</template>