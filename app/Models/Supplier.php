<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'tax_number',
        'address',
        'total_purchases',
        'outstanding',
        'status',
    ];

    protected $casts = [
        'total_purchases' => 'decimal:2',
        'outstanding' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
