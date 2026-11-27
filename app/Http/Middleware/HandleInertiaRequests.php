<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Inquiry;
use App\Models\InventoryAlert;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'notifications' => fn () => $request->user()
                ? $request->user()->notifications()
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(fn ($n) => [
                        'id'               => $n->id,
                        'title'            => $n->data['title'] ?? 'Notification',
                        'message'          => $n->data['message'] ?? '',
                        'type'             => $n->data['type'] ?? 'info',
                        'link'             => $n->data['link'] ?? null,
                        'created_at_human' => $n->created_at->diffForHumans(),
                        'read_at'          => $n->read_at ? $n->read_at->toISOString() : null,
                    ])
                : [],

            'system_settings' => fn () => \App\Http\Controllers\SettingsController::all(),

            'recent_activities' => fn () => $request->user()?->isAdmin()
                ? InventoryMovement::with(['variant.product', 'user'])
                    ->latest()
                    ->take(15)
                    ->get()
                    ->map(function ($movement) {
                        $product = $movement->variant?->product;
                        $productName = $product?->name ?? 'Item';
                        $variantLabel = $movement->variant?->variant_label;
                        $sku = $movement->variant?->sku;
                        $action = $movement->type === 'in' ? 'Added' : 'Removed';
                        $suffix = $variantLabel ? " ({$variantLabel})" : '';

                        return [
                            'id' => $movement->id,
                            'type' => $movement->type,
                            'quantity' => $movement->quantity,
                            'product_name' => $productName,
                            'variant_label' => $variantLabel,
                            'sku' => $sku,
                            'reason' => $movement->reason,
                            'description' => "{$action} {$movement->quantity} unit(s) of {$productName}{$suffix}",
                            'user_name' => $movement->user?->name ?? 'System',
                            'created_at_human' => $movement->created_at?->diffForHumans() ?? 'Just now',
                            'href' => $sku
                                ? route('inventory.index', ['search' => $sku])
                                : route('inventory.movements.index'),
                        ];
                    })
                : [],

            'announcements' => fn () => Announcement::where('is_active', true)
                ->orderByDesc('priority')
                ->latest()
                ->get(['id', 'content', 'priority']),

            'storefront_menu' => fn () => (function () {
                $verticalKeywords = [
                    'apparel-footwear' => ['shoe', 'sneaker', 'footwear', 'sandal', 'boot', 'cap', 'hat', 'headwear', 'cloth', 'apparel', 'shirt', 'hoodie', 'jacket', 'activewear'],
                    'bags-accessories' => ['bag', 'tote', 'backpack', 'crossbody', 'wallet', 'cardholder', 'watch', 'accessor', 'belt', 'eyewear', 'sunglass'],
                    'beauty-fragrance' => ['perfume', 'fragrance', 'mist', 'decant', 'cologne', 'lotion', 'cream', 'beauty', 'skincare', 'body', 'bath', 'cosmetic', 'grooming'],
                    'home-kitchen'     => ['kitchen', 'frying', 'pan', 'cookware', 'pot', 'plate', 'dinnerware', 'bowl', 'mug', 'drinkware', 'pantry', 'wellness', 'home', 'furniture', 'electronic', 'appliance', 'living'],
                ];

                $activeCategories = Category::whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);

                $activeBrands = Brand::whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);

                $activeTags = Tag::whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);

                $verticals = collect($verticalKeywords)->mapWithKeys(function (array $keywords, string $verticalSlug) use ($activeCategories) {
                    $matchedCategories = $activeCategories->filter(function ($cat) use ($keywords) {
                        $haystack = strtolower($cat->name . ' ' . $cat->slug);
                        foreach ($keywords as $kw) {
                            if (str_contains($haystack, $kw)) {
                                return true;
                            }
                        }
                        return false;
                    })->values();

                    $categoryIds = $matchedCategories->pluck('id');

                    $matchedBrands = $categoryIds->isNotEmpty()
                        ? Brand::whereHas('products', fn ($q) => $q->where('is_active', true)->whereIn('category_id', $categoryIds))
                            ->withCount(['products' => fn ($q) => $q->where('is_active', true)->whereIn('category_id', $categoryIds)])
                            ->orderByDesc('products_count')
                            ->orderBy('name')
                            ->take(5)
                            ->get(['id', 'name', 'slug'])
                        : collect();

                    return [
                        $verticalSlug => [
                            'categories' => $matchedCategories,
                            'brands'     => $matchedBrands,
                        ],
                    ];
                })->all();

                return [
                    'categories'        => $activeCategories,
                    'brands'            => $activeBrands,
                    'tags'              => $activeTags,
                    'verticals'         => $verticals,
                    'featured_products' => Product::with([
                        'category:id,name,slug',
                        'brand:id,name,slug',
                        'tags:id,name,slug',
                        'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
                    ])
                        ->where('is_active', true)
                        ->latest()
                        ->take(24)
                        ->get(['id', 'category_id', 'brand_id', 'name', 'slug', 'description', 'price']),
                ];
            })(),

            'admin_badges' => fn () => $request->user()?->isAdmin()
                ? [
                    'unresolved_alerts' => \App\Models\ProductVariant::whereHas('product')
                        ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                        ->count(),
                    'new_inquiries' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
                ]
                : [
                    'unresolved_alerts' => 0,
                    'new_inquiries' => 0,
                ],
        ]);
    }
}