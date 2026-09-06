<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at is used

    protected $fillable = [
        'sale_id',
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

        public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
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
     * Compute line total: (price * quantity) - discount
     */
    public function getLineTotalAttribute(): float
    {
        return max(0, ($this->price * $this->quantity) - $this->discount);
    }
}
