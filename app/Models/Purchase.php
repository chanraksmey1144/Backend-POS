<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'branch_id',
        'warehouse_id',
        'order_date',
        'expected_date',
        'received_at',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date'     => 'datetime',
        'expected_date'  => 'datetime',
        'received_at'    => 'datetime',
        'subtotal'       => 'float',
        'discount'       => 'float',
        'tax'            => 'float',
        'total'          => 'float',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];
    /**
     * Auto-generate purchase_number if omitted (e.g. PO-2026-0001)
     */
    protected static function booted(): void
    {
        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $year = now()->format('Y');
                $countThisYear = static::whereYear('created_at', $year)->count() + 1;
                $purchase->purchase_number = sprintf('PO-%s-%04d', $year, $countThisYear);
            }
            if (empty($purchase->order_date)) {
                $purchase->order_date = now();
            }
        });
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
