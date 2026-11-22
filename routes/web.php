<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\InventoryAlertController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Models\Announcement;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'index'])->name('storefront.index');

Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->role === 'admin') {
        $stats = [
            'totalItems' => ProductVariant::count(),
            'outOfStockCount' => ProductVariant::where('stock_qty', '<=', 0)->count(),
            'lowStockCount' => ProductVariant::where('stock_qty', '>', 0)
                ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->count(),
            'activeAnnouncements' => Announcement::where('is_active', true)->count(),
            'totalValue' => ProductVariant::join('products', 'product_variants.product_id', '=', 'products.id')
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

        $topStockedItems = ProductVariant::with('product')
            ->orderByDesc('stock_qty')
            ->take(5)
            ->get();

        $activeAnnouncementsList = Announcement::where('is_active', true)
            ->orderByDesc('priority')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentAlerts' => $recentAlerts,
            'recentMovements' => $recentMovements,
            'topStockedItems' => $topStockedItems,
            'activeAnnouncementsList' => $activeAnnouncementsList,
        ]);
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');

    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

    Route::get('/api/search', SearchController::class)->name('api.search');

    // Inventory Alerts
    Route::get('/inventory/alerts', [InventoryAlertController::class, 'index'])->name('inventory.alerts.index');
    Route::patch('/inventory/alerts/{alert}/resolve', [InventoryAlertController::class, 'resolve'])->name('inventory.alerts.resolve');
    Route::post('/inventory/alerts/resolve-all', [InventoryAlertController::class, 'resolveAll'])->name('inventory.alerts.resolve-all');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
});

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

require __DIR__ . '/auth.php';
