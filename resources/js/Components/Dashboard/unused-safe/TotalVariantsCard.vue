<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    count: { type: Number, default: 0 },
    variants: { type: Array, default: () => [] }
})
</script>

<template>
    <div
        class="p-5 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 flex flex-col justify-between min-h-[280px]">
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800/80">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">Total Variants</span>
                    <span
                        class="text-xs font-medium text-slate-600 dark:text-zinc-400 bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md font-mono">
                        {{ count }}
                    </span>
                </div>
                <Link href="/inventory"
                    class="text-xs font-normal text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                    View all →
                </Link>
            </div>

            <!-- Item List -->
            <div class="mt-3 h-52 overflow-y-auto pr-1 space-y-2.5 divide-y divide-slate-100 dark:divide-zinc-800/50">
                <div v-for="item in variants" :key="item.id" class="flex items-center justify-between pt-2 first:pt-0">
                    <div class="min-w-0 pr-3">
                        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 truncate">
                            {{ item.product?.name || item.sku }}
                        </p>
                        <p class="text-[11px] font-mono text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                            {{ item.sku }} <span v-if="item.variant_label">({{ item.variant_label }})</span>
                        </p>
                    </div>
                    <span
                        class="text-xs font-mono font-normal text-slate-600 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-800/60 px-2 py-0.5 rounded flex-shrink-0">
                        {{ item.stock_qty }} units
                    </span>
                </div>

                <div v-if="variants.length === 0"
                    class="flex items-center justify-center h-full text-xs text-slate-400 dark:text-zinc-500">
                    No active variants found.
                </div>
            </div>
        </div>
    </div>
</template>