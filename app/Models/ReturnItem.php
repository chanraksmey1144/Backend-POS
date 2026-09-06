<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

        public $timestamps = false; // Only created_at is used
    protected $fillable = [
        'return_id',
        'product_id',
        'variant_id',
        'name',
        'sku',
        'price',
        'cost',
        'quantity',
        'discount',
        'tax',
        'created_at',
    ];
    protected $casts = [
        'price'      => 'float',
        'cost'       => 'float',
        'quantity'   => 'float',
        'discount'   => 'float',
        'tax'        => 'float',
        'created_at' => 'datetime',
    ];

        public function return(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
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
     * Calculate line refund total: (price * quantity) - discount
     */
    public function getLineTotalAttribute(): float
    {
        return max(0, ($this->price * $this->quantity) - $this->discount);
    }

}
