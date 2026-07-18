<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-2">Admin Control Center</h1>
                    <p class="text-muted-foreground text-zinc-600 dark:text-zinc-400">
                        You are logged in with administrative access privileges.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>