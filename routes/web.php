<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\InventoryAlert;
use App\Models\Announcement;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'index'])->name('storefront.index');

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->role === 'admin') {
        // Compute database metrics using variant architecture
        $stats = [
            'totalItems' => ProductVariant::count(),
            'lowStockCount' => ProductVariant::whereRaw('stock_qty <= low_stock_threshold')->count(),
            'activeAnnouncements' => Announcement::where('is_active', true)->count(),
            'totalValue' => ProductVariant::join('products', 'product_variants.product_id', '=', 'products.id')
                ->sum(DB::raw('stock_qty * COALESCE(product_variants.price_override, products.price)')),
        ];

        // Fetch recent alert records linked via the 'variant' relation
        $recentAlerts = InventoryAlert::with('variant.product')
            ->where('is_resolved', false)
            ->latest()
            ->take(5)
            ->get();

        // Fetch highly stocked variant assets
        $topStockedItems = ProductVariant::with('product')
            ->orderByDesc('stock_qty')
            ->take(4)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentAlerts' => $recentAlerts,
            'topStockedItems' => $topStockedItems,
        ]);
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
});

require __DIR__ . '/auth.php';