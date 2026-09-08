<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

        public $timestamps = false; // Only created_at is used
    protected $fillable = [
        'purchase_id',
        'product_id',
        'variant_id',
        'name',
        'sku',
        'cost',
        'quantity',
        'received_quantity',
        'created_at',
    ];
    protected $casts = [
        'cost'              => 'float',
        'quantity'          => 'float',
        'received_quantity' => 'float',
        'created_at'        => 'datetime',
    ];
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

        public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    /**
     * Compute line total: cost * quantity
     */
    public function getLineTotalAttribute(): float
    {
        return $this->cost * $this->quantity;
    }
}
