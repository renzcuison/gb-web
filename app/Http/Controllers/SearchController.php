<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\SearchQueryExpander;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim(preg_replace('/\s+/', ' ', (string) $request->input('q', '')));
        $category = $request->input('category', 'All');

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $primaryPhrase = SearchQueryExpander::expandPhrases($query)[1] ?? $query;
        $results = collect();

        if (in_array($category, ['All', 'Products'])) {
            $productsQuery = Product::with(['variants', 'brand', 'category']);
            SearchQueryExpander::applyToProductQuery($productsQuery, $query);

            $products = $productsQuery
                ->orderByRaw('CASE WHEN LOWER(name) LIKE ? THEN 0 ELSE 1 END', ['%' . Str::lower($primaryPhrase) . '%'])
                ->take(6)
                ->get()
                ->map(function ($product) {
                    $firstVariant = $product->variants->first();
                    $sku = $firstVariant?->sku;
                    $brandName = $product->brand?->name;
                    $totalStock = $product->variants->sum('stock_qty');

                    $parts = array_filter([
                        $brandName,
                        $sku,
                        "{$totalStock} in stock",
                    ]);

                    return [
                        'id'       => 'product-' . $product->id,
                        'type'     => 'Products',
                        'title'    => $product->name,
                        'subtitle' => implode(' · ', $parts),
                        'href'     => '/inventory?search=' . urlencode($sku ?: $product->name),
                    ];
                });

            $results = $results->concat($products);
        }

        if (in_array($category, ['All', 'Categories'])) {
            $categoriesQuery = Category::withCount('products');
            SearchQueryExpander::applyToTaxonomyQuery($categoriesQuery, $query);

            $categories = $categoriesQuery
                ->take(4)
                ->get()
                ->map(function ($cat) {
                    return [
                        'id'       => 'category-' . $cat->id,
                        'type'     => 'Categories',
                        'title'    => $cat->name,
                        'subtitle' => "{$cat->products_count} products",
                        'href'     => '/inventory?category=' . $cat->id,
                    ];
                });

            $results = $results->concat($categories);
        }

        if (in_array($category, ['All', 'Brands'])) {
            $brandsQuery = Brand::withCount('products');
            SearchQueryExpander::applyToTaxonomyQuery($brandsQuery, $query);

            $brands = $brandsQuery
                ->take(4)
                ->get()
                ->map(function ($brand) {
                    return [
                        'id'       => 'brand-' . $brand->id,
                        'type'     => 'Brands',
                        'title'    => $brand->name,
                        'subtitle' => "{$brand->products_count} products",
                        'href'     => '/inventory?brand=' . $brand->id,
                    ];
                });

            $results = $results->concat($brands);
        }

        return response()->json($results->take(8)->values());
    }
}
