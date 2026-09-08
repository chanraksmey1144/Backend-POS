<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

        public $timestamps = false; // Immutable ledger (only created_at)
    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'user_id',
        'movement_date',
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'reference',
        'note',
        'created_at',
    ];
    protected $casts = [
        'movement_date' => 'datetime',
        'quantity'      => 'float',
        'before_stock'  => 'float',
        'after_stock'   => 'float',
        'created_at'    => 'datetime',
    ];

    
    protected static function booted(): void
    {
        static::creating(function ($movement) {
            if (empty($movement->movement_date)) {
                $movement->movement_date = now();
            }
            if (empty($movement->user_id) && auth()->check()) {
                $movement->user_id = auth()->id();
            }
        });
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
