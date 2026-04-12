<script setup>
import Header from '@/Pages/Storefront/Header/Index.vue';
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <Header />

        <main class="flex-1 flex flex-col justify-center items-center px-6">
            <div class="w-full sm:max-w-md">
                <slot />
            </div>
        </main>
    </div>
</template>