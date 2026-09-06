<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldSale extends Model
{
    use HasFactory;

        public $timestamps = false; // Only created_at is used
    protected $fillable = [
        'hold_number',
        'customer_id',
        'cashier_id',
        'discount',
        'tax',
        'total',
        'items_json',
        'created_at',
    ];
    protected $casts = [
        'items_json' => 'array', // Automatically serialize/unserialize JSON array
        'discount'   => 'float',
        'tax'        => 'float',
        'total'      => 'float',
        'created_at' => 'datetime',
    ];

        /**
     * Auto-generate hold_number if not provided (e.g. HOLD-001, HOLD-002)
     */
    protected static function booted(): void
    {
        static::creating(function ($heldSale) {
            if (empty($heldSale->hold_number)) {
                $todayCount = static::whereDate('created_at', now()->today())->count() + 1;
                $heldSale->hold_number = sprintf('HOLD-%03d', $todayCount);
            }
        });
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

}
