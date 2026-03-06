<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\User;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::first();
        $brand = Brand::first();
        $tags = Tag::all();
        $user = User::first();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Minimalist Oak Chair',
            'slug' => Str::slug('Minimalist Oak Chair'),
            'description' => 'A beautiful 3D-modeled oak chair for modern interiors.',
            'cost_price' => 150.00,
            'price' => 299.00,
            'is_active' => true,
        ]);

        $product->tags()->attach($tags->pluck('id'));

        $product->attributes()->createMany([
            ['name' => 'Material', 'value' => 'Solid Oak'],
            ['name' => 'Finish', 'value' => 'Matte Varnish'],
        ]);

        $variant = $product->variants()->create([
            'sku' => 'CHAIR-OAK-STD',
            'variant_label' => 'Standard Size',
            'stock_qty' => 50,
            'low_stock_threshold' => 5,
        ]);

        InventoryMovement::create([
            'variant_id' => $variant->id,
            'user_id' => $user->id,
            'quantity' => 50,
            'type' => 'in',
            'reason' => 'Initial stock upload',
        ]);

        $product->media()->create([
            'file_path' => 'products/chair-primary.glb',
            'is_primary' => true,
            'order' => 1,
        ]);
    }
}