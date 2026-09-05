<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_percent',
    ];
    protected $casts = [
        'discount_percent' => 'float',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Get all customers in this group.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'group_id');
    }
}
