<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'grant_all',
    ];
    protected $casts = [
        'grant_all'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    
/**
 * Get all permissions assigned to this role.
 */
public function permissions(): HasMany
{
    return $this->hasMany(RolePermission::class);
}
/**
 * Check if the role has a given permission (handles grant_all wildcard).
 */
public function hasPermission(string $permission): bool
{
    if ($this->grant_all) {
        return true;
    }
    return $this->permissions()->where('permission', $permission)->exists();
}
}
