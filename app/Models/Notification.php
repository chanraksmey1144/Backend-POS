<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    
    protected $table = 'notifications';
    // Bảng chỉ có created_at nên tắt updated_at
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'link',
    ];
    protected $casts = [
        'user_id'    => 'integer',
        'type'       => NotificationType::class,
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }
    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });
    }
    public function scopeOfType(Builder $query, NotificationType|string $type): Builder
    {
        $typeValue = $type instanceof NotificationType ? $type->value : $type;
        return $query->where('type', $typeValue);
    }
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }
}
