<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'cashier_id',
        'branch_id',
        'register_id',
        'sale_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'change',
        'payment_method',
        'status',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'sale_date'  => 'datetime',
        'subtotal'   => 'float',
        'discount'   => 'float',
        'tax'        => 'float',
        'total'      => 'float',
        'paid'       => 'float',
        'change'     => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Auto-generate invoice number if not explicitly given (e.g. INV-20260906-0001)
     */
    protected static function booted(): void
    {
        static::creating(function ($sale) {
            if (empty($sale->invoice_number)) {
                $dateStr = now()->format('Ymd');
                $countToday = static::whereDate('created_at', now()->today())->count() + 1;
                $sale->invoice_number = sprintf('INV-%s-%04d', $dateStr, $countToday);
            }
            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
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
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }
}
