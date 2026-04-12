<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="route('home')" class="group inline-flex items-center space-x-1.5">
        <span class="text-lg font-bold tracking-[0.2em] uppercase text-zinc-900">
            GreatBuy
        </span>
        <span
            class="text-lg font-light tracking-[0.2em] uppercase text-zinc-400 group-hover:text-zinc-600 transition-colors duration-300">
            Originals
        </span>
    </Link>
</template>