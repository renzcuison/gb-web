<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const currentYear = new Date().getFullYear();
const activeChatKey = ref(null);

const featuredCategories = computed(() => props.categories.slice(0, 4));

const chatTopics = {
    inquiries: {
        question: 'How do inquiries work?',
        answer: 'Select any item and variant from our catalog to submit a direct product inquiry, or message us on Facebook. Our team confirms real-time stock allocation, pricing, and dispatch timelines within one business day.',
    },
    delivery: {
        question: 'How do pickup & shipping work?',
        answer: 'Confirmed items are available for scheduled meetup or pickup in Davao City, or insured courier dispatch nationwide across the Philippines.',
    },
    privacy: {
        question: 'What is your privacy policy?',
        answer: 'Contact details submitted through product inquiries are used strictly to respond to your request and coordinate item availability. We never sell or share customer data.',
    },
    terms: {
        question: 'What are your terms of use?',
        answer: 'Catalog availability and pricing are updated continuously from live inventory and are confirmed upon inquiry review.',
    },
};

const activeChat = computed(() => (activeChatKey.value ? chatTopics[activeChatKey.value] : null));

const toggleChat = (key) => {
    activeChatKey.value = activeChatKey.value === key ? null : key;
};

const browseCatalog = (params = {}) => {
    router.get(route('storefront.index'), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
        },
    });
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <footer class="relative overflow-hidden bg-white/35 backdrop-blur-2xl text-zinc-900">
        <!-- Sheer white-to-silver diffused glass light fields -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden bg-zinc-50/60" aria-hidden="true">
            <div class="absolute -top-24 left-[12%] h-72 w-[30rem] rounded-full bg-zinc-200/45 blur-[95px]"></div>
            <div class="absolute -bottom-24 left-[42%] h-72 w-[32rem] rounded-full bg-zinc-300/35 blur-[105px]"></div>
            <div class="absolute -top-16 right-[10%] h-64 w-[26rem] rounded-full bg-zinc-200/40 blur-[90px]"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-white/75 via-white/30 to-zinc-100/45"></div>
        </div>

        <div class="relative z-10 max-w-full mx-auto px-6 lg:px-10 pt-14 pb-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-12">
                <div class="lg:col-span-5 flex flex-col space-y-4 pr-0 lg:pr-12">
                    <Logo />

                    <p class="text-xs text-zinc-500 font-normal leading-relaxed max-w-sm">
                        Authentic fragrances, designer bags, and everyday apparel. Browse and inquire directly on any item.
                    </p>

                    <div class="pt-1">
                        <button
                            type="button"
                            @click="browseCatalog()"
                            class="text-xs text-zinc-900 font-medium cursor-pointer hover:text-zinc-500">
                            Explore Full Catalog
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col space-y-4">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        Catalog
                    </span>

                    <ul class="flex flex-col space-y-2.5 text-xs">
                        <li>
                            <button
                                type="button"
                                @click="browseCatalog()"
                                class="text-left text-zinc-600 hover:text-zinc-900 cursor-pointer">
                                Shop All
                            </button>
                        </li>
                        <li>
                            <button
                                type="button"
                                @click="browseCatalog({ in_stock: 1 })"
                                class="text-left text-zinc-600 hover:text-zinc-900 cursor-pointer">
                                In-Stock Ready
                            </button>
                        </li>
                        <li v-for="cat in featuredCategories" :key="cat.id">
                            <button
                                type="button"
                                @click="browseCatalog({ category: cat.slug })"
                                class="text-left text-zinc-600 hover:text-zinc-900 cursor-pointer">
                                {{ cat.name }}
                            </button>
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
                                @click="toggleChat('inquiries')"
                                :class="[
                                    'text-left cursor-pointer',
                                    activeChatKey === 'inquiries'
                                        ? 'text-zinc-900 font-medium'
                                        : 'text-zinc-600 hover:text-zinc-900',
                                ]">
                                How to Inquire
                            </button>
                        </li>
                        <li>
                            <button
                                type="button"
                                @click="toggleChat('delivery')"
                                :class="[
                                    'text-left cursor-pointer',
                                    activeChatKey === 'delivery'
                                        ? 'text-zinc-900 font-medium'
                                        : 'text-zinc-600 hover:text-zinc-900',
                                ]">
                                Pickup &amp; Shipping
                            </button>
                        </li>
                        <li>
                            <button
                                type="button"
                                @click="toggleChat('terms')"
                                :class="[
                                    'text-left cursor-pointer',
                                    activeChatKey === 'terms'
                                        ? 'text-zinc-900 font-medium'
                                        : 'text-zinc-600 hover:text-zinc-900',
                                ]">
                                Terms of Use
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-3 flex flex-col space-y-4">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                        Contact &amp; Connect
                    </span>

                    <div class="flex flex-col space-y-2 text-xs text-zinc-600 leading-relaxed">
                        <a
                            href="mailto:greatbuyoriginals@gmail.com"
                            class="text-zinc-900 hover:text-zinc-500 w-fit">
                            greatbuyoriginals@gmail.com
                        </a>
                        <a
                            href="https://www.facebook.com/profile.php?id=100075567471861"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-zinc-900 hover:text-zinc-500 w-fit">
                            Facebook &middot; GreatBuy Originals
                        </a>
                        <span>Mon – Sat, 10:00 AM – 7:00 PM PHT</span>
                        <span>Davao City, Philippines &middot; Nationwide Shipping</span>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-[11px] text-zinc-400">
                <span>
                    &copy; {{ currentYear }} GreatBuy Originals. All rights reserved.
                </span>

                <div class="flex items-center space-x-6">
                    <button
                        type="button"
                        @click="toggleChat('privacy')"
                        :class="[
                            'cursor-pointer',
                            activeChatKey === 'privacy'
                                ? 'text-zinc-900 font-medium'
                                : 'hover:text-zinc-900',
                        ]">
                        Privacy Policy
                    </button>
                    <button
                        type="button"
                        @click="toggleChat('terms')"
                        :class="[
                            'cursor-pointer',
                            activeChatKey === 'terms'
                                ? 'text-zinc-900 font-medium'
                                : 'hover:text-zinc-900',
                        ]">
                        Terms
                    </button>
                    <button
                        type="button"
                        @click="scrollToTop"
                        class="text-zinc-900 cursor-pointer hover:text-zinc-500">
                        Top &uarr;
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="activeChat"
            class="fixed bottom-6 right-6 z-50 w-[calc(100vw-3rem)] max-w-sm bg-white/75 backdrop-blur-2xl border border-zinc-200 text-zinc-900">
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                    Client Care
                </span>
                <button
                    type="button"
                    @click="activeChatKey = null"
                    aria-label="Close chat"
                    class="text-[11px] text-zinc-600 hover:text-zinc-900 cursor-pointer">
                    Close
                </button>
            </div>

            <div class="px-5 py-2 space-y-3">
                <div class="flex justify-end">
                    <div class="bg-zinc-900 text-white text-xs px-4 py-3 max-w-[85%] leading-relaxed">
                        {{ activeChat.question }}
                    </div>
                </div>

                <div class="flex justify-start">
                    <div class="bg-zinc-100/80 text-zinc-800 text-xs px-4 py-3 max-w-[90%] leading-relaxed">
                        {{ activeChat.answer }}
                    </div>
                </div>
            </div>

            <div class="px-5 py-3.5 flex items-center justify-between gap-2 text-[11px]">
                <span class="text-zinc-400">Direct assistance</span>
                <a
                    href="https://www.facebook.com/profile.php?id=100075567471861"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-zinc-900 font-medium hover:text-zinc-500">
                    Message on Facebook
                </a>
            </div>
        </div>
    </footer>
</template>