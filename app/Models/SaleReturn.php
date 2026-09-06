<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    use HasFactory;

    // Explicitly bind to 'returns' table
    protected $table = 'sale_returns';
    protected $fillable = [
        'sale_id',
        'branch_id',
        'cashier_id',
        'reason',
        'refund_amount',
    ];
    protected $casts = [
        'refund_amount' => 'float',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
