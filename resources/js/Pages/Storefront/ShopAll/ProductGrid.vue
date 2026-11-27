<script setup>
import ProductCard from '@/Components/ProductCard.vue';

defineProps({
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    hasMore: { type: Boolean, default: false },
    isLoadingMore: { type: Boolean, default: false },
    hasActiveFilters: { type: Boolean, default: false },
});

defineEmits(['load-more', 'reset']);
</script>

<template>
    <div class="flex-1 w-full min-w-0">
        <div
            v-if="items.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-1 gap-y-10 lg:gap-x-1.5 lg:gap-y-12"
        >
            <ProductCard v-for="product in items" :key="product.id" :product="product" />
        </div>

        <div v-else class="py-24 text-center space-y-3">
            <p class="text-sm text-zinc-500 font-normal">
                No products match your current selection.
            </p>
            <button
                v-if="hasActiveFilters"
                type="button"
                @click="$emit('reset')"
                class="text-sm text-zinc-900 font-medium hover:text-zinc-500 cursor-pointer"
            >
                Clear all filters
            </button>
        </div>

        <div v-if="items.length > 0" class="flex flex-col items-center justify-center pt-12 space-y-4">
            <span class="text-xs text-zinc-400">
                Showing {{ items.length }} of {{ total || items.length }}
            </span>

            <button
                v-if="hasMore"
                type="button"
                :disabled="isLoadingMore"
                @click="$emit('load-more')"
                class="px-8 py-3.5 text-xs uppercase tracking-[0.25em] font-medium border border-solid border-zinc-900 rounded-none bg-white text-zinc-900 hover:bg-zinc-900 hover:text-white disabled:opacity-50 cursor-pointer"
            >
                {{ isLoadingMore ? 'Loading...' : 'Show more' }}
            </button>
        </div>
    </div>
</template>