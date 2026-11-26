<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\InventoryAlertController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\StorefrontSearchController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Models\Announcement;
use App\Models\Inquiry;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'index'])->name('storefront.index');
Route::get('/storefront/search/suggestions', [StorefrontSearchController::class, 'suggestions'])
    ->middleware('throttle:60,1')
    ->name('storefront.search.suggestions');
Route::post('/storefront/search/log', [StorefrontSearchController::class, 'log'])
    ->middleware('throttle:30,1')
    ->name('storefront.search.log');
Route::post('/inquiries', [InquiryController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('inquiries.store');

Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->role === 'admin') {
        $stats = [
            'totalItems' => ProductVariant::count(),
            'archivedItems' => ProductVariant::onlyTrashed()->count(),
            'outOfStockCount' => ProductVariant::where('stock_qty', '<=', 0)->count(),
            'lowStockCount' => ProductVariant::where('stock_qty', '>', 0)
                ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->count(),
            'newInquiriesCount' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'activeAnnouncements' => Announcement::where('is_active', true)->count(),
            'totalValue' => ProductVariant::join('products', 'product_variants.product_id', '=', 'products.id')
                ->sum(DB::raw('stock_qty * COALESCE(product_variants.price_override, products.price, 0)')),
        ];

        $outOfStockItems = ProductVariant::with('product:id,name')
            ->where('stock_qty', '<=', 0)
            ->latest('updated_at')
            ->take(3)
            ->get(['id', 'product_id', 'sku', 'variant_label', 'stock_qty', 'updated_at']);

        $lowStockItems = ProductVariant::with('product:id,name')
            ->where('stock_qty', '>', 0)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->take(3)
            ->get(['id', 'product_id', 'sku', 'variant_label', 'stock_qty', 'low_stock_threshold']);

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
            'outOfStockItems' => $outOfStockItems,
            'lowStockItems' => $lowStockItems,
            'recentAlerts' => $recentAlerts,
            'recentMovements' => $recentMovements,
            'topStockedItems' => $topStockedItems,
            'activeAnnouncementsList' => $activeAnnouncementsList,
            'recentInquiries' => $recentInquiries,
        ]);
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/inventory/movements', [InventoryMovementController::class, 'index'])->name('inventory.movements.index');
    Route::get('/inventory/movements/export', [InventoryMovementController::class, 'export'])->name('inventory.movements.export');

    // Product Management
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

    // Catalog
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);
    Route::resource('tags', TagController::class)->except(['create', 'show', 'edit']);

    // Search
    Route::get('/api/search', SearchController::class)->name('api.search');

    // Inventory Alerts
    Route::get('/inventory/alerts', [InventoryAlertController::class, 'index'])->name('inventory.alerts.index');
    Route::patch('/inventory/alerts/{alert}/resolve', [InventoryAlertController::class, 'resolve'])->name('inventory.alerts.resolve');
    Route::post('/inventory/alerts/resolve-all', [InventoryAlertController::class, 'resolveAll'])->name('inventory.alerts.resolve-all');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    // Customer Inquiries
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::patch('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/fulfill', [InquiryController::class, 'fulfill'])->name('inquiries.fulfill');
    Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy'])->name('inquiries.destroy');
});

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

require __DIR__ . '/auth.php';