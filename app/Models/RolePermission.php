<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'role_id',
        'permission',
    ];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    /**
     * The role that this permission belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
