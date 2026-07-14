<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return Inertia::render('Storefront/Index', [
            'products' => $products
        ]);
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'brand', 'variants'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Storefront/Show', [
            'product' => $product
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:255',
            'attributes.*.value' => 'required|string|max:255',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.variant_label' => 'required|string|max:255',
            'variants.*.price_override' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'description' => $validated['description'],
                'cost_price' => $validated['cost_price'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'],
            ]);

            if (!empty($validated['tags'])) {
                $product->tags()->attach($validated['tags']);
            }

            if (!empty($validated['attributes'])) {
                foreach ($validated['attributes'] as $attribute) {
                    $product->attributes()->create([
                        'name' => $attribute['name'],
                        'value' => $attribute['value']
                    ]);
                }
            }

            foreach ($validated['variants'] as $variant) {
                $productVariant = $product->variants()->create([
                    'sku' => $variant['sku'],
                    'variant_label' => $variant['variant_label'],
                    'price_override' => $variant['price_override'],
                    'stock_qty' => $variant['stock_qty'],
                    'low_stock_threshold' => $variant['low_stock_threshold'],
                ]);

                if ($productVariant->stock_qty > 0) {
                    InventoryMovement::create([
                        'variant_id' => $productVariant->id,
                        'user_id' => auth()->id(),
                        'quantity' => $productVariant->stock_qty,
                        'type' => 'in',
                        'reason' => 'Initial inventory load',
                    ]);
                }

                if ($productVariant->stock_qty <= $productVariant->low_stock_threshold) {
                    InventoryAlert::create([
                        'variant_id' => $productVariant->id,
                        'threshold_reached' => $productVariant->stock_qty,
                        'is_resolved' => false
                    ]);
                }
            }
        });

        return back()->with('message', 'Product and its variants created successfully.');
    }
}