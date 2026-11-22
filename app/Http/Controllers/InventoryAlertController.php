<?php

namespace App\Http\Controllers;

use App\Models\InventoryAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryAlertController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'unresolved');
        $severity = $request->input('severity', 'all');

        $query = InventoryAlert::with([
            'variant.product.category',
            'variant.product.brand',
        ])->whereHas('variant');

        if ($status === 'unresolved') {
            $query->where('is_resolved', false);
        } elseif ($status === 'resolved') {
            $query->where('is_resolved', true);
        }

        if ($severity === 'out_of_stock') {
            $query->whereHas('variant', fn ($q) => $q->where('stock_qty', '<=', 0));
        } elseif ($severity === 'low_stock') {
            $query->whereHas('variant', fn ($q) => $q->where('stock_qty', '>', 0));
        }

        if ($search !== '') {
            $query->whereHas('variant', function ($vq) use ($search) {
                $vq->where('sku', 'like', "%{$search}%")
                    ->orWhere('variant_label', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $alerts = $query
            ->orderBy('is_resolved', 'asc')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'unresolved' => InventoryAlert::where('is_resolved', false)->whereHas('variant')->count(),
            'out_of_stock' => InventoryAlert::where('is_resolved', false)
                ->whereHas('variant', fn ($q) => $q->where('stock_qty', '<=', 0))
                ->count(),
            'low_stock' => InventoryAlert::where('is_resolved', false)
                ->whereHas('variant', fn ($q) => $q->where('stock_qty', '>', 0))
                ->count(),
            'resolved' => InventoryAlert::where('is_resolved', true)->whereHas('variant')->count(),
        ];

        return Inertia::render('Admin/Alerts/Index', [
            'alerts' => $alerts,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'severity' => $severity,
            ],
        ]);
    }

    public function resolve(InventoryAlert $alert)
    {
        if (! $alert->is_resolved) {
            $alert->update(['is_resolved' => true]);
        }

        return back()->with('success', 'Alert marked as resolved.');
    }

    public function resolveAll()
    {
        $updated = InventoryAlert::where('is_resolved', false)->update(['is_resolved' => true]);

        return back()->with('success', "{$updated} open alert(s) marked as resolved.");
    }
}