<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const currentYear = new Date().getFullYear();
const activeNotice = ref(null);

const featuredCategories = computed(() => props.categories.slice(0, 4));

const notices = {
    inquiries: {
        title: 'How Inquiries Work',
        body: 'Select any piece and variant from our catalog to submit a direct product inquiry. Our team confirms real-time stock allocation, pricing, and dispatch timelines within one business day.',
    },
    delivery: {
        title: 'Pickup & Dispatch',
        body: 'Confirmed items are available for scheduled showroom pickup or insured courier dispatch nationwide across the Philippines.',
    },
    privacy: {
        title: 'Privacy Policy',
        body: 'Contact details submitted through product inquiries are used strictly to respond to your request and coordinate item availability. We never sell or share customer data.',
    },
    terms: {
        title: 'Terms of Use',
        body: 'Catalog availability and pricing are updated continuously from live inventory and are confirmed upon inquiry review.',
    },
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <footer class="bg-white border-t border-zinc-200 text-zinc-900">
        <div class="max-w-full mx-auto px-6 lg:px-10 pt-14 pb-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-12">
                <div class="lg:col-span-5 flex flex-col space-y-4 pr-0 lg:pr-12">
                    <Link
                        :href="route('storefront.index')"
                        class="text-lg sm:text-xl uppercase tracking-[0.15em] text-zinc-900 font-bold leading-none w-fit">
                        GreatBuy Originals
                    </Link>

                    <p class="text-xs text-zinc-500 font-normal leading-relaxed max-w-sm">
                        Curated apparel, footwear, and everyday objects engineered for longevity. Browse live inventory and inquire directly on any piece.
                    </p>

                    <div class="pt-1">
                        <Link
                            :href="route('storefront.index')"
                            class="text-xs text-zinc-900 font-medium border-b border-black pb-0.5">
                            Explore Full Catalog
                        </Link>
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col space-y-4">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        Catalog
                    </span>

                    <ul class="flex flex-col space-y-2.5 text-xs">
                        <li>
                            <Link
                                :href="route('storefront.index')"
                                class="text-zinc-500 hover:text-zinc-900 transition-colors">
                                Shop All
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('storefront.index', { in_stock: 1 })"
                                class="text-zinc-500 hover:text-zinc-900 transition-colors">
                                In-Stock Ready
                            </Link>
                        </li>
                        <li v-for="cat in featuredCategories" :key="cat.id">
                            <Link
                                :href="route('storefront.index', { category: cat.slug })"
                                class="text-zinc-500 hover:text-zinc-900 transition-colors">
                                {{ cat.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-2 flex flex-col space-y-4">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        Client Care
                    </span>

                    <ul class="flex flex-col space-y-2.5 text-xs">
                        <li>
                            <button
                                type="button"
                                @click="activeNotice = notices.inquiries"
                                class="text-left text-zinc-500 hover:text-zinc-900 transition-colors">
                                How to Inquire
                            </button>
                        </li>
                        <li>
                            <button
                                type="button"
                                @click="activeNotice = notices.delivery"
                                class="text-left text-zinc-500 hover:text-zinc-900 transition-colors">
                                Pickup &amp; Dispatch
                            </button>
                        </li>
                        <li>
                            <button
                                type="button"
                                @click="activeNotice = notices.terms"
                                class="text-left text-zinc-500 hover:text-zinc-900 transition-colors">
                                Terms of Use
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-3 flex flex-col space-y-4">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        Contact &amp; Hours
                    </span>

                    <div class="flex flex-col space-y-2 text-xs text-zinc-500 leading-relaxed">
                        <a
                            href="mailto:inquiries@greatbuyoriginals.ph"
                            class="text-zinc-900 hover:opacity-70 transition-opacity w-fit">
                            inquiries@greatbuyoriginals.ph
                        </a>
                        <span>Mon – Sat, 10:00 AM – 7:00 PM PHT</span>
                        <span>Manila, Philippines · Nationwide Dispatch</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-100 pt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-[11px] text-zinc-400">
                <span>
                    &copy; {{ currentYear }} GreatBuy Originals. All rights reserved.
                </span>

                <div class="flex items-center space-x-6">
                    <button
                        type="button"
                        @click="activeNotice = notices.privacy"
                        class="hover:text-zinc-900 transition-colors">
                        Privacy Policy
                    </button>
                    <button
                        type="button"
                        @click="activeNotice = notices.terms"
                        class="hover:text-zinc-900 transition-colors">
                        Terms
                    </button>
                    <button
                        type="button"
                        @click="scrollToTop"
                        class="text-zinc-900 border-b border-black pb-0.5">
                        Top &uarr;
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="activeNotice"
            @click.self="activeNotice = null"
            class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-6">
            <div class="bg-white border border-zinc-900 rounded-none max-w-md w-full p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        GreatBuy Originals
                    </span>
                    <button
                        type="button"
                        @click="activeNotice = null"
                        class="text-xs text-zinc-900 border-b border-black pb-0.5">
                        Close
                    </button>
                </div>

                <h3 class="text-base uppercase tracking-wide text-zinc-900 font-bold">
                    {{ activeNotice.title }}
                </h3>

                <p class="text-xs text-zinc-600 font-normal leading-relaxed">
                    {{ activeNotice.body }}
                </p>
            </div>
        </div>
    </footer>
</template>