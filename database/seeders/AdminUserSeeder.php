<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or update the default admin user (admin@pos.com).
     */
    public function run(): void
    {
        $adminRole = Role::where('key', 'admin')->firstOrCreate(
            ['key' => 'admin'],
            ['name' => 'Administrator', 'grant_all' => true]
        );

        $branch = Branch::firstOrCreate(
            ['id' => 1],
            [
                'name'    => 'Headquarters Jakarta',
                'code'    => 'HQ-JKT-01',
                'phone'   => '+628123456789',
                'address' => 'Jl. Sudirman No. 10',
                'status'  => 'active',
            ]
        );

        $email = 'admin@pos.com';

        User::updateOrCreate(
            ['email' => $email],
            [
                'role_id'       => $adminRole->id,
                'branch_id'     => $branch->id,
                'name'          => 'System Administrator',
                'password_hash' => Hash::make('12345678'),
                'phone'         => '+628123456789',
                'status'        => 'active',
            ]
        );

        $this->command->info("Admin user '{$email}' seeded with password '12345678'.");
    }
}
