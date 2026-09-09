<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    
    protected $table = 'audit_logs';
    // The table only has created_at
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'record',
        'description',
    ];
    protected $casts = [
        'user_id'    => 'integer',
        'action'     => AuditAction::class,
        'created_at' => 'datetime',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
        public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
    public function scopeOfAction(Builder $query, AuditAction|string $action): Builder
    {
        $val = $action instanceof AuditAction ? $action->value : $action;
        return $query->where('action', $val);
    }
    public function scopeOfModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }
    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to));
    }
    
    /**
     * Easy helper to write audit logs from any Controller or Service:
     * e.g., AuditLog::log('create', 'sales', 'ORD-101', 'Sale order created');
     */
    public static function log(
        AuditAction|string $action,
        string $module,
        ?string $record = null,
        ?string $description = null,
        ?int $userId = null
    ): self {
        return self::create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action instanceof AuditAction ? $action->value : $action,
            'module'      => $module,
            'record'      => $record,
            'description' => $description,
        ]);
    }

}
