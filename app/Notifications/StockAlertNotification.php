<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    public function __construct(
        public ProductVariant $variant
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->variant->loadMissing('product');

        $productName = $this->variant->product?->name ?? 'Product';
        $variantLabel = $this->variant->variant_label ?: 'Standard';
        $isOutOfStock = $this->variant->stock_qty <= 0;

        return [
            'title' => $isOutOfStock ? 'Out of Stock Alert' : 'Low Stock Alert',
            'message' => $isOutOfStock
                ? "{$productName} ({$variantLabel}) [{$this->variant->sku}] is out of stock (0 units remaining)."
                : "{$productName} ({$variantLabel}) [{$this->variant->sku}] is low on stock ({$this->variant->stock_qty} units left, threshold: {$this->variant->low_stock_threshold}).",
            'type' => $isOutOfStock ? 'error' : 'warning',
            'link' => '/inventory/alerts',
        ];
    }
}