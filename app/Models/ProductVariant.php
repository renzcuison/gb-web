<?php

namespace App\Models;

use App\Http\Controllers\SettingsController;
use App\Notifications\StockAlertNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;

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
        $settings = SettingsController::all();

        $isOutOfStock = $this->stock_qty <= 0;
        $isLowStock = ! $isOutOfStock && $this->stock_qty <= $this->low_stock_threshold;

        $shouldTrackAlert = ($isOutOfStock && $settings['alert_on_zero_stock'])
            || ($isLowStock && $settings['alert_on_low_stock']);

        if ($shouldTrackAlert) {
            $openAlert = $this->alerts()
                ->where('is_resolved', false)
                ->latest()
                ->first();

            $shouldNotify = false;

            if ($openAlert) {
                $escalatedToOutOfStock = $openAlert->threshold_reached > 0
                    && $this->stock_qty <= 0
                    && $settings['alert_on_zero_stock'];

                if ($openAlert->threshold_reached !== $this->stock_qty) {
                    $openAlert->update(['threshold_reached' => $this->stock_qty]);
                }

                $shouldNotify = $escalatedToOutOfStock;
            } else {
                $this->alerts()->create([
                    'threshold_reached' => $this->stock_qty,
                    'is_resolved' => false,
                ]);

                $shouldNotify = true;
            }

            if ($shouldNotify) {
                $admins = User::where('role', 'admin')->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new StockAlertNotification($this));
                }
            }

            return;
        }

        if ($this->stock_qty > $this->low_stock_threshold && $settings['auto_resolve_alerts_on_restock']) {
            $this->alerts()
                ->where('is_resolved', false)
                ->update(['is_resolved' => true]);
        }
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
