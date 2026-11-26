<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public const CACHE_KEY = 'system_settings';

    public static function defaults(): array
    {
        return [
            'default_low_stock_threshold' => 5,
            'allow_negative_stock' => false,
            'require_adjustment_reason' => true,
            'currency_symbol' => '₱',

            'auto_resolve_alerts_on_restock' => true,
            'alert_on_zero_stock' => true,
            'alert_on_low_stock' => true,

            'storefront_inquiries_enabled' => true,
            'auto_deduct_stock_on_fulfill' => true,
            'hide_out_of_stock_storefront' => false,
            'inquiry_recipient_email' => config('mail.from.address', 'admin@example.com'),

            'sku_prefix' => 'SKU-',
            'export_date_format' => 'Y-m-d',
        ];
    }

    public static function all(): array
    {
        return array_merge(
            self::defaults(),
            Cache::get(self::CACHE_KEY, [])
        );
    }

    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => self::all(),
            'variantsCount' => ProductVariant::count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated =$request->validate([
            'default_low_stock_threshold' => ['required', 'integer', 'min:0', 'max:10000'],
            'allow_negative_stock' => ['required', 'boolean'],
            'require_adjustment_reason' => ['required', 'boolean'],
            'currency_symbol' => ['required', 'string', 'max:5'],

            'auto_resolve_alerts_on_restock' => ['required', 'boolean'],
            'alert_on_zero_stock' => ['required', 'boolean'],
            'alert_on_low_stock' => ['required', 'boolean'],

            'storefront_inquiries_enabled' => ['required', 'boolean'],
            'auto_deduct_stock_on_fulfill' => ['required', 'boolean'],
            'hide_out_of_stock_storefront' => ['required', 'boolean'],
            'inquiry_recipient_email' => ['required', 'email', 'max:150'],

            'sku_prefix' => ['nullable', 'string', 'max:20'],
            'export_date_format' => ['required', 'in:Y-m-d,m/d/Y,d/m/Y'],
            'apply_threshold_to_all' => ['nullable', 'boolean'],
        ]);

        $applyToAll = (bool) ($validated['apply_threshold_to_all'] ?? false);
        unset($validated['apply_threshold_to_all']);

        $validated['sku_prefix'] =$validated['sku_prefix'] ?? '';

        Cache::forever(self::CACHE_KEY, $validated);

        if ($applyToAll) {
            ProductVariant::query()->update([
                'low_stock_threshold' => $validated['default_low_stock_threshold'],
            ]);
        }

        return back()->with('success', $applyToAll
            ? 'IMS preferences saved and low-stock threshold applied to all variants.'
            : 'IMS preferences saved.');
    }
}