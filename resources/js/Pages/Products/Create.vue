<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductGeneralForm from './Components/ProductGeneralForm.vue'
import ProductVariantBuilder from './Components/ProductVariantBuilder.vue'
import { Button } from '@/Components/ui/button'
import { ArrowLeft } from 'lucide-vue-next'

defineOptions({ layout: AdminLayout })

const props = defineProps({
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

const form = useForm({
    name: '',
    category_id: '',
    brand_id: '',
    description: '',
    cost_price: null,
    price: null,
    is_active: true,
    tags: [],
    variants: [
        {
            variant_label: 'Standard',
            sku: '',
            price_override: null,
            stock_qty: 0,
            low_stock_threshold: 10
        }
    ]
})

const handleSubmit = () => {
    form.post(route('products.store'))
}
</script>

<template>

    <Head title="Create Product Item Group" />

    <div class="max-w-5xl mx-auto space-y-8 pb-12">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <Link href="/inventory"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 transition-colors">
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Back to Inventory</span>
                </Link>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-zinc-100">
                    New Item Group
                </h1>
                <p class="text-sm text-slate-500 dark:text-zinc-400">
                    Create a parent product and configure its variants, opening stock, and reorder levels.
                </p>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-8">
            <ProductGeneralForm :form="form" :categories="props.categories" :brands="props.brands" />

            <ProductVariantBuilder :form="form" />

            <div class="flex items-center justify-end gap-3 pt-4">
                <Button type="button" variant="ghost" as-child
                    class="dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
                    <Link href="/inventory">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    Save Product Group
                </Button>
            </div>
        </form>
    </div>
</template>