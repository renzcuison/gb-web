<script setup>
import { Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
</script>

<template>
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-slate-200/80 dark:border-zinc-800">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">Dashboard</h1>

        <div class="flex items-center gap-2.5">
            <Button size="sm" as-child
                class="text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 px-3.5">
                <Link href="/inventory">+ Update Stock</Link>
            </Button>

            <Button size="sm" variant="outline" as-child
                class="text-xs font-medium border-slate-200 dark:border-zinc-800 bg-white hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/80 text-slate-700 dark:text-zinc-300 px-3.5">
                <Link href="/announcements">Announcements</Link>
            </Button>
        </div>
    </div>
</template>