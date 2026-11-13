<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'threshold_reached',
        'is_resolved',
    ];

    /**
     * Get the product variant associated with the alert.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Relationship alias for queries referencing productVariant.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
