import { computed, ref, watch } from 'vue';

export const SHOW_PLACEHOLDER_IMAGES = true;

const PLACEHOLDER_SETS = {
    apparel: [
        '/images/product-placeholders/apparel_1.webp',
        '/images/product-placeholders/apparel_2.webp',
    ],
    accessories: [
        '/images/product-placeholders/accessories_1.webp',
        '/images/product-placeholders/accessories_2.webp',
    ],
    beauty: [
        '/images/product-placeholders/beauty_1.webp',
        '/images/product-placeholders/beauty_2.webp',
    ],
    home: [
        '/images/product-placeholders/home_1.webp',
        '/images/product-placeholders/home_2.webp',
    ],
    default: [
        '/images/product-placeholders/generic_1.webp',
        '/images/product-placeholders/generic_2.webp',
    ],
};

const BUCKET_RULES = [
    {
        bucket: 'apparel',
        keywords: [
            'apparel', 'clothing', 'top', 'shirt', 'tee', 'blouse', 'sweater',
            'hoodie', 'jacket', 'outerwear', 'coat', 'dress', 'pant', 'jean',
            'short', 'skirt', 'bottom', 'activewear', 'loungewear', 'sleepwear',
            'shoe', 'footwear', 'sneaker', 'sandal', 'boot', 'cap', 'hat', 'headwear',
        ],
    },
    {
        bucket: 'accessories',
        keywords: [
            'accessor', 'bag', 'tote', 'backpack', 'crossbody', 'wallet', 'cardholder',
            'pouch', 'sunglass', 'eyewear', 'watch', 'jewel', 'belt', 'scarf',
        ],
    },
    {
        bucket: 'beauty',
        keywords: [
            'beauty', 'fragrance', 'perfume', 'mist', 'decant', 'cologne', 'scent',
            'skincare', 'serum', 'cleanser', 'moisturizer', 'makeup', 'cosmetic',
            'lip', 'hair', 'body', 'bath', 'lotion', 'cream', 'grooming', 'wellness',
        ],
    },
    {
        bucket: 'home',
        keywords: [
            'home', 'living', 'decor', 'kitchen', 'cookware', 'frying', 'pan', 'pot',
            'plate', 'dinnerware', 'bowl', 'mug', 'drinkware', 'tumbler', 'pantry',
            'bedding', 'towel', 'candle', 'diffuser', 'storage', 'lifestyle',
            'gadget', 'electronic', 'appliance', 'furniture',
        ],
    },
];

function resolveBucket(product) {
    const raw = [
        product?.category?.slug,
        product?.category?.name,
        product?.category_slug,
        product?.category_name,
        product?.subtitle,
    ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();

    if (!raw) return 'default';

    for (const rule of BUCKET_RULES) {
        if (rule.keywords.some((kw) => raw.includes(kw))) {
            return rule.bucket;
        }
    }

    return 'default';
}

function stableIndex(product, length) {
    const id = Number(product?.id);
    if (Number.isFinite(id) && id >= 0) {
        return id % length;
    }

    const seed = String(product?.slug || product?.name || '');
    let hash = 0;
    for (let i = 0; i < seed.length; i++) {
        hash = (hash * 31 + seed.charCodeAt(i)) >>> 0;
    }
    return hash % length;
}

export function resolveProductImages(product) {
    const realPrimary = product?.img || product?.image || product?.media?.[0]?.url || null;
    const realHover = product?.imgHover || product?.media?.[1]?.url || null;

    if (realPrimary) {
        return { primaryImg: realPrimary, hoverImg: realHover, isPlaceholder: false };
    }

    if (!SHOW_PLACEHOLDER_IMAGES) {
        return { primaryImg: null, hoverImg: null, isPlaceholder: false };
    }

    const bucket = resolveBucket(product);
    const set = PLACEHOLDER_SETS[bucket] || PLACEHOLDER_SETS.default;
    const idx = stableIndex(product, set.length);

    return {
        primaryImg: set[idx],
        hoverImg: set.length > 1 ? set[(idx + 1) % set.length] : null,
        isPlaceholder: true,
    };
}

export function useProductImage(productRef) {
    const realBroken = ref(false);
    const placeholderStage = ref(0);
    const hoverBroken = ref(false);

    const getProduct = () =>
        typeof productRef === 'function' ? productRef() : productRef?.value ?? productRef;

    watch(
        () => getProduct()?.id,
        () => {
            realBroken.value = false;
            placeholderStage.value = 0;
            hoverBroken.value = false;
        }
    );

    const realPrimary = computed(() => {
        const p = getProduct();
        return p?.img || p?.image || p?.media?.[0]?.url || null;
    });

    const realHover = computed(() => {
        const p = getProduct();
        return p?.imgHover || p?.media?.[1]?.url || null;
    });

    const placeholderPair = computed(() => {
        const p = getProduct();
        const bucket = resolveBucket(p);
        const set = PLACEHOLDER_SETS[bucket] || PLACEHOLDER_SETS.default;
        const idx = stableIndex(p, set.length);
        return {
            primary: set[idx],
            secondary: set.length > 1 ? set[(idx + 1) % set.length] : null,
        };
    });

    const usingReal = computed(() => Boolean(realPrimary.value) && !realBroken.value);

    const primaryImg = computed(() => {
        if (usingReal.value) return realPrimary.value;
        if (!SHOW_PLACEHOLDER_IMAGES || placeholderStage.value >= 2) return null;
        if (placeholderStage.value === 1) return PLACEHOLDER_SETS.default[0];
        return placeholderPair.value.primary;
    });

    const hoverImg = computed(() => {
        if (hoverBroken.value) return null;
        if (usingReal.value) return realHover.value;
        if (!SHOW_PLACEHOLDER_IMAGES || placeholderStage.value >= 1) return null;
        return placeholderPair.value.secondary;
    });

    const isPlaceholder = computed(() => Boolean(primaryImg.value) && !usingReal.value);

    const onPrimaryError = () => {
        if (usingReal.value) {
            realBroken.value = true;
        } else if (placeholderStage.value === 0) {
            placeholderStage.value = 1;
        } else {
            placeholderStage.value = 2;
        }
    };

    const onHoverError = () => {
        hoverBroken.value = true;
    };

    return {
        primaryImg,
        hoverImg,
        isPlaceholder,
        onPrimaryError,
        onHoverError,
    };
}