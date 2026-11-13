<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim($request->input('q', ''));
        $category = $request->input('category', 'All');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = collect();

        if (in_array($category, ['All', 'Products'])) {
            $products = Product::with(['variants', 'category'])
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orWhereHas('variants', function ($q) use ($query) {
                    $q->where('sku', 'like', "%{$query}%");
                })
                ->take(6)
                ->get()
                ->map(function ($product) {
                    $firstVariant = $product->variants->first();
                    $sku = $firstVariant?->sku;
                    $skuText = $sku ? "SKU: {$sku} • " : '';
                    $totalStock = $product->variants->sum('stock_qty');

                    return [
                        'id'       => 'product-' . $product->id,
                        'type'     => 'Products',
                        'title'    => $product->name,
                        'subtitle' => "{$skuText}Stock: {$totalStock} units",
                        'href'     => "/inventory?search=" . urlencode($product->name),
                    ];
                });

            $results = $results->concat($products);
        }

        if (in_array($category, ['All', 'Categories'])) {
            $categories = Category::withCount('products')
                ->where('name', 'like', "%{$query}%")
                ->take(4)
                ->get()
                ->map(function ($cat) {
                    return [
                        'id'       => 'category-' . $cat->id,
                        'type'     => 'Categories',
                        'title'    => $cat->name,
                        'subtitle' => "{$cat->products_count} Products",
                        'href'     => "/inventory?category=" . $cat->id,
                    ];
                });

            $results = $results->concat($categories);
        }

        if (in_array($category, ['All', 'Brands'])) {
            $brands = Brand::withCount('products')
                ->where('name', 'like', "%{$query}%")
                ->take(4)
                ->get()
                ->map(function ($brand) {
                    return [
                        'id'       => 'brand-' . $brand->id,
                        'type'     => 'Brands',
                        'title'    => $brand->name,
                        'subtitle' => "{$brand->products_count} Products",
                        'href'     => "/inventory?brand=" . $brand->id,
                    ];
                });

            $results = $results->concat($brands);
        }

        return response()->json($results->take(8));
    }
}
