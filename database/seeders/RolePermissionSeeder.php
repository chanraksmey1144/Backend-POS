<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashier = Role::where('key', 'cashier')->first();
        if ($cashier) {
            $cashierPermissions = [
                'sales.view',
                'sales.create',
                'registers.open',
                'registers.close',
                'products.view',
                'customers.view',
                'customers.create',
            ];
            foreach ($cashierPermissions as $perm) {
                RolePermission::firstOrCreate([
                    'role_id'    => $cashier->id,
                    'permission' => $perm,
                ]);
            }

            $manager = Role::where('key', 'manager')->first();
            if ($manager) {
                $managerPermissions = [
                    'products.view',
                    'products.create',
                    'products.edit',
                    'inventory.view',
                    'inventory.adjust',
                    'inventory.transfer',
                    'sales.view',
                    'sales.create',
                    'sales.refund',
                    'reports.view',
                ];
                foreach ($managerPermissions as $perm) {
                    RolePermission::firstOrCreate([
                        'role_id'    => $manager->id,
                        'permission' => $perm,
                    ]);
                }
            }
        }
    }
}
