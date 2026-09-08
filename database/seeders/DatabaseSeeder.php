<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\Register;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
 // 1. Roles
        $adminRole = Role::firstOrCreate(
            ['key' => 'admin'],
            ['name' => 'Administrator', 'grant_all' => true]
        );
        $cashierRole = Role::firstOrCreate(
            ['key' => 'cashier'],
            ['name' => 'Cashier', 'grant_all' => false]
        );
        // 2. Branch #1
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
        // 3. Register #1 (linked to Branch 1)
        Register::firstOrCreate(
            ['id' => 1],
            [
                'branch_id' => $branch->id,
                'name'      => 'Main POS Register 01',
                'code'      => 'REG-01',
                'status'    => 'active',
            ]
        );
        // 4. Cashier User #1 (linked to Branch 1 & Role 1)
        User::firstOrCreate(
            ['id' => 1],
            [
                'role_id'       => $adminRole->id,
                'branch_id'     => $branch->id,
                'name'          => 'Budi Santoso (Cashier)',
                'email'         => 'budi.cashier@pos.com',
                'password_hash' => bcrypt('Password123!'),
                'phone'         => '+6281234567890',
                'status'        => 'active',
            ]
        );
        // 5. Admin User #1 (admin@pos.com)
        $this->call(AdminUserSeeder::class);

        // 6. Customer Group #1 & Customer #1
        $group = CustomerGroup::firstOrCreate(
            ['id' => 1],
            ['name' => 'VIP Members', 'discount_percent' => 10.00]
        );
        Customer::firstOrCreate(
            ['id' => 1],
            [
                'group_id' => $group->id,
                'name'     => 'PT Jaya Abadi Sentosa',
                'email'    => 'client@jayaabadi.com',
                'phone'    => '+6281987654321',
                'status'   => 'active',
            ]
        );
        // 6. Warehouse, Category, Brand, Unit & Product #1
        Warehouse::firstOrCreate(
            ['id' => 1],
            ['branch_id' => $branch->id, 'name' => 'Central Storage', 'code' => 'WH-01', 'status' => 'active']
        );
        Category::firstOrCreate(['id' => 1], ['name' => 'Beverages', 'code' => 'CAT-BEV', 'status' => 'active']);
        Brand::firstOrCreate(['id' => 1], ['name' => 'Coca-Cola', 'code' => 'BRD-COKE', 'status' => 'active']);
        Unit::firstOrCreate(['id' => 1], ['name' => 'Pieces', 'short_name' => 'pcs', 'status' => 'active']);
        Supplier::firstOrCreate(['id' => 1], ['name' => 'PT Sumber Pangan', 'status' => 'active']);
        Product::firstOrCreate(
            ['id' => 1],
            [
                'category_id' => 1,
                'brand_id'    => 1,
                'unit_id'     => 1,
                'name'        => 'Coca-Cola Can 330ml',
                'sku'         => 'COKE-330',
                'price'       => 8500.00,
                'cost'        => 5000.00,
                'stock'       => 100.000,
                'status'      => 'active',
            ]
        );
    }
}
