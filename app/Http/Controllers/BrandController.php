<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $brands = Brand::withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(100)
            ->withQueryString();

        return Inertia::render('Brands/Index', [
            'brands'  => $brands,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('brands', 'name')],
            'description' => ['nullable', 'string'],
        ]);

        Brand::create([
            'name'        => $validated['name'],
            'slug'        => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'description' => ['nullable', 'string'],
        ]);

        $brand->update([
            'name'        => $validated['name'],
            'slug'        => $brand->name === $validated['name']
                ? $brand->slug
                : $this->generateUniqueSlug($validated['name'], $brand->id),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products_count > 0 || $brand->products()->withTrashed()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete a brand linked to existing or archived products.');
        }

        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $counter = 2;

        while (
            Brand::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}