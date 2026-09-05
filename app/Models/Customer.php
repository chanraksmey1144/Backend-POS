<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

        protected $fillable = [
        'group_id',
        'name',
        'email',
        'phone',
        'address',
        'loyalty_points',
        'total_spent',
        'outstanding',
        'status',
    ];
    protected $casts = [
        'loyalty_points' => 'integer',
        'total_spent'    => 'float',
        'outstanding'    => 'float',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

        /**
     * Get the customer group / tier that owns this customer.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }
}
