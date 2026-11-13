<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
{
    public function model(array $row): Model|array|null
    {
        $name = trim($row['name']);
        $slug = Str::slug($name);

        $categoryId = !empty($row['category'])
            ? Category::firstOrCreate(
                ['name' => trim($row['category'])],
                ['slug' => Str::slug(trim($row['category']))]
            )->id
            : null;

        $brandId = !empty($row['brand'])
            ? Brand::firstOrCreate(
                ['name' => trim($row['brand'])],
                ['slug' => Str::slug(trim($row['brand']))]
            )->id
            : null;

        $product = Product::updateOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $categoryId,
                'brand_id'    => $brandId,
                'name'        => $name,
                'description' => $row['description'] ?? null,
                'cost_price'  => $row['cost_price'] ?? 0.00,
                'price'       => $row['price'] ?? 0.00,
                'is_active'   => isset($row['is_active'])
                    ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN)
                    : true,
            ]
        );

        if (!empty($row['sku'])) {
            $stockQty = (int) ($row['stock'] ?? $row['stock_qty'] ?? $row['quantity'] ?? 0);

            $variant = $product->variants()->updateOrCreate(
                ['sku' => trim($row['sku'])],
                [
                    'variant_label' => $row['variant_label'] ?? $row['variant'] ?? 'Default',
                    'price'         => $row['price'] ?? $product->price,
                    'stock_qty'     => $stockQty,
                ]
            );

            if ($stockQty > 0) {
                InventoryMovement::create([
                    'variant_id' => $variant->id,
                    'user_id'    => auth()->id(),
                    'quantity'   => $stockQty,
                    'type'       => 'in',
                    'reason'     => 'Excel Import',
                ]);
            }
        }

        return null;
    }

    public function rules(): array
    {
        return [
            '*.name'          => 'required|string|max:255',
            '*.category'      => 'nullable|string|max:255',
            '*.brand'         => 'nullable|string|max:255',
            '*.cost_price'    => 'nullable|numeric|min:0',
            '*.price'         => 'required|numeric|min:0',
            '*.description'   => 'nullable|string',
            '*.is_active'     => 'nullable',
            '*.sku'           => 'nullable|string|max:100',
            '*.variant_label' => 'nullable|string|max:100',
            '*.stock'         => 'nullable|integer|min:0',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
