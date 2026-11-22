<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AnnouncementBar from '@/Pages/Storefront/AnnouncementBar.vue';
import Header from '@/Pages/Storefront/Header/Index.vue';
import Footer from '@/Pages/Storefront/Footer.vue';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
});

const mediaList = computed(() => props.product.media || []);
const activeImageIndex = ref(0);

const variants = computed(() => props.product.variants || []);

const defaultVariantId = () => {
    const inStock = variants.value.find((v) => Number(v.stock_qty) > 0);
    return inStock ? inStock.id : variants.value[0]?.id ?? null;
};

const selectedVariantId = ref(defaultVariantId());
const inquirySubmitted = ref(false);

const inquiryForm = useForm({
    product_id: props.product.id,
    product_variant_id: selectedVariantId.value,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    quantity: 1,
    message: '',
});

watch(selectedVariantId, (val) => {
    inquiryForm.product_variant_id = val;
});

watch(
    () => props.product.id,
    (newId) => {
        activeImageIndex.value = 0;
        const nextVariantId = defaultVariantId();
        selectedVariantId.value = nextVariantId;
        inquirySubmitted.value = false;
        inquiryForm.reset();
        inquiryForm.clearErrors();
        inquiryForm.product_id = newId;
        inquiryForm.product_variant_id = nextVariantId;
    }
);

const activeImage = computed(() => mediaList.value[activeImageIndex.value]?.url || null);

const selectedVariant = computed(
    () => variants.value.find((v) => v.id === selectedVariantId.value) || variants.value[0] || null
);

const activePrice = computed(() => {
    const raw = selectedVariant.value?.price_override ?? props.product.price ?? 0;
    return Number(raw).toLocaleString('en-PH');
});

const availabilityText = computed(() => {
    const qty = Number(selectedVariant.value?.stock_qty ?? 0);
    const threshold = Number(selectedVariant.value?.low_stock_threshold ?? 10);

    if (qty <= 0) return 'Out of Stock — Available for inquiry';
    if (qty <= threshold) return `Low Stock (${qty} remaining)`;
    return `In Stock (${qty} available)`;
});

const scrollToInquiry = () => {
    document.getElementById('inquiry-section')?.scrollIntoView({ behavior: 'smooth' });
};

const submitInquiry = () => {
    inquiryForm.product_id = props.product.id;
    inquiryForm.product_variant_id = selectedVariant.value?.id ?? null;

    inquiryForm.post(route('inquiries.store'), {
        preserveScroll: true,
        onSuccess: () => {
            inquirySubmitted.value = true;
            inquiryForm.reset('customer_name', 'customer_email', 'customer_phone', 'quantity', 'message');
        },
    });
};

const resetInquiryState = () => {
    inquirySubmitted.value = false;
    inquiryForm.clearErrors();
};
</script>

<template>
    <Head :title="product.name" />

    <div class="min-h-screen bg-white text-zinc-900 flex flex-col">
        <AnnouncementBar />
        <Header />

        <main class="flex-1 max-w-full w-full mx-auto px-6 lg:px-10 pt-8 pb-20 space-y-20">
            <nav class="flex items-center space-x-2 text-xs text-zinc-400">
                <Link :href="route('storefront.index')" class="text-zinc-600 hover:text-black transition-colors">
                    Storefront
                </Link>
                <span v-if="product.category">/</span>
                <Link
                    v-if="product.category"
                    :href="route('storefront.index', { category: product.category.slug })"
                    class="hover:text-black transition-colors">
                    {{ product.category.name }}
                </Link>
                <span>/</span>
                <span class="text-zinc-900 truncate">{{ product.name }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                <div class="lg:col-span-7 flex flex-col-reverse sm:flex-row gap-3">
                    <div
                        v-if="mediaList.length > 1"
                        class="flex sm:flex-col gap-2 overflow-x-auto sm:overflow-y-auto sm:max-h-[640px] shrink-0">
                        <button
                            v-for="(item, idx) in mediaList"
                            :key="item.id"
                            type="button"
                            @click="activeImageIndex = idx"
                            :class="[
                                'w-16 h-20 sm:w-20 sm:h-24 bg-zinc-100 rounded-[1px] overflow-hidden shrink-0 transition-opacity',
                                activeImageIndex === idx
                                    ? 'ring-1 ring-zinc-900 opacity-100'
                                    : 'opacity-50 hover:opacity-100',
                            ]">
                            <img :src="item.url" :alt="product.name" class="w-full h-full object-cover" />
                        </button>
                    </div>

                    <div class="flex-1 aspect-[4/5] bg-zinc-100 rounded-[1px] overflow-hidden">
                        <img
                            v-if="activeImage"
                            :src="activeImage"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-xs uppercase tracking-[0.3em] text-zinc-400">
                            No Image
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 flex flex-col space-y-6 lg:sticky lg:top-24">
                    <div class="flex flex-col space-y-2 border-b border-zinc-100 pb-6">
                        <span class="text-[12px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-none">
                            {{ product.brand?.name || product.category?.name || 'GreatBuy Originals' }}
                        </span>

                        <h1 class="text-2xl sm:text-3xl uppercase tracking-wide text-zinc-900 font-bold leading-tight pt-1">
                            {{ product.name }}
                        </h1>

                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-lg text-zinc-900 font-bold price-tag">
                                ₱{{ activePrice }}
                            </span>
                            <span v-if="selectedVariant?.sku" class="text-xs text-zinc-400">
                                SKU {{ selectedVariant.sku }}
                            </span>
                        </div>
                    </div>

                    <div v-if="variants.length > 0" class="flex flex-col space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="uppercase tracking-[0.2em] text-zinc-400 font-semibold">Option</span>
                            <span class="text-zinc-700">{{ selectedVariant?.variant_label }}</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="variant in variants"
                                :key="variant.id"
                                type="button"
                                @click="selectedVariantId = variant.id"
                                :class="[
                                    'px-4 py-2.5 text-xs border border-solid rounded-none transition-colors',
                                    selectedVariant?.id === variant.id
                                        ? 'border-zinc-900 bg-zinc-900 text-white font-medium'
                                        : variant.stock_qty <= 0
                                            ? 'border-zinc-200 bg-zinc-50 text-zinc-400 hover:border-zinc-400'
                                            : 'border-zinc-300 bg-white text-zinc-900 hover:border-zinc-900',
                                ]">
                                {{ variant.variant_label }}
                            </button>
                        </div>
                    </div>

                    <!-- Stock Status Line -->
                    <p class="text-xs text-zinc-500">
                        {{ availabilityText }}
                    </p>

                    <button
                        type="button"
                        @click="scrollToInquiry"
                        class="w-full py-4 px-6 bg-zinc-900 hover:bg-black text-white text-xs uppercase tracking-[0.25em] font-medium rounded-none transition-colors">
                        Inquire About This Piece
                    </button>

                    <div v-if="product.description" class="border-t border-zinc-100 pt-6 space-y-2">
                        <h2 class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                            Details
                        </h2>
                        <p class="text-sm text-zinc-600 font-normal leading-relaxed whitespace-pre-line">
                            {{ product.description }}
                        </p>
                    </div>

                    <div v-if="product.attributes && product.attributes.length > 0" class="border-t border-zinc-100 pt-6 space-y-3">
                        <h2 class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                            Specifications
                        </h2>
                        <div class="divide-y divide-zinc-100 text-xs">
                            <div
                                v-for="attr in product.attributes"
                                :key="attr.id"
                                class="flex justify-between py-2">
                                <span class="text-zinc-400">{{ attr.name }}</span>
                                <span class="text-zinc-900">{{ attr.value }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="product.tags && product.tags.length > 0" class="flex flex-wrap gap-4 pt-2">
                        <Link
                            v-for="t in product.tags"
                            :key="t.id"
                            :href="route('storefront.index', { tag: t.slug })"
                            class="text-xs text-zinc-400 hover:text-black border-b border-transparent hover:border-black pb-0.5 transition-colors">
                            #{{ t.name }}
                        </Link>
                    </div>
                </div>
            </div>

            <section id="inquiry-section" class="border-t border-zinc-100 pt-14 scroll-mt-24">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                    <div class="lg:col-span-5 flex flex-col space-y-[18px]">
                        <span class="text-[12px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-none">
                            Direct Inquiry
                        </span>
                        <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                            Request Availability
                        </h2>
                        <p class="text-xs text-zinc-500 font-normal leading-relaxed max-w-md">
                            Submit your contact details and preferred quantity for
                            <span class="text-zinc-900 font-medium">{{ product.name }}</span>
                            <span v-if="selectedVariant"> ({{ selectedVariant.variant_label }})</span>.
                            Our team will confirm stock allocation and dispatch details directly.
                        </p>

                        <div class="border-t border-zinc-100 pt-4 space-y-1.5 text-xs text-zinc-500">
                            <div>
                                <span class="text-zinc-400">Selected Piece:</span>
                                <span class="text-zinc-900 ml-1.5">{{ product.name }}</span>
                            </div>
                            <div v-if="selectedVariant">
                                <span class="text-zinc-400">Option / SKU:</span>
                                <span class="text-zinc-900 ml-1.5">
                                    {{ selectedVariant.variant_label }} ({{ selectedVariant.sku }})
                                </span>
                            </div>
                            <div>
                                <span class="text-zinc-400">Unit Price:</span>
                                <span class="text-zinc-900 font-bold ml-1.5 price-tag">₱{{ activePrice }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div
                            v-if="inquirySubmitted"
                            class="border border-zinc-900 rounded-none p-8 sm:p-10 space-y-5 bg-white">
                            <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold block">
                                Inquiry Received
                            </span>
                            <h3 class="text-lg uppercase tracking-wide text-zinc-900 font-bold">
                                Thank you for reaching out
                            </h3>
                            <p class="text-xs text-zinc-600 font-normal leading-relaxed max-w-lg">
                                Your inquiry for <span class="text-zinc-900 font-medium">{{ product.name }}</span>
                                <span v-if="selectedVariant"> ({{ selectedVariant.variant_label }})</span>
                                has been logged. A member of our team will respond to your email shortly.
                            </p>
                            <div class="pt-2">
                                <button
                                    type="button"
                                    @click="resetInquiryState"
                                    class="text-xs text-zinc-900 font-medium border-b border-black pb-0.5">
                                    Send another inquiry
                                </button>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitInquiry" class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                        Full Name *
                                    </label>
                                    <input
                                        v-model="inquiryForm.customer_name"
                                        type="text"
                                        required
                                        placeholder="Juan Dela Cruz"
                                        class="w-full py-3 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors"
                                    />
                                    <p v-if="inquiryForm.errors.customer_name" class="text-[11px] text-rose-600">
                                        {{ inquiryForm.errors.customer_name }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                        Email Address *
                                    </label>
                                    <input
                                        v-model="inquiryForm.customer_email"
                                        type="email"
                                        required
                                        placeholder="name@example.com"
                                        class="w-full py-3 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors"
                                    />
                                    <p v-if="inquiryForm.errors.customer_email" class="text-[11px] text-rose-600">
                                        {{ inquiryForm.errors.customer_email }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2 space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                        Phone / Viber (Optional)
                                    </label>
                                    <input
                                        v-model="inquiryForm.customer_phone"
                                        type="text"
                                        placeholder="+63 900 000 0000"
                                        class="w-full py-3 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors"
                                    />
                                    <p v-if="inquiryForm.errors.customer_phone" class="text-[11px] text-rose-600">
                                        {{ inquiryForm.errors.customer_phone }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                        Quantity *
                                    </label>
                                    <input
                                        v-model.number="inquiryForm.quantity"
                                        type="number"
                                        min="1"
                                        max="999"
                                        required
                                        class="w-full py-3 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors"
                                    />
                                    <p v-if="inquiryForm.errors.quantity" class="text-[11px] text-rose-600">
                                        {{ inquiryForm.errors.quantity }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                    Message *
                                </label>
                                <textarea
                                    v-model="inquiryForm.message"
                                    rows="4"
                                    required
                                    placeholder="Ask about sizing, stock availability, bulk pricing, or dispatch options..."
                                    class="w-full py-3 px-3.5 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 transition-colors resize-none"
                                ></textarea>
                                <p v-if="inquiryForm.errors.message" class="text-[11px] text-rose-600">
                                    {{ inquiryForm.errors.message }}
                                </p>
                            </div>

                            <div class="pt-1 flex items-center justify-between gap-4">
                                <span class="text-[11px] text-zinc-400">
                                    Response within 1 business day.
                                </span>

                                <button
                                    type="submit"
                                    :disabled="inquiryForm.processing"
                                    class="px-8 py-4 bg-zinc-900 hover:bg-black text-white text-xs uppercase tracking-[0.25em] font-medium rounded-none disabled:opacity-50 transition-colors">
                                    {{ inquiryForm.processing ? 'Sending...' : 'Submit Inquiry' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section v-if="relatedProducts.length > 0" class="border-t border-zinc-100 pt-14 space-y-6">
                <div class="flex flex-col space-y-[18px]">
                    <span class="text-[12px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-none">
                        Discover More
                    </span>
                    <div class="flex items-baseline space-x-6">
                        <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                            Related Pieces
                        </h2>
                        <Link
                            :href="route('storefront.index')"
                            class="text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5">
                            View all
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-1 gap-y-10 lg:gap-x-1.5">
                    <ProductCard
                        v-for="item in relatedProducts"
                        :key="item.id"
                        :product="item"
                    />
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>