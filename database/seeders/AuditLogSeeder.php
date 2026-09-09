<?php

namespace Database\Seeders;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 $userId = User::query()->first()?->id ?? 1;
        $logs = [
            [
                'user_id'     => $userId,
                'action'      => AuditAction::LOGIN,
                'module'      => 'auth',
                'record'      => (string) $userId,
                'description' => 'User logged in via API.',
            ],
            [
                'user_id'     => $userId,
                'action'      => AuditAction::CREATE,
                'module'      => 'products',
                'record'      => 'PROD-101',
                'description' => 'Created product "Mechanical Keyboard AKKO".',
            ],
            [
                'user_id'     => $userId,
                'action'      => AuditAction::RECEIVE,
                'module'      => 'purchases',
                'record'      => 'PO-2026-001',
                'description' => 'Received 50 units into Warehouse A.',
            ],
            [
                'user_id'     => $userId,
                'action'      => AuditAction::PAYMENT,
                'module'      => 'sales',
                'record'      => 'ORD-2026-001',
                'description' => 'Received payment of $125.00.',
            ],
            [
                'user_id'     => $userId,
                'action'      => AuditAction::TRANSFER,
                'module'      => 'inventory',
                'record'      => 'TRF-001',
                'description' => 'Transferred 10 items from Warehouse A to Branch 1.',
            ],
        ];
        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
