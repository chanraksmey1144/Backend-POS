<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'key'         => 'admin',
                'name'        => 'Administrator',
                'description' => 'Full access with wildcards to all features and settings',
                'grant_all'   => true,
            ],
            [
                'key'         => 'manager',
                'name'        => 'Branch Manager',
                'description' => 'Manages branch operations, inventories, and reports',
                'grant_all'   => false,
            ],
            [
                'key'         => 'cashier',
                'name'        => 'Cashier',
                'description' => 'Handles POS register sales, opening/closing cash drawers',
                'grant_all'   => false,
            ],
            [
                'key'         => 'accountant',
                'name'        => 'Accountant',
                'description' => 'Access to financial statements, taxes, and ledgers',
                'grant_all'   => false,
            ],
            [
                'key'         => 'viewer',
                'name'        => 'Viewer',
                'description' => 'Read-only access for auditing and viewing purposes',
                'grant_all'   => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['key' => $role['key']], $role);
        }
    }
}
