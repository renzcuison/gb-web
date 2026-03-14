<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex items-center">
        <Link :href="route('storefront.index')"
            class="text-2xl font-logo font-regular tracking-tightest">
            <span class="text-[rgb(3,161,252)]">GREATBUY</span>

            <span class="text-[rgb(3,161,252)] text-[10px] uppercase tracking-[0.2em] ml-0.5 opacity-90">Originals</span>
        </Link>
    </div>
</template>