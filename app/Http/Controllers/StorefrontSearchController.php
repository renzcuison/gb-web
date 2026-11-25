<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchLog;
use App\Models\Tag;
use App\Support\SearchQueryExpander;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StorefrontSearchController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim(preg_replace('/\s+/', ' ', (string) $request->input('q', '')));

        $recommendations = Cache::remember(
            'storefront:search:recommendations',
            now()->addMinutes(5),
            fn () => $this->buildDefaultRecommendations()
        );

        if (mb_strlen($query) < 2) {
            return response()->json([
                'mode' => 'recommendations',
                'popular_searches' => $recommendations['popular_searches'],
                'trending_products' => $recommendations['trending_products'],
                'taxonomy' => [],
                'products' => [],
            ]);
        }

        $taxonomy = collect()
            ->merge(
                SearchQueryExpander::applyToTaxonomyQuery(Category::query(), $query)
                    ->whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->limit(3)
                    ->get(['id', 'name', 'slug'])
                    ->map(fn ($c) => [
                        'type' => 'Category',
                        'label' => $c->name,
                        'params' => ['category' => $c->slug],
                    ])
            )
            ->merge(
                SearchQueryExpander::applyToTaxonomyQuery(Brand::query(), $query)
                    ->whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->limit(3)
                    ->get(['id', 'name', 'slug'])
                    ->map(fn ($b) => [
                        'type' => 'Brand',
                        'label' => $b->name,
                        'params' => ['brand' => $b->slug],
                    ])
            )
            ->merge(
                SearchQueryExpander::applyToTaxonomyQuery(Tag::query(), $query)
                    ->whereHas('products', fn ($q) => $q->where('is_active', true))
                    ->limit(3)
                    ->get(['id', 'name', 'slug'])
                    ->map(fn ($t) => [
                        'type' => 'Collection',
                        'label' => $t->name,
                        'params' => ['tag' => $t->slug],
                    ])
            )
            ->take(5)
            ->values();

        $primaryPhrase = SearchQueryExpander::expandPhrases($query)[1] ?? $query;

        $productsQuery = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
            'variants:id,product_id,sku,price_override,stock_qty',
        ])->where('is_active', true);

        SearchQueryExpander::applyToProductQuery($productsQuery, $query);

        $products = $productsQuery
            ->orderByRaw('CASE WHEN LOWER(name) LIKE ? THEN 0 ELSE 1 END', ['%' . Str::lower($primaryPhrase) . '%'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Product $product) => $this->formatProductPreview($product))
            ->values();

        return response()->json([
            'mode' => 'autocomplete',
            'popular_searches' => $recommendations['popular_searches'],
            'trending_products' => [],
            'taxonomy' => $taxonomy,
            'products' => $products,
        ]);
    }

    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:80'],
        ]);

        $term = trim($validated['query']);

        $matchQuery = Product::where('is_active', true);
        SearchQueryExpander::applyToProductQuery($matchQuery, $term);
        $matchCount = $matchQuery->count();

        if ($matchCount > 0) {
            SearchLog::record($term, $matchCount, $request->session()->getId());
            Cache::forget('storefront:search:recommendations');
        }

        return response()->json(['logged' => $matchCount > 0]);
    }

    private function buildDefaultRecommendations(): array
    {
        $now = now();
        $last24h = $now->copy()->subDay()->toDateTimeString();
        $last7d = $now->copy()->subDays(7)->toDateTimeString();
        $last30d = $now->copy()->subDays(30)->toDateTimeString();

        $rawPopular = SearchLog::query()
            ->select([
                'normalized_query',
                DB::raw('MAX(display_query) as label'),
                DB::raw("SUM(CASE WHEN created_at >= '{$last24h}' THEN 3.0 WHEN created_at >= '{$last7d}' THEN 1.5 ELSE 1.0 END) as trend_score"),
                DB::raw("SUM(CASE WHEN created_at >= '{$last24h}' THEN 1 ELSE 0 END) as recent_hits"),
            ])
            ->where('created_at', '>=', $last30d)
            ->whereRaw('CHAR_LENGTH(normalized_query) >= 3')
            ->groupBy('normalized_query')
            ->orderByDesc('trend_score')
            ->limit(15)
            ->get();

        $terms = collect();
        $seen = [];

        foreach ($rawPopular as $row) {
            if ($terms->count() >= 6) {
                break;
            }

            $term = trim((string) $row->label);
            $key = Str::lower($term);

            if ($term === '' || isset($seen[$key])) {
                continue;
            }

            $stillMatchesQuery = Product::where('is_active', true);
            SearchQueryExpander::applyToProductQuery($stillMatchesQuery, $term);

            if (!$stillMatchesQuery->exists()) {
                continue;
            }

            $seen[$key] = true;
            $terms->push([
                'label' => $term,
                'query' => $term,
                'badge' => ((int) $row->recent_hits) >= 2 ? 'Trending' : 'Popular',
            ]);
        }

        if ($terms->count() < 6) {
            $inquiredProducts = Product::where('is_active', true)
                ->withCount(['inquiries' => fn ($q) => $q->where('created_at', '>=', $last30d)])
                ->orderByDesc('inquiries_count')
                ->latest()
                ->limit(6)
                ->get(['id', 'name']);

            foreach ($inquiredProducts as $prod) {
                if ($terms->count() >= 6) {
                    break;
                }
                $key = Str::lower($prod->name);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $terms->push([
                        'label' => $prod->name,
                        'query' => $prod->name,
                        'badge' => 'In Demand',
                    ]);
                }
            }
        }

        if ($terms->count() < 6) {
            $fallbackTags = Tag::whereHas('products', fn ($q) => $q->where('is_active', true))
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->orderByDesc('products_count')
                ->limit(6)
                ->get(['id', 'name']);

            foreach ($fallbackTags as $tag) {
                if ($terms->count() >= 6) {
                    break;
                }
                $key = Str::lower($tag->name);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $terms->push([
                        'label' => $tag->name,
                        'query' => $tag->name,
                        'badge' => 'Featured',
                    ]);
                }
            }
        }

        if ($terms->count() < 6) {
            $fallbackCategories = Category::whereHas('products', fn ($q) => $q->where('is_active', true))
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->orderByDesc('products_count')
                ->limit(6)
                ->get(['id', 'name']);

            foreach ($fallbackCategories as $cat) {
                if ($terms->count() >= 6) {
                    break;
                }
                $key = Str::lower($cat->name);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $terms->push([
                        'label' => $cat->name,
                        'query' => $cat->name,
                        'badge' => 'Category',
                    ]);
                }
            }
        }

        $trendingProducts = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
            'variants:id,product_id,sku,price_override,stock_qty',
        ])
            ->where('is_active', true)
            ->withCount(['inquiries' => fn ($q) => $q->where('created_at', '>=', $last30d)])
            ->orderByDesc('inquiries_count')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Product $product) => $this->formatProductPreview($product))
            ->values();

        return [
            'popular_searches' => $terms->values()->all(),
            'trending_products' => $trendingProducts->all(),
        ];
    }

    private function formatProductPreview(Product $product): array
    {
        $totalStock = $product->variants->sum('stock_qty');
        $minPrice = $product->variants
            ->map(fn ($v) => $v->price_override ?? $product->price)
            ->filter(fn ($p) => $p !== null)
            ->min() ?? $product->price;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'subtitle' => $product->brand?->name ?? $product->category?->name ?? 'GreatBuy Originals',
            'price' => (float) $minPrice,
            'in_stock' => $totalStock > 0,
            'image' => $product->media->first()?->url,
        ];
    }
}