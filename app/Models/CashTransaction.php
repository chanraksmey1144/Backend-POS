<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at is used
    protected $fillable = [
        'session_id',
        'user_id',
        'transaction_type',
        'amount',
        'description',
        'created_at',
    ];
    protected $casts = [
        'amount'     => 'float',
        'created_at' => 'datetime',
    ];
    
    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            if (empty($transaction->user_id) && auth()->check()) {
                $transaction->user_id = auth()->id();
            }
        });
    }
        public function session(): BelongsTo
    {
        return $this->belongsTo(CashRegisterSession::class, 'session_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
