<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if ($request->user()->role !== 'admin') {
            return Inertia::render('Dashboard');
        }

        $liveVariantsQuery = ProductVariant::whereHas('product');

        $stats = [
            'totalItems' => (clone $liveVariantsQuery)->count(),
            'archivedItems' => ProductVariant::onlyTrashed()->count(),
            'outOfStockCount' => (clone $liveVariantsQuery)->where('stock_qty', '<=', 0)->count(),
            'lowStockCount' => (clone $liveVariantsQuery)
                ->where('stock_qty', '>', 0)
                ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->count(),
            'newInquiriesCount' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'totalUnits' => (int) (clone $liveVariantsQuery)->sum('stock_qty'),
            'totalValue' => ProductVariant::join('products', 'product_variants.product_id', '=', 'products.id')
                ->whereNull('product_variants.deleted_at')
                ->whereNull('products.deleted_at')
                ->sum(DB::raw('stock_qty * COALESCE(product_variants.price_override, products.price, 0)')),
        ];

        $recentAlerts = InventoryAlert::with('productVariant.product')
            ->where('is_resolved', false)
            ->latest()
            ->take(6)
            ->get();

        $recentMovements = InventoryMovement::with(['variant.product', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $topStockedItems = (clone $liveVariantsQuery)
            ->with('product')
            ->orderByDesc('stock_qty')
            ->take(5)
            ->get();

        $recentInquiries = Inquiry::with([
            'product:id,name,slug',
            'variant:id,sku,variant_label',
        ])
            ->whereIn('status', [Inquiry::STATUS_NEW, Inquiry::STATUS_CONTACTED])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentAlerts' => $recentAlerts,
            'recentMovements' => $recentMovements,
            'topStockedItems' => $topStockedItems,
            'recentInquiries' => $recentInquiries,
        ]);
    }
}