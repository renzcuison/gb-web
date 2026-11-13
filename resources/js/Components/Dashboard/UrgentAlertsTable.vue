<script setup>
import { Link } from '@inertiajs/vue3'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'

defineProps({
    alerts: { type: Array, default: () => [] }
})
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
            <h2 class="text-sm font-medium text-slate-800 dark:text-zinc-200">Urgent System Alerts</h2>
            <Button variant="ghost" size="sm" as-child
                class="text-xs font-normal text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 -mr-2">
                <Link href="/inventory/alerts">View all →</Link>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow class="border-b border-slate-100 dark:border-zinc-800/80 hover:bg-transparent">
                    <TableHead class="px-5 text-xs font-medium text-slate-400 dark:text-zinc-500">Status</TableHead>
                    <TableHead class="px-5 text-xs font-medium text-slate-400 dark:text-zinc-500">Item / SKU</TableHead>
                    <TableHead class="px-5 text-xs font-medium text-slate-400 dark:text-zinc-500">Details</TableHead>
                    <TableHead class="px-5 text-right text-xs font-medium text-slate-400 dark:text-zinc-500">Stock State
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="alert in alerts" :key="alert.id"
                    class="border-b border-slate-100 dark:border-zinc-800/50 hover:bg-slate-50/50 dark:hover:bg-zinc-800/40">
                    <TableCell class="px-5 py-3">
                        <span class="text-xs font-medium"
                            :class="alert.is_resolved ? 'text-slate-400 dark:text-zinc-500' : 'text-rose-600 dark:text-rose-400'">
                            {{ alert.is_resolved ? 'Resolved' : 'Active Warning' }}
                        </span>
                    </TableCell>
                    <TableCell class="px-5 py-3">
                        <div class="font-medium text-xs text-slate-800 dark:text-zinc-200">
                            {{ alert.product_variant?.product?.name || 'Deleted Product' }}
                        </div>
                        <div class="text-[11px] font-mono text-slate-400 dark:text-zinc-500 mt-0.5">
                            {{ alert.product_variant?.sku }}
                            <span v-if="alert.product_variant?.variant_label">({{ alert.product_variant.variant_label
                                }})</span>
                        </div>
                    </TableCell>
                    <TableCell class="px-5 py-3 text-slate-500 dark:text-zinc-400 text-xs font-normal">
                        Dropped to or below threshold of <span class="font-medium text-slate-700 dark:text-zinc-300">{{
                            alert.threshold_reached }}</span> units.
                    </TableCell>
                    <TableCell
                        class="px-5 py-3 text-right font-mono font-medium text-rose-600 dark:text-rose-400 text-xs">
                        {{ alert.product_variant?.stock_qty ?? 0 }} Left
                    </TableCell>
                </TableRow>
                <TableRow v-if="alerts.length === 0">
                    <TableCell colspan="4"
                        class="h-28 text-center text-slate-400 dark:text-zinc-500 text-xs font-normal">
                        No system hardware or stock level faults detected.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>