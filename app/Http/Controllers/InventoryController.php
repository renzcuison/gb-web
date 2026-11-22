<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $categoryId = $request->input('category');
        $brandId = $request->input('brand');
        $sort = $request->input('sort', 'stock_asc');

        $query = ProductVariant::with([
            'product' => fn ($q) => $q->withTrashed()->with([
                'category',
                'brand',
                'tags:id,name,color_code',
                'media' => fn ($mq) => $mq->orderByDesc('is_primary')->orderBy('order'),
            ]),
            'inventoryMovements' => fn ($q) => $q->latest()->limit(10),
        ]);

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'in_stock') {
            $query->whereColumn('stock_qty', '>', 'low_stock_threshold');
        } elseif ($status === 'low_stock') {
            $query->where('stock_qty', '>', 0)
                ->whereColumn('stock_qty', '<=', 'low_stock_threshold');
        } elseif ($status === 'out_of_stock') {
            $query->where('stock_qty', '<=', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('variant_label', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->withTrashed()->where('name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId) {
            $query->whereHas('product', fn ($q) => $q->withTrashed()->where('category_id', $categoryId));
        }

        if ($brandId) {
            $query->whereHas('product', fn ($q) => $q->withTrashed()->where('brand_id', $brandId));
        }

        match ($sort) {
            'stock_desc' => $query->orderBy('stock_qty', 'desc'),
            'sku_asc' => $query->orderBy('sku', 'asc'),
            'name_asc' => $query->join('products', 'product_variants.product_id', '=', 'products.id')
                ->orderBy('products.name', 'asc')
                ->select('product_variants.*'),
            default => $query->orderBy('stock_qty', 'asc'),
        };

        $inventory = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => ProductVariant::count(),
            'in_stock' => ProductVariant::whereColumn('stock_qty', '>', 'low_stock_threshold')->count(),
            'low_stock' => ProductVariant::where('stock_qty', '>', 0)
                ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                ->count(),
            'out_of_stock' => ProductVariant::where('stock_qty', '<=', 0)->count(),
            'archived' => ProductVariant::onlyTrashed()->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'inventory' => $inventory,
            'stats' => $stats,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search ?? '',
                'status' => $status,
                'category' => $categoryId ? (int) $categoryId : null,
                'brand' => $brandId ? (int) $brandId : null,
                'sort' => $sort,
            ],
        ]);
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($validated['variant_id']);

            if ($validated['type'] === 'out' && $variant->stock_qty < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock quantity for this adjustment.',
                ]);
            }

            InventoryMovement::create([
                'variant_id' => $variant->id,
                'user_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'type' => $validated['type'],
                'reason' => $validated['reason'],
            ]);

            if ($validated['type'] === 'in') {
                $variant->increment('stock_qty', $validated['quantity']);
            } else {
                $variant->decrement('stock_qty', $validated['quantity']);
            }

            $variant->refresh();
            $variant->syncStockAlert();
        });

        return back()->with('success', 'Inventory adjusted successfully.');
    }
}