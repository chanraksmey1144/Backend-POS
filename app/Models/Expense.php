<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'branch_id',
        'created_by',
        'category',
        'amount',
        'payment_method',
        'expense_date',
        'description',
        'receipt',
    ];
    protected $casts = [
        'amount'       => 'float',
        'expense_date' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

        /**
     * Auto-assign logged-in user and current timestamp if omitted
     */
    protected static function booted(): void
    {
        static::creating(function ($expense) {
            if (empty($expense->expense_date)) {
                $expense->expense_date = now();
            }
            if (empty($expense->created_by) && auth()->check()) {
                $expense->created_by = auth()->id();
            }
        });
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
