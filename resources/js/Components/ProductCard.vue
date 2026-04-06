<script setup>
defineProps({
    product: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="flex flex-col space-y-3 group/item cursor-pointer snap-start">
        <div class="bg-zinc-100 aspect-[4/5] w-full rounded-[1px] overflow-hidden relative">
            <img :src="product.img" :alt="product.name" class="w-full h-full object-cover" />

            <img v-if="product.imgHover" :src="product.imgHover" :alt="product.name"
                class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover/item:opacity-100" />
        </div>

        <div class="flex flex-col space-y-0.5">
            <span class="text-[14px] font-normal text-zinc-900 tracking-tight leading-snug">
                {{ product.name }}
            </span>
            <p v-if="product.description" class="text-[14px] text-zinc-500 font-normal leading-relaxed line-clamp-1">
                {{ product.description }}
            </p>
            <span class="text-[13px] text-zinc-900 font-bold mt-1.5">
                ₱{{ product.price }}
            </span>
        </div>
    </div>
</template>