<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

        protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'cost',
        'price',
        'stock',
    ];
    protected $casts = [
        'cost'       => 'float',
        'price'      => 'float',
        'stock'      => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
