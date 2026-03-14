<?php


namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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
}