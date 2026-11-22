<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Inquiry;
use App\Models\InventoryAlert;
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

            'recent_activities' => fn () => $request->user()?->isAdmin()
                ? InventoryMovement::with(['variant.product', 'user'])
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($movement) {
                        $productName = $movement->variant?->product?->name ?? 'Item';
                        $variantLabel = $movement->variant?->variant_label ? " ({$movement->variant->variant_label})" : '';
                        $action = $movement->type === 'in' ? 'Added' : 'Removed';

                        return [
                            'id' => $movement->id,
                            'description' => "{$action} {$movement->quantity} unit(s) of {$productName}{$variantLabel}",
                            'user_name' => $movement->user?->name ?? 'System',
                            'created_at_human' => $movement->created_at?->diffForHumans() ?? 'Just now',
                        ];
                    })
                : [],

            'announcements' => fn () => Announcement::where('is_active', true)
                ->orderByDesc('priority')
                ->latest()
                ->get(['id', 'content', 'priority']),

            'storefront_menu' => fn () => [
                'categories' => Category::whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
                'tags' => Tag::whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
                'featured_products' => Product::with([
                    'category:id,name,slug',
                    'brand:id,name,slug',
                    'tags:id,name,slug',
                    'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
                ])
                    ->where('is_active', true)
                    ->latest()
                    ->take(12)
                    ->get(['id', 'category_id', 'brand_id', 'name', 'slug', 'description', 'price']),
            ],

            'admin_badges' => fn () => $request->user()?->isAdmin()
                ? [
                    'unresolved_alerts' => InventoryAlert::where('is_resolved', false)->count(),
                    'new_inquiries' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
                ]
                : [
                    'unresolved_alerts' => 0,
                    'new_inquiries' => 0,
            ],
        ]);
    }
}