<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useStorefrontMenu } from '@/Composables/useStorefrontMenu';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['navigate']);
const { buildMenuColumns, getShowcaseSlots } = useStorefrontMenu();

const columns = computed(() => buildMenuColumns(props.item));
const showcaseSlots = computed(() => getShowcaseSlots(props.item));
</script>

<template>
    <div class="absolute top-full left-0 w-full">
        <div class="fixed inset-0 bg-black/[0.01] backdrop-blur-md pointer-events-none -z-10"
            style="clip-path: inset(104px 0 0 0);"></div>

        <div class="bg-white relative z-50 w-full">
            <div class="max-w-full mx-auto px-6 lg:px-10 py-12">
                <div class="flex justify-between items-start gap-12">
                    <!-- Left Navigation Columns -->
                    <div class="flex items-start gap-14 lg:gap-20 text-left">
                        <div
                            v-for="col in columns"
                            :key="col.title"
                            class="flex flex-col space-y-4">
                            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold">
                                {{ col.title }}
                            </span>
                            <div class="flex flex-col space-y-3">
                                <Link
                                    v-for="link in col.links"
                                    :key="`${col.title}-${link.label}`"
                                    :href="link.href"
                                    @click="emit('navigate')"
                                    class="text-sm font-normal text-zinc-600 hover:text-black transition-colors w-fit">
                                    {{ link.label }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right Product Showcase (Hairline Seam) -->
                    <div v-if="showcaseSlots.length > 0" class="flex gap-x-1">
                        <div
                            v-for="slot in showcaseSlots"
                            :key="slot.label"
                            @click="emit('navigate')"
                            class="flex flex-col space-y-3 w-[215px] lg:w-[235px]">
                            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold">
                                {{ slot.label }}
                            </span>
                            <ProductCard :product="slot.product" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>