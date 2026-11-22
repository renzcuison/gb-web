<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <div class="border-b border-slate-200 dark:border-zinc-800 mb-6">
        <nav class="-mb-px flex space-x-6 text-sm font-medium overflow-x-auto">
            <Link :href="route('inventory.index')" :class="[
                page.component === 'Inventory/Index'
                    ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-zinc-400 dark:hover:text-zinc-200',
                'flex items-center pb-3 border-b-2 transition-colors whitespace-nowrap'
            ]">
                <span>Stock Inventory</span>
            </Link>

            <Link :href="route('inventory.movements.index')" :class="[
                page.component === 'Inventory/Movements'
                    ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-zinc-400 dark:hover:text-zinc-200',
                'flex items-center pb-3 border-b-2 transition-colors whitespace-nowrap'
            ]">
                <span>Movement Logs</span>
            </Link>

            <Link :href="route('categories.index')" :class="[
                page.component.startsWith('Categories')
                    ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-zinc-400 dark:hover:text-zinc-200',
                'flex items-center pb-3 border-b-2 transition-colors whitespace-nowrap'
            ]">
                <span>Categories</span>
            </Link>

            <Link :href="route('brands.index')" :class="[
                page.component.startsWith('Brands')
                    ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-zinc-400 dark:hover:text-zinc-200',
                'flex items-center pb-3 border-b-2 transition-colors whitespace-nowrap'
            ]">
                <span>Brands</span>
            </Link>

            <Link :href="route('tags.index')" :class="[
                page.component.startsWith('Tags')
                    ? 'border-slate-900 text-slate-900 dark:border-zinc-100 dark:text-zinc-100 font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-zinc-400 dark:hover:text-zinc-200',
                'flex items-center pb-3 border-b-2 transition-colors whitespace-nowrap'
            ]">
                <span>Tags</span>
            </Link>
        </nav>
    </div>
</template>