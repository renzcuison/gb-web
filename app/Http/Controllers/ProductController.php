<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ProductController extends Controller
{
public function index(Request $request)
    {
        $perPage = 12;
        $query = $this->buildCatalogQuery($request);

        if ($request->wantsJson()) {
            $offset = max(0, (int) $request->input('offset', 0));

            $chunk = $query
                ->skip($offset)
                ->take($perPage + 1)
                ->get(['id', 'category_id', 'brand_id', 'name', 'slug', 'description', 'price']);

            $hasMore = $chunk->count() > $perPage;

            return response()->json([
                'items' => $chunk->take($perPage)->values(),
                'has_more' => $hasMore,
            ]);
        }

        $total = (clone $query)->count();
        $initialItems = $query
            ->take($perPage)
            ->get(['id', 'category_id', 'brand_id', 'name', 'slug', 'description', 'price']);

        $newArrivals = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
            'variants:id,product_id,sku,variant_label,price_override,stock_qty,low_stock_threshold',
        ])
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        return Inertia::render('Storefront/Index', [
            'catalog' => [
                'items' => $initialItems,
                'total' => $total,
                'has_more' => $total > $initialItems->count(),
            ],
            'newArrivals' => $newArrivals,
            'categories' => Category::whereHas('products', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'brands' => Brand::whereHas('products', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'tags' => Tag::whereHas('products', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'color_code']),
            'filters' => [
                'search' => trim((string) $request->input('search', '')),
                'category' => $request->input('category') ?: '',
                'brand' => $request->input('brand') ?: '',
                'tag' => $request->input('tag') ?: '',
                'in_stock' => $request->boolean('in_stock'),
                'sort' => $request->input('sort', 'latest'),
            ],
        ]);
    }

    private function buildCatalogQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');
        $brand = $request->input('brand');
        $tag = $request->input('tag');
        $inStock = $request->boolean('in_stock');
        $sort = $request->input('sort', 'latest');

        $query = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
        ])->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'like', "%{$search}%"));
            });
        }

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                is_numeric($category)
                    ? $q->where('id', (int) $category)
                    : $q->where('slug', $category);
            });
        }

        if ($brand) {
            $query->whereHas('brand', function ($q) use ($brand) {
                is_numeric($brand)
                    ? $q->where('id', (int) $brand)
                    : $q->where('slug', $brand);
            });
        }

        if ($tag) {
            $query->whereHas('tags', function ($q) use ($tag) {
                is_numeric($tag)
                    ? $q->where('tags.id', (int) $tag)
                    : $q->where('tags.slug', $tag);
            });
        }

        if ($inStock) {
            $query->whereHas('variants', fn ($q) => $q->where('stock_qty', '>', 0));
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        return $query;
    }

    public function show($slug)
    {
        $product = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'variants' => fn ($q) =>$q->orderBy('id'),
            'media' => fn ($q) =>$q->orderByDesc('is_primary')->orderBy('order'),
            'tags:id,name,slug,color_code',
            'attributes:id,product_id,name,value',
        ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::with([
            'category:id,name,slug',
            'brand:id,name,slug',
            'media' => fn ($q) =>$q->orderByDesc('is_primary')->orderBy('order'),
            'variants:id,product_id,price_override,stock_qty',
        ])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id',$product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Storefront/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name', 'color_code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated =$request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'primary_image_index' => 'nullable|integer|min:0',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|distinct|unique:product_variants,sku',
            'variants.*.variant_label' => 'required|string|max:255',
            'variants.*.price_override' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $validated) {$product = Product::create([
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'description' => $validated['description'] ?? null,
                'cost_price' => $validated['cost_price'] ?? null,
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (!empty($validated['tags'])) {
                $product->tags()->attach($validated['tags']);
            }

            if ($request->hasFile('images')) {
                $primaryIndex = (int) ($validated['primary_image_index'] ?? 0);

                foreach ($request->file('images') as$index => $file) {$path = $file->store("products/{$product->id}", 'public');

                    $product->media()->create([
                        'file_path' => $path,
                        'is_primary' => $index ===$primaryIndex,
                        'order' => $index,
                    ]);
                }
            }

            foreach ($validated['variants'] as$variant) {
                $productVariant =$product->variants()->create([
                    'sku' => $variant['sku'],
                    'variant_label' => $variant['variant_label'],
                    'price_override' => $variant['price_override'] ?? null,
                    'stock_qty' => $variant['stock_qty'],
                    'low_stock_threshold' => $variant['low_stock_threshold'] ?? 10,
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
            }
        });

        return redirect()->route('inventory.index')->with('success', 'Product group and variants created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load([
            'variants',
            'tags:id,name,color_code',
            'media' => fn ($q) =>$q->orderByDesc('is_primary')->orderBy('order'),
        ]);

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name', 'color_code']),
        ]);
    }

    public function update(Request $request, Product$product)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'deleted_media_ids' => 'nullable|array',
            'deleted_media_ids.*' => 'integer|exists:product_media,id',
            'primary_media_id' => 'nullable|integer',
            'primary_new_image_index' => 'nullable|integer|min:0',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.variant_label' => 'required|string|max:255',
            'variants.*.price_override' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
        ];

        foreach ($request->input('variants', []) as $index =>$variant) {
            $rules["variants.{$index}.sku"] = [
                'required',
                'string',
                'distinct',
                Rule::unique('product_variants', 'sku')->ignore($variant['id'] ?? null),
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($request,$product, $validated) {$product->update([
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'cost_price' => $validated['cost_price'] ?? null,
                'price' => $validated['price'],
                'is_active' => $validated['is_active'],
            ]);

            $product->tags()->sync($validated['tags'] ?? []);

            $submittedIds = collect($validated['variants'])
                ->pluck('id')
                ->filter()
                ->all();

            $product->variants()->whereNotIn('id',$submittedIds)->delete();

            foreach ($validated['variants'] as$variantData) {
                if (!empty($variantData['id'])) {$existing = $product->variants()->findOrFail($variantData['id']);
                    $oldStock =$existing->stock_qty;
                    $newStock = (int)$variantData['stock_qty'];

                    $existing->update([
                        'sku' => $variantData['sku'],
                        'variant_label' => $variantData['variant_label'],
                        'price_override' => $variantData['price_override'] ?? null,
                        'stock_qty' => $newStock,
                        'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 10,
                    ]);

                    if ($oldStock !== $newStock) {$diff = $newStock -$oldStock;
                        InventoryMovement::create([
                            'variant_id' => $existing->id,
                            'user_id' => auth()->id(),
                            'quantity' => abs($diff),
                            'type' => $diff > 0 ? 'in' : 'out',
                            'reason' => 'Product edit stock adjustment',
                        ]);
                    }
                } else {
                    $created =$product->variants()->create([
                        'sku' => $variantData['sku'],
                        'variant_label' => $variantData['variant_label'],
                        'price_override' => $variantData['price_override'] ?? null,
                        'stock_qty' => $variantData['stock_qty'],
                        'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 10,
                    ]);

                    if ($created->stock_qty > 0) {
                        InventoryMovement::create([
                            'variant_id' => $created->id,
                            'user_id' => auth()->id(),
                            'quantity' => $created->stock_qty,
                            'type' => 'in',
                            'reason' => 'Initial variant stock',
                        ]);
                    }
                }
            }

            if (!empty($validated['deleted_media_ids'])) {$toDelete = $product->media()->whereIn('id',$validated['deleted_media_ids'])->get();
                foreach ($toDelete as$media) {
                    Storage::disk('public')->delete($media->file_path);$media->delete();
                }
            }

            $createdMedia = [];
            $nextOrder = ($product->media()->max('order') ?? -1) + 1;

            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as$index => $file) {$path = $file->store("products/{$product->id}", 'public');
                    $createdMedia[$index] =$product->media()->create([
                        'file_path' => $path,
                        'is_primary' => false,
                        'order' => $nextOrder +$index,
                    ]);
                }
            }

            $product->media()->update(['is_primary' => false]);

            if (!empty($validated['primary_media_id'])) {$product->media()
                    ->where('id', $validated['primary_media_id'])
                    ->update(['is_primary' => true]);
            } elseif (isset($validated['primary_new_image_index']) && isset($createdMedia[$validated['primary_new_image_index']])) {
                $createdMedia[$validated['primary_new_image_index']]->update(['is_primary' => true]);
            } else {
                $firstMedia =$product->media()->orderBy('order')->first();
                if ($firstMedia) {$firstMedia->update(['is_primary' => true]);
                }
            }
        });

        return redirect()->route('inventory.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            $product->variants()->delete();$product->delete();
        });

        return redirect()->route('inventory.index')->with('success', 'Product archived.');
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($product) {
            $product->restore();$product->variants()->onlyTrashed()->restore();
        });

        return redirect()->route('inventory.index')->with('success', 'Product restored successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));

            return back()->with('success', 'Products imported successfully!');
        } catch (ValidationException $e) {$failures = $e->failures();$errors = [];

            foreach ($failures as $failure) {$errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()->withErrors(['file' => $errors]);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Failed to process import file: ' . $e->getMessage()]);
        }
    }
}