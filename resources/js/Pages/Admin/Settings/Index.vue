<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { PackageCheck, BellRing, Store, FileSpreadsheet } from 'lucide-vue-next'

const props = defineProps({
    settings: { type: Object, required: true },
    variantsCount: { type: Number, default: 0 },
})

const page = usePage()

const form = useForm({
    default_low_stock_threshold: props.settings.default_low_stock_threshold ?? 5,
    allow_negative_stock: Boolean(props.settings.allow_negative_stock),
    require_adjustment_reason: Boolean(props.settings.require_adjustment_reason),
    currency_symbol: props.settings.currency_symbol ?? '₱',

    auto_resolve_alerts_on_restock: Boolean(props.settings.auto_resolve_alerts_on_restock),
    alert_on_zero_stock: Boolean(props.settings.alert_on_zero_stock),
    alert_on_low_stock: Boolean(props.settings.alert_on_low_stock),

    storefront_inquiries_enabled: Boolean(props.settings.storefront_inquiries_enabled),
    auto_deduct_stock_on_fulfill: Boolean(props.settings.auto_deduct_stock_on_fulfill),
    hide_out_of_stock_storefront: Boolean(props.settings.hide_out_of_stock_storefront),
    inquiry_recipient_email: props.settings.inquiry_recipient_email ?? '',

    sku_prefix: props.settings.sku_prefix ?? 'SKU-',
    export_date_format: props.settings.export_date_format ?? 'Y-m-d',
    apply_threshold_to_all: false,
})

const submit = () => {
    form.patch(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.apply_threshold_to_all = false
        },
    })
}
</script>

<template>

    <Head title="System Preferences" />

    <AdminLayout>
        <div class="w-full space-y-5">
            <!-- Page Header -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
                        System Preferences
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Configure stock rules, alert automation, inquiry fulfillment, and export defaults.
                    </p>
                </div>
            </div>

            <!-- Flash Banner -->
            <div v-if="page.props.flash?.success"
                class="rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                {{ page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- 1. Stock Control & Valuation -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                                Stock Control &amp; Valuation
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Control reorder thresholds, stock floor limits, and inventory movement accountability.
                            </p>
                        </div>
                        <PackageCheck class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Default Low-Stock Threshold (units)
                            </label>
                            <input v-model.number="form.default_low_stock_threshold" type="number" min="0" max="10000"
                                required
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                                Applied automatically when creating new product variants.
                            </p>
                            <p v-if="form.errors.default_low_stock_threshold"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ form.errors.default_low_stock_threshold }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Valuation Currency Symbol
                            </label>
                            <input v-model="form.currency_symbol" type="text" maxlength="5" required
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                                Used across dashboard stock valuation, product pricing, and exports.
                            </p>
                            <p v-if="form.errors.currency_symbol"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ form.errors.currency_symbol }}
                            </p>
                        </div>
                    </div>

                    <label
                        class="flex items-center gap-2.5 text-xs text-slate-600 dark:text-zinc-300 cursor-pointer select-none">
                        <input v-model="form.apply_threshold_to_all" type="checkbox"
                            class="rounded border-slate-300 dark:border-zinc-700 text-slate-900 focus:ring-0" />
                        <span>
                            Overwrite low-stock threshold on all
                            <span class="font-mono font-semibold">{{ variantsCount }}</span> existing variants
                        </span>
                    </label>

                    <div class="space-y-3.5 pt-1">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Allow Negative Stock Balances
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Permit outbound stock deductions even when available quantity reaches 0.
                                </div>
                            </div>

                            <button type="button" @click="form.allow_negative_stock = !form.allow_negative_stock"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.allow_negative_stock ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.allow_negative_stock ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Require Reason on Manual Stock Adjustments
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Enforce an audit note whenever staff log manual restocks, damage, or shrinkage.
                                </div>
                            </div>

                            <button type="button"
                                @click="form.require_adjustment_reason = !form.require_adjustment_reason"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.require_adjustment_reason ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.require_adjustment_reason ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Alert Automation -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                                Alert Automation
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Choose which inventory events generate alerts and when they clear.
                            </p>
                        </div>
                        <BellRing class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
                    </div>

                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Trigger Low-Stock Alerts
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Create an alert when a variant drops at or below its low-stock threshold.
                                </div>
                            </div>

                            <button type="button" @click="form.alert_on_low_stock = !form.alert_on_low_stock"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.alert_on_low_stock ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.alert_on_low_stock ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Trigger Out-of-Stock Alerts
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Flag high-priority alerts immediately when any variant hits 0 units.
                                </div>
                            </div>

                            <button type="button" @click="form.alert_on_zero_stock = !form.alert_on_zero_stock"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.alert_on_zero_stock ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.alert_on_zero_stock ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Auto-Resolve Alerts on Restock
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Automatically resolve open alerts once stock is replenished above threshold.
                                </div>
                            </div>

                            <button type="button"
                                @click="form.auto_resolve_alerts_on_restock = !form.auto_resolve_alerts_on_restock"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.auto_resolve_alerts_on_restock ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.auto_resolve_alerts_on_restock ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Storefront & Inquiry Fulfillment -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                                Storefront &amp; Inquiry Fulfillment
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Manage storefront stock visibility and how customer inquiries affect inventory.
                            </p>
                        </div>
                        <Store class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                            Inquiry Notification Email
                        </label>
                        <input v-model="form.inquiry_recipient_email" type="email" required
                            class="w-full sm:max-w-md rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                        <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                            Receives email notifications whenever a customer submits a product inquiry.
                        </p>
                        <p v-if="form.errors.inquiry_recipient_email"
                            class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                            {{ form.errors.inquiry_recipient_email }}
                        </p>
                    </div>

                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Auto-Deduct Stock When Fulfilling Inquiries
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Automatically subtract requested quantity and log a movement when an inquiry is
                                    fulfilled.
                                </div>
                            </div>

                            <button type="button"
                                @click="form.auto_deduct_stock_on_fulfill = !form.auto_deduct_stock_on_fulfill"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.auto_deduct_stock_on_fulfill ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.auto_deduct_stock_on_fulfill ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Accept Storefront Product Inquiries
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Allow customers to submit reservation or stock inquiries from product pages.
                                </div>
                            </div>

                            <button type="button"
                                @click="form.storefront_inquiries_enabled = !form.storefront_inquiries_enabled"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.storefront_inquiries_enabled ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.storefront_inquiries_enabled ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-medium text-slate-900 dark:text-zinc-100">
                                    Hide Out-of-Stock Products on Storefront
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Automatically conceal items from the public catalog when all variants reach 0 stock.
                                </div>
                            </div>

                            <button type="button"
                                @click="form.hide_out_of_stock_storefront = !form.hide_out_of_stock_storefront"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="form.hide_out_of_stock_storefront ? 'bg-slate-900 dark:bg-zinc-100' : 'bg-slate-200 dark:bg-zinc-700'">
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full transition duration-200"
                                    :class="form.hide_out_of_stock_storefront ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-300'" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. SKU & Export Formatting -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                                SKU &amp; Export Formatting
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Standardize auto-generated SKU codes and CSV/Excel audit log exports.
                            </p>
                        </div>
                        <FileSpreadsheet class="h-4 w-4 text-slate-400 dark:text-zinc-500 shrink-0 mt-0.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Default SKU Prefix
                            </label>
                            <input v-model="form.sku_prefix" type="text" maxlength="20" placeholder="SKU-"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                                Prepended when auto-generating variant SKUs in the product builder.
                            </p>
                            <p v-if="form.errors.sku_prefix" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ form.errors.sku_prefix }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Export Date Format
                            </label>
                            <select v-model="form.export_date_format"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 font-mono text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none">
                                <option value="Y-m-d">YYYY-MM-DD (2026-11-26)</option>
                                <option value="m/d/Y">MM/DD/YYYY (11/26/2026)</option>
                                <option value="d/m/Y">DD/MM/YYYY (26/11/2026)</option>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400 dark:text-zinc-500">
                                Applied to timestamps in Inventory and Movement Log exports.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <span v-if="form.recentlySuccessful"
                            class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            Saved.
                        </span>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-2 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Save Preferences' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>