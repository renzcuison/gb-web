<?php

namespace App\Http\Middleware;

use App\Models\InventoryMovement;
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
            'notifications' => fn() => $request->user()
                ? $request->user()->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($n) => [
                    'id'               => $n->id,
                    'title'            => $n->data['title'] ?? 'Notification',
                    'message'          => $n->data['message'] ?? '',
                    'type'             => $n->data['type'] ?? 'info',
                    'link'             => $n->data['link'] ?? null,
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'read_at'          => $n->read_at ? $n->read_at->toISOString() : null,
                ])
                : [],

            'recent_activities' => fn() => $request->user()
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
        ]);
    }
}
