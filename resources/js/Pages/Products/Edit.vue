<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductGeneralForm from './Components/ProductGeneralForm.vue'
import ProductMediaUploader from './Components/ProductMediaUploader.vue'
import ProductVariantBuilder from './Components/ProductVariantBuilder.vue'
import { Button } from '@/Components/ui/button'
import { ArrowLeft, Archive } from 'lucide-vue-next'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    brands: {
        type: Array,
        default: () => []
    },
    tags: {
        type: Array,
        default: () => []
    }
})

const initialPrimaryMedia = props.product.media?.find((m) => m.is_primary) || props.product.media?.[0] || null

const form = useForm({
    name: props.product.name,
    category_id: props.product.category_id,
    brand_id: props.product.brand_id,
    description: props.product.description || '',
    cost_price: props.product.cost_price !== null ? Number(props.product.cost_price) : null,
    price: Number(props.product.price),
    is_active: Boolean(props.product.is_active),
    tags: (props.product.tags || []).map((t) => t.id),
    new_images: [],
    deleted_media_ids: [],
    primary_media_id: initialPrimaryMedia ? initialPrimaryMedia.id : null,
    primary_new_image_index: null,
    variants: (props.product.variants || []).map((v) => ({
        id: v.id,
        variant_label: v.variant_label,
        sku: v.sku,
        price_override: v.price_override !== null ? Number(v.price_override) : null,
        stock_qty: v.stock_qty,
        low_stock_threshold: v.low_stock_threshold
    }))
})

const handleSubmit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put'
    })).post(route('products.update', props.product.id))
}

const archiveProduct = () => {
    if (confirm(`Archive "${props.product.name}"? It will be hidden from the storefront and active inventory.`)) {
        router.delete(route('products.destroy', props.product.id))
    }
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <div class="max-w-5xl mx-auto space-y-8 pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <Link :href="route('inventory.index')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 transition-colors">
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Back to Inventory</span>
                </Link>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-zinc-100">
                    Edit {{ product.name }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-zinc-400">
                    Update product details, photos, tags, and variant configurations.
                </p>
            </div>

            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="archiveProduct"
                class="gap-1.5 text-rose-600 border-rose-200 hover:bg-rose-50 dark:border-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-950/30 self-start sm:self-auto"
            >
                <Archive class="h-4 w-4" />
                <span>Archive Product</span>
            </Button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-8">
            <ProductGeneralForm :form="form" :categories="props.categories" :brands="props.brands" :tags="props.tags" />

            <ProductMediaUploader :form="form" :is-edit="true" :existing-media="product.media || []" />

            <ProductVariantBuilder :form="form" :is-edit="true" />

            <div class="flex items-center justify-end gap-3 pt-4">
                <Button type="button" variant="ghost" as-child
                    class="dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
                    <Link :href="route('inventory.index')">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>