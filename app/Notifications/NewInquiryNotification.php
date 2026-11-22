<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Notifications\Notification;

class NewInquiryNotification extends Notification
{
    public function __construct(
        public Inquiry $inquiry
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->inquiry->loadMissing(['product', 'variant']);

        $productName = $this->inquiry->product?->name ?? 'Product';
        $variantText = $this->inquiry->variant?->variant_label
            ? " ({$this->inquiry->variant->variant_label})"
            : '';

        return [
            'title' => 'New Customer Inquiry',
            'message' => "{$this->inquiry->customer_name} inquired about {$productName}{$variantText} — Qty: {$this->inquiry->quantity}.",
            'type' => 'info',
            'link' => '/inquiries',
        ];
    }
}