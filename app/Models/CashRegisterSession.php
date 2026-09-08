<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterSession extends Model
{
    use HasFactory;

        protected $fillable = [
        'register_id',
        'branch_id',
        'user_id',
        'opening_cash',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];
    protected $casts = [
        'opening_cash'  => 'float',
        'expected_cash' => 'float',
        'actual_cash'   => 'float',
        'difference'    => 'float',
        'opened_at'     => 'datetime',
        'closed_at'     => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
        /**
     * Auto-assign opened_at, user_id, and expected_cash when opening shift
     */
    protected static function booted(): void
    {
        static::creating(function ($session) {
            if (empty($session->opened_at)) {
                $session->opened_at = now();
            }
            if (empty($session->user_id) && auth()->check()) {
                $session->user_id = auth()->id();
            }
            if (!isset($session->expected_cash)) {
                $session->expected_cash = $session->opening_cash ?? 0;
            }
        });
    }
    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
