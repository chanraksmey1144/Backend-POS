<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'item_count',
        'status',
        'notes',
        'created_by',
    ];
    protected $casts = [
        'item_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Auto-generate transfer_number if omitted (e.g. TRF-2026-0001)
     */
    protected static function booted(): void
    {
        static::creating(function ($transfer) {
            if (empty($transfer->transfer_number)) {
                $year = now()->format('Y');
                $countThisYear = static::whereYear('created_at', $year)->count() + 1;
                $transfer->transfer_number = sprintf('TRF-%s-%04d', $year, $countThisYear);
            }
            if (empty($transfer->created_by) && auth()->check()) {
                $transfer->created_by = auth()->id();
            }
        });
    }
    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }
    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items(): HasMany
{
    return $this->hasMany(TransferItem::class, 'transfer_id');
}
}
