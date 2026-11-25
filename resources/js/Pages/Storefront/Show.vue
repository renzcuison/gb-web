<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AnnouncementBar from '@/Pages/Storefront/AnnouncementBar.vue';
import Header from '@/Pages/Storefront/Header/Index.vue';
import Footer from '@/Pages/Storefront/Footer.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { resolveProductImages } from '@/Composables/useProductImage';

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
});

const mediaList = computed(() => {
    if (props.product.media && props.product.media.length > 0) {
        return props.product.media;
    }
    const { primaryImg, hoverImg } = resolveProductImages(props.product);
    return [primaryImg, hoverImg]
        .filter(Boolean)
        .map((url, idx) => ({ id: `placeholder-${idx}`, url }));
});
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

const customerAttributes = computed(() => {
    const blockedKeywords = ['sku', 'batch', 'eta', 'supplier', 'cost'];
    return (props.product.attributes || []).filter((attr) => {
        const label = String(attr.name || '').trim().toLowerCase();
        return !blockedKeywords.some((keyword) => label.includes(keyword));
    });
});

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

        <main class="flex-1 max-w-full w-full mx-auto px-6 lg:px-10 pt-4 pb-20 space-y-5">
            <nav class="flex items-center space-x-2 text-xs text-zinc-400">
                <Link :href="route('storefront.index')" class="text-zinc-600 hover:text-black">
                    Storefront
                </Link>
                <span v-if="product.category">/</span>
                <Link v-if="product.category" :href="route('storefront.index', { category: product.category.slug })"
                    class="hover:text-black">
                    {{ product.category.name }}
                </Link>
                <span>/</span>
                <span class="text-zinc-900 truncate">{{ product.name }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-stretch pb-8">
                <div class="flex flex-col sm:flex-row gap-2.5 items-start shrink-0">
                    <div class="w-full sm:w-auto sm:h-[calc(100dvh-185px)] sm:min-h-[480px] aspect-[4/5] bg-zinc-100 rounded-[1px] overflow-hidden shrink-0">
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

                    <div
                        v-if="mediaList.length > 1"
                        class="flex sm:flex-col gap-2 overflow-x-auto sm:overflow-y-auto sm:max-h-[calc(100dvh-185px)] shrink-0">
                        <button
                            v-for="(item, idx) in mediaList"
                            :key="item.id"
                            type="button"
                            @click="activeImageIndex = idx"
                            :class="[
                                'w-14 h-[70px] sm:w-16 sm:h-20 bg-zinc-100 rounded-[1px] overflow-hidden shrink-0 cursor-pointer',
                                activeImageIndex === idx
                                    ? 'ring-1 ring-zinc-900 opacity-100'
                                    : 'opacity-50 hover:opacity-100',
                            ]">
                            <img :src="item.url" :alt="product.name" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 w-full flex justify-center lg:h-[calc(100dvh-185px)] lg:min-h-[480px] lg:overflow-y-auto">
                    <div class="w-full max-w-lg flex flex-col justify-between space-y-4 h-full">
                        <div class="flex flex-col space-y-3.5">
                            <div class="flex flex-col space-y-1.5">
                                <span
                                    class="text-[11px] uppercase tracking-[0.35em] text-zinc-400 font-bold leading-none">
                                    {{ product.brand?.name || product.category?.name || 'GreatBuy Originals' }}
                                </span>

                                <h1
                                    class="text-2xl sm:text-3xl uppercase tracking-wide text-zinc-900 font-bold leading-tight pt-0.5">
                                    {{ product.name }}
                                </h1>

                                <div class="pt-0.5">
                                    <span class="text-lg text-zinc-900 font-bold price-tag">
                                        ₱{{ activePrice }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="variants.length > 0" class="flex flex-col space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="uppercase tracking-[0.2em] text-zinc-400 font-semibold">Option</span>
                                    <span class="text-zinc-700">{{ selectedVariant?.variant_label }}</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button v-for="variant in variants" :key="variant.id" type="button"
                                        @click="selectedVariantId = variant.id" :class="[
                                            'px-3.5 py-1.5 text-xs border border-solid rounded-none cursor-pointer',
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

                            <p class="text-xs text-zinc-500">
                                {{ availabilityText }}
                            </p>

                            <div v-if="product.description" class="space-y-1">
                                <h2 class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                                    Details
                                </h2>
                                <p class="text-xs text-zinc-600 font-normal leading-relaxed whitespace-pre-line">
                                    {{ product.description }}
                                </p>
                            </div>

                            <div v-if="customerAttributes.length > 0" class="space-y-1.5">
                                <h2 class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold">
                                    Specifications
                                </h2>
                                <div class="space-y-1 text-xs">
                                    <div v-for="attr in customerAttributes" :key="attr.id"
                                        class="flex justify-between gap-4">
                                        <span class="text-zinc-400">{{ attr.name }}</span>
                                        <span class="text-zinc-900 text-right">{{ attr.value }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="product.tags && product.tags.length > 0" class="flex flex-wrap gap-3">
                                <Link v-for="t in product.tags" :key="t.id"
                                    :href="route('storefront.index', { tag: t.slug })"
                                    class="text-xs text-zinc-400 hover:text-black">
                                    #{{ t.name }}
                                </Link>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col justify-end space-y-3 pt-1">
                            <div class="space-y-0.5">
                                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold block">
                                    Inquire About This Piece
                                </span>
                                <p class="text-xs text-zinc-500">
                                    Confirm stock allocation and dispatch details for
                                    <span class="text-zinc-900 font-medium">{{ product.name }}</span>
                                    <span v-if="selectedVariant"> ({{ selectedVariant.variant_label }})</span>.
                                </p>
                            </div>

                            <div v-if="inquirySubmitted"
                                class="border border-zinc-900 rounded-none p-6 space-y-4 bg-white">
                                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-bold block">
                                    Inquiry Received
                                </span>
                                <h3 class="text-base uppercase tracking-wide text-zinc-900 font-bold">
                                    Thank you for reaching out
                                </h3>
                                <p class="text-xs text-zinc-600 font-normal leading-relaxed">
                                    Your inquiry for <span class="text-zinc-900 font-medium">{{ product.name }}</span>
                                    <span v-if="selectedVariant"> ({{ selectedVariant.variant_label }})</span>
                                    has been logged. A member of our team will respond to your email shortly.
                                </p>
                                <div>
                                    <button type="button" @click="resetInquiryState"
                                        class="text-xs text-zinc-900 font-medium hover:text-zinc-500 cursor-pointer">
                                        Send another inquiry
                                    </button>
                                </div>
                            </div>

                            <form v-else @submit.prevent="submitInquiry"
                                class="flex-1 flex flex-col justify-between space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label
                                            class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                            Full Name *
                                        </label>
                                        <input v-model="inquiryForm.customer_name" type="text" required
                                            placeholder="Juan Dela Cruz"
                                            class="w-full py-2 px-3 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0" />
                                        <p v-if="inquiryForm.errors.customer_name" class="text-[11px] text-rose-600">
                                            {{ inquiryForm.errors.customer_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-1">
                                        <label
                                            class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                            Email Address *
                                        </label>
                                        <input v-model="inquiryForm.customer_email" type="email" required
                                            placeholder="name@example.com"
                                            class="w-full py-2 px-3 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0" />
                                        <p v-if="inquiryForm.errors.customer_email" class="text-[11px] text-rose-600">
                                            {{ inquiryForm.errors.customer_email }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2 space-y-1">
                                        <label
                                            class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                            Phone / Viber (Optional)
                                        </label>
                                        <input v-model="inquiryForm.customer_phone" type="text"
                                            placeholder="+63 900 000 0000"
                                            class="w-full py-2 px-3 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0" />
                                        <p v-if="inquiryForm.errors.customer_phone" class="text-[11px] text-rose-600">
                                            {{ inquiryForm.errors.customer_phone }}
                                        </p>
                                    </div>

                                    <div class="space-y-1">
                                        <label
                                            class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                            Quantity *
                                        </label>
                                        <input v-model.number="inquiryForm.quantity" type="number" min="1" max="999"
                                            required
                                            class="w-full py-2 px-3 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-0" />
                                        <p v-if="inquiryForm.errors.quantity" class="text-[11px] text-rose-600">
                                            {{ inquiryForm.errors.quantity }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-1 flex flex-col space-y-1 min-h-[76px]">
                                    <label
                                        class="block text-[10px] uppercase tracking-[0.25em] text-zinc-400 font-bold">
                                        Message *
                                    </label>
                                    <textarea v-model="inquiryForm.message" required
                                        placeholder="Ask about sizing, stock availability, bulk pricing, or dispatch options..."
                                        class="w-full flex-1 py-2 px-3 text-xs border border-solid border-zinc-300 rounded-none bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-0 resize-none"></textarea>
                                    <p v-if="inquiryForm.errors.message" class="text-[11px] text-rose-600">
                                        {{ inquiryForm.errors.message }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[11px] text-zinc-400">
                                        Response within 1 business day.
                                    </span>

                                    <button type="submit" :disabled="inquiryForm.processing"
                                        class="px-7 py-3 bg-zinc-900 hover:bg-black text-white text-xs uppercase tracking-[0.25em] font-medium rounded-none disabled:opacity-50 cursor-pointer">
                                        {{ inquiryForm.processing ? 'Sending...' : 'Submit Inquiry' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <section v-if="relatedProducts.length > 0" class="pt-14 space-y-6">
                <div class="flex flex-col space-y-[18px]">
                    <span class="text-[12px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-none">
                        Discover More
                    </span>
                    <div class="flex items-baseline space-x-6">
                        <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                            Related Pieces
                        </h2>
                        <Link :href="route('storefront.index')"
                            class="text-[13px] text-zinc-900 font-medium hover:text-zinc-500">
                            View all
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-1 gap-y-10 lg:gap-x-1.5">
                    <ProductCard v-for="item in relatedProducts" :key="item.id" :product="item" />
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>