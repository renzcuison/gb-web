<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome to GB-WEB" />

    <div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-6">
        <div v-if="canLogin" class="absolute top-0 right-0 p-6 text-right z-10">
            <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="font-semibold text-gray-400 hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Dashboard</Link
            >

            <template v-else>
                <Link
                    :href="route('login')"
                    class="font-semibold text-gray-400 hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                    >Log in</Link
                >

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="ml-4 font-semibold text-gray-400 hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                    >Register</Link
                >
            </template>
        </div>

        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-5xl font-bold mb-4 text-blue-500">GB-WEB</h1>
            <p class="text-xl text-gray-400">Inventory Management & E-commerce System</p>

            <div class="mt-8">
                <p class="text-sm text-gray-500 italic">System Status: Environment Ready (2026)</p>
            </div>
        </div>
    </div>
</template>