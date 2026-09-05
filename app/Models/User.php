<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'password_hash',
        'avatar',
        'status',
        'last_login_at',
        'must_change_password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'password_hash'        => 'hashed',
        'must_change_password' => 'boolean',
        'last_login_at'        => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];
    /**
     * Get the role of the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    /**
     * Get the branch assigned to the user.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
