<?php

namespace App\Exports;

use App\Http\Controllers\SettingsController;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        $currency = SettingsController::all()['currency_symbol'] ?? '₱';

        return [
            'SKU',
            'Product Name',
            'Variant',
            'Category',
            'Brand',
            "Cost Price ({$currency})",
            "Selling Price ({$currency})",
            'Stock Qty',
            'Low Stock Threshold',
            'Status',
        ];
    }

    public function map($variant): array
    {
        $status = 'In Stock';
        if ($variant->deleted_at || $variant->product?->deleted_at) {
            $status = 'Archived';
        } elseif ($variant->stock_qty <= 0) {
            $status = 'Out of Stock';
        } elseif ($variant->stock_qty <= $variant->low_stock_threshold) {
            $status = 'Low Stock';
        }

        $sellingPrice = $variant->price_override ?? $variant->product?->price ?? 0;

        return [
            $variant->sku,
            $variant->product?->name ?? '—',
            $variant->variant_label ?: 'Standard',
            $variant->product?->category?->name ?? '—',
            $variant->product?->brand?->name ?? '—',
            $variant->product?->cost_price !== null ? number_format((float) $variant->product->cost_price, 2, '.', '') : '0.00',
            number_format((float) $sellingPrice, 2, '.', ''),
            (int) $variant->stock_qty,
            (int) $variant->low_stock_threshold,
            $status,
        ];
    }
}
