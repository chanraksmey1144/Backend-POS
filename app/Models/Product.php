<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

        protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'description',
        'image_label',
        'image_color',
        'image_url',
        'image_public_id',
        'cost',
        'price',
        'wholesale_price',
        'tax_percent',
        'track_inventory',
        'min_stock',
        'max_stock',
        'stock',
        'status',
    ];

        protected $casts = [
        'cost'            => 'float',
        'price'           => 'float',
        'wholesale_price' => 'float',
        'tax_percent'     => 'float',
        'track_inventory' => 'boolean',
        'min_stock'       => 'float',
        'max_stock'       => 'float',
        'stock'           => 'float',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

        public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
    return $this->hasMany(ProductVariant::class);
    }
}
