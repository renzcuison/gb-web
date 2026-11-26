<?php

namespace App\Exports;

use App\Http\Controllers\SettingsController;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryMovementExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        return [
            'Log ID',
            'Timestamp',
            'Product Name',
            'Variant',
            'SKU',
            'Direction',
            'Quantity',
            'Reason',
            'Recorded By',
        ];
    }

    public function map($movement): array
    {
        $dateFormat = SettingsController::all()['export_date_format'] ?? 'Y-m-d';

        return [
            $movement->id,
            $movement->created_at?->format("{$dateFormat} H:i:s") ?? '—',
            $movement->variant?->product?->name ?? 'Deleted Product',
            $movement->variant?->variant_label ?: 'Standard',
            $movement->variant?->sku ?? '—',
            $movement->type === 'in' ? 'Stock In' : 'Stock Out',
            ($movement->type === 'in' ? '+' : '-') . $movement->quantity,
            $movement->reason ?: ($movement->type === 'in' ? 'Manual Stock In' : 'Manual Stock Out'),
            $movement->user?->name ?? 'System',
        ];
    }
}
