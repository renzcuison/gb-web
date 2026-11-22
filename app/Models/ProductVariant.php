<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_label',
        'price_override',
        'stock_qty',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'stock_qty' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ProductVariant $variant) {
            if ($variant->wasChanged(['stock_qty', 'low_stock_threshold'])) {
                $variant->syncStockAlert();
            }
        });
    }

    public function syncStockAlert(): void
    {
        if ($this->stock_qty <= $this->low_stock_threshold) {
            $openAlert = $this->alerts()
                ->where('is_resolved', false)
                ->latest()
                ->first();

            if ($openAlert) {
                if ($openAlert->threshold_reached !== $this->stock_qty) {
                    $openAlert->update(['threshold_reached' => $this->stock_qty]);
                }
            } else {
                $this->alerts()->create([
                    'threshold_reached' => $this->stock_qty,
                    'is_resolved' => false,
                ]);
            }

            return;
        }

        $this->alerts()
            ->where('is_resolved', false)
            ->update(['is_resolved' => true]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(InventoryAlert::class, 'variant_id');
    }
}