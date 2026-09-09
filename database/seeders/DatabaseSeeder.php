<?php

namespace Database\Seeders;

use App\Enums\AuditAction;
use App\Enums\NotificationType;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\CashRegisterSession;
use App\Models\CashTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Expense;
use App\Models\HeldSale;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Register;
use App\Models\ReturnItem;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\TransferItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with rich, referentially-consistent
     * demo data for every module of the Inventory & POS system.
     */
    public function run(): void
    {
        $this->resetTransactionalTables();

        // -------------------------------------------------------------------
        // Roles & role permissions
        // -------------------------------------------------------------------
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class);

        $this->seedOrganization();
        $this->seedUsers();
        $this->seedCustomers();
        $this->seedCatalog();
        $this->seedSuppliers();
        $this->seedPurchases();
        $this->seedSales();
        $this->seedReturns();
        $this->seedExpenses();
        $this->seedStockTransfers();
        $this->seedCashRegister();
        $this->seedHeldSales();
        $this->seedNotifications();
        $this->seedAuditLogs();

        $this->call(AdminUserSeeder::class);
        $this->call(SettingSeeder::class);

        $this->command->info('Database seeded with demo data for the Inventory & POS system.');
    }

    // -----------------------------------------------------------------------
    // Resettable (transactional) tables so the seeder can be re-run safely
    // -----------------------------------------------------------------------
    private function resetTransactionalTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $tables = [
            'sale_items', 'sale_returns', 'return_items', 'held_sales',
            'purchase_items', 'purchases', 'transfer_items', 'stock_transfers',
            'stock_movements', 'expenses', 'cash_transactions',
            'cash_register_sessions', 'sales', 'notifications', 'audit_logs',
            'role_permissions',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -----------------------------------------------------------------------
    // 1. Organization structure
    // -----------------------------------------------------------------------
    private function seedOrganization(): void
    {
        $branches = [
            ['id' => 1, 'name' => 'Main Branch',          'code' => 'MAIN', 'phone' => '+855 23 999 111', 'email' => 'main@storemaster.com',    'address' => 'Phnom Penh, Toul Kork, Street 310', 'status' => 'active'],
            ['id' => 2, 'name' => 'City Mall Branch',     'code' => 'CTY',  'phone' => '+855 23 999 222', 'email' => 'city@storemaster.com',    'address' => 'Phnom Penh, Boeung Keng Kang, Mao Tse Tung Blvd', 'status' => 'active'],
            ['id' => 3, 'name' => 'Airport Branch',       'code' => 'AIR',  'phone' => '+855 23 999 333', 'email' => 'airport@storemaster.com', 'address' => 'Phnom Penh, Sen Sok, Airport Road', 'status' => 'active'],
            ['id' => 4, 'name' => 'Takhmau Branch',       'code' => 'TKM',  'phone' => '+855 23 999 444', 'email' => 'takhmau@storemaster.com', 'address' => 'Kandal, Takhmau, National Road 21', 'status' => 'inactive'],
        ];
        foreach ($branches as $row) {
            Branch::firstOrCreate(['id' => $row['id']], $row);
        }

        $warehouses = [
            ['id' => 1, 'branch_id' => 1, 'name' => 'Main Warehouse',       'code' => 'WH-MAIN', 'address' => 'Phnom Penh, Toul Kork',      'status' => 'active'],
            ['id' => 2, 'branch_id' => 2, 'name' => 'City Mall Store',      'code' => 'WH-CTY',  'address' => 'Phnom Penh, Boeung Keng Kang', 'status' => 'active'],
            ['id' => 3, 'branch_id' => 3, 'name' => 'Airport Distribution', 'code' => 'WH-AIR',  'address' => 'Phnom Penh, Sen Sok',       'status' => 'active'],
            ['id' => 4, 'branch_id' => 4, 'name' => 'Takhmau Storage',      'code' => 'WH-TKM',  'address' => 'Kandal, Takhmau',           'status' => 'inactive'],
        ];
        foreach ($warehouses as $row) {
            Warehouse::firstOrCreate(['id' => $row['id']], $row);
        }

        $registers = [
            ['id' => 1, 'branch_id' => 1, 'name' => 'Front Register 1',    'code' => 'REG-01', 'status' => 'active'],
            ['id' => 2, 'branch_id' => 1, 'name' => 'Front Register 2',    'code' => 'REG-02', 'status' => 'active'],
            ['id' => 3, 'branch_id' => 2, 'name' => 'City Mall Register',  'code' => 'REG-03', 'status' => 'active'],
            ['id' => 4, 'branch_id' => 3, 'name' => 'Airport Register',    'code' => 'REG-04', 'status' => 'inactive'],
        ];
        foreach ($registers as $row) {
            Register::firstOrCreate(['id' => $row['id']], $row);
        }
    }

    // -----------------------------------------------------------------------
    // 2. Users
    // -----------------------------------------------------------------------
    private function seedUsers(): void
    {
        $roles = Role::all()->keyBy('key');

        $users = [
            ['id' => 1, 'email' => 'demo@storemaster.com',   'name' => 'Admin',       'role' => 'admin',      'branch' => 1, 'status' => 'active',   'phone' => '+855 12 000 001'],
            ['id' => 2, 'email' => 'sreyleap@storemaster.com', 'name' => 'Srey Leap', 'role' => 'cashier',    'branch' => 1, 'status' => 'active',   'phone' => '+855 12 000 002'],
            ['id' => 3, 'email' => 'dara@storemaster.com',   'name' => 'Dara',       'role' => 'cashier',    'branch' => 2, 'status' => 'active',   'phone' => '+855 12 000 003'],
            ['id' => 4, 'email' => 'bopha@storemaster.com',  'name' => 'Bopha',      'role' => 'manager',    'branch' => 1, 'status' => 'active',   'phone' => '+855 12 000 004'],
            ['id' => 5, 'email' => 'ronan@storemaster.com',  'name' => 'Ronan',      'role' => 'accountant', 'branch' => 1, 'status' => 'active',   'phone' => '+855 12 000 005'],
            ['id' => 6, 'email' => 'kosal@storemaster.com',  'name' => 'Kosal',      'role' => 'viewer',     'branch' => 2, 'status' => 'inactive', 'phone' => '+855 12 000 006'],
        ];

        foreach ($users as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'id'            => $row['id'],
                    'role_id'       => $roles[$row['role']]->id,
                    'branch_id'     => $row['branch'],
                    'name'          => $row['name'],
                    'phone'         => $row['phone'],
                    'password_hash' => Hash::make('Password123!'),
                    'status'        => $row['status'],
                ]
            );
        }

        $this->command->info('Users seeded. Login: demo@storemaster.com / Password123!');
    }

    // -----------------------------------------------------------------------
    // 3. Customers
    // -----------------------------------------------------------------------
    private function seedCustomers(): void
    {
        $groups = [
            ['id' => 1, 'name' => 'Regular',   'discount_percent' => 0],
            ['id' => 2, 'name' => 'VIP',       'discount_percent' => 5],
            ['id' => 3, 'name' => 'Wholesale', 'discount_percent' => 10],
        ];
        foreach ($groups as $row) {
            CustomerGroup::firstOrCreate(['id' => $row['id']], $row);
        }

        $customers = [
            ['id' => 1,  'name' => 'Sokha Chea',    'email' => 'sokha.chea@gmail.com',     'phone' => '+855 12 345 678', 'group' => 2, 'loyalty' => 1240, 'spent' => 1850.40, 'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Toul Kork'],
            ['id' => 2,  'name' => 'Dara Kim',      'email' => 'dara.kim@outlook.com',     'phone' => '+855 97 234 567', 'group' => 1, 'loyalty' => 320,  'spent' => 640.90,  'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Boeung Keng Kang'],
            ['id' => 3,  'name' => 'Malis Sok',     'email' => 'malis.sok@gmail.com',      'phone' => '+855 16 789 012', 'group' => 2, 'loyalty' => 890,  'spent' => 1320.75, 'outstanding' => 45.50,   'status' => 'active',   'address' => 'Kandal, Takhmau'],
            ['id' => 4,  'name' => 'Vichea Long',   'email' => 'vichea.long@gmail.com',    'phone' => '+855 92 456 789', 'group' => 1, 'loyalty' => 150,  'spent' => 285.00,  'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Mean Chey'],
            ['id' => 5,  'name' => 'Sreypov Chan',  'email' => 'sreypov.chan@gmail.com',   'phone' => '+855 70 123 456', 'group' => 3, 'loyalty' => 2100, 'spent' => 4210.00, 'outstanding' => 320.75, 'status' => 'active',   'address' => 'Phnom Penh, Sen Sok'],
            ['id' => 6,  'name' => 'Rithy Sam',     'email' => 'rithy.sam@gmail.com',      'phone' => '+855 89 654 321', 'group' => 1, 'loyalty' => 85,   'spent' => 160.25,  'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Dangkao'],
            ['id' => 7,  'name' => 'Sreyneang Oum', 'email' => 'sreyneang.oum@gmail.com',  'phone' => '+855 12 987 654', 'group' => 2, 'loyalty' => 640,  'spent' => 980.30,  'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Chamkar Mon'],
            ['id' => 8,  'name' => 'Vannak Heng',   'email' => 'vannak.heng@gmail.com',    'phone' => '+855 99 876 543', 'group' => 1, 'loyalty' => 40,   'spent' => 92.50,   'outstanding' => 0,      'status' => 'active',   'address' => 'Kampong Speu'],
            ['id' => 9,  'name' => 'Channary Pen',  'email' => 'channary.pen@gmail.com',   'phone' => '+855 15 555 888', 'group' => 2, 'loyalty' => 1780, 'spent' => 2650.60, 'outstanding' => 0,      'status' => 'active',   'address' => 'Phnom Penh, Russey Keo'],
            ['id' => 10, 'name' => 'Borey Phon',    'email' => 'borey.phon@gmail.com',     'phone' => '+855 93 222 111', 'group' => 1, 'loyalty' => 210,  'spent' => 410.00,  'outstanding' => 0,      'status' => 'inactive', 'address' => 'Phnom Penh, Por Sen Chey'],
        ];
        foreach ($customers as $row) {
            Customer::firstOrCreate(['id' => $row['id']], [
                'group_id'       => $row['group'],
                'name'           => $row['name'],
                'email'          => $row['email'],
                'phone'          => $row['phone'],
                'address'        => $row['address'],
                'loyalty_points' => $row['loyalty'],
                'total_spent'    => $row['spent'],
                'outstanding'    => $row['outstanding'],
                'status'         => $row['status'],
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 4. Catalog
    // -----------------------------------------------------------------------
    private function seedCatalog(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Beverages',          'code' => 'BEV'],
            ['id' => 2, 'name' => 'Food & Snacks',      'code' => 'FOD'],
            ['id' => 3, 'name' => 'Dairy & Bakery',     'code' => 'DRY'],
            ['id' => 4, 'name' => 'Staples & Rice',     'code' => 'STP'],
            ['id' => 5, 'name' => 'Electronics',        'code' => 'ELE'],
            ['id' => 6, 'name' => 'Apparel',            'code' => 'APR'],
            ['id' => 7, 'name' => 'Personal Care',      'code' => 'PCR'],
            ['id' => 8, 'name' => 'Cleaning Supplies',  'code' => 'CLN'],
        ];
        foreach ($categories as $row) {
            Category::firstOrCreate(['id' => $row['id']], ['name' => $row['name'], 'code' => $row['code'], 'status' => 'active']);
        }

        $brands = [
            ['id' => 1, 'name' => 'Coca-Cola',     'code' => 'COCA'],
            ['id' => 2, 'name' => 'PepsiCo',       'code' => 'PEPS'],
            ['id' => 3, 'name' => 'Nestlé',        'code' => 'NEST'],
            ['id' => 4, 'name' => 'Ace Data',      'code' => 'ACED'],
            ['id' => 5, 'name' => 'Logitech',      'code' => 'LOGI'],
            ['id' => 6, 'name' => 'Unilever',      'code' => 'UNIL'],
            ['id' => 7, 'name' => 'Golden Harvest','code' => 'GLDH'],
            ['id' => 8, 'name' => 'No Brand',      'code' => 'NBRN'],
        ];
        foreach ($brands as $row) {
            Brand::firstOrCreate(['id' => $row['id']], ['name' => $row['name'], 'code' => $row['code'], 'status' => 'active']);
        }

        $units = [
            ['id' => 1, 'name' => 'Piece', 'short_name' => 'pc'],
            ['id' => 2, 'name' => 'Bottle', 'short_name' => 'btl'],
            ['id' => 3, 'name' => 'Can', 'short_name' => 'can'],
            ['id' => 4, 'name' => 'Pack', 'short_name' => 'pk'],
            ['id' => 5, 'name' => 'Kilogram', 'short_name' => 'kg'],
            ['id' => 6, 'name' => 'Liter', 'short_name' => 'L'],
            ['id' => 7, 'name' => 'Bag', 'short_name' => 'bag'],
            ['id' => 8, 'name' => 'Box', 'short_name' => 'box'],
        ];
        foreach ($units as $row) {
            Unit::firstOrCreate(['id' => $row['id']], ['name' => $row['name'], 'short_name' => $row['short_name'], 'status' => 'active']);
        }

        // [id, name, sku, barcode, cat, brand, unit, cost, price, wholesale, min, max, stock, label, color, variants]
        $products = [
            [1,  'Coca-Cola 500ml',        'CC-500ML',   '8991000000101', 1, 1, 2, 0.55, 0.75, 0.65, 48,  480, 240, 'CC', '#0f172a', []],
            [2,  'Pepsi 500ml',            'PS-500ML',   '8991000000102', 1, 2, 2, 0.52, 0.70, 0.60, 48,  480, 36,  'PE', '#0d9488', []],
            [3,  'Mineral Water 500ml',    'MW-500ML',   '8991000000103', 1, 8, 2, 0.18, 0.25, 0.20, 96,  960, 0,   'MW', '#38bdf8', []],
            [4,  'Fresh Milk 1L',          'FM-1L',      '8991000000104', 3, 3, 6, 1.20, 1.60, 1.40, 24,  240, 96,  'FM', '#7dd3fc', []],
            [5,  'White Bread Loaf',       'WB-LOAF',    '8991000000105', 3, 8, 1, 0.90, 1.25, 1.05, 20,  200, 40,  'WB', '#d97706', []],
            [6,  'Arabica Coffee Beans 250g', 'AC-250G', '8991000000106', 1, 3, 4, 4.20, 6.00, 5.20, 12,  120, 62,  'AC', '#92400e', []],
            [7,  'Jasmine Rice 5kg',       'JR-5KG',     '8991000000107', 4, 7, 7, 7.50, 9.50, 8.50, 10,  100, 34,  'JR', '#a16207', []],
            [8,  'Instant Noodles 5-Pack', 'IN-5PK',     '8991000000108', 2, 8, 4, 1.10, 1.50, 1.25, 60,  600, 210, 'IN', '#ea580c', []],
            [9,  'Potato Chips 80g',       'PC-80G',     '8991000000109', 2, 8, 4, 0.60, 0.90, 0.75, 40,  400, 150, 'PC', '#f59e0b', []],
            [10, 'Chocolate Bar 100g',     'CB-100G',    '8991000000110', 2, 3, 1, 1.30, 1.80, 1.50, 30,  300, 85,  'CB', '#78350f', []],
            [11, 'Wireless Mouse',         'EL-WMOUSE',  '8991000000111', 5, 5, 1, 4.50, 7.50, 6.20, 5,   50,  18,  'WM', '#334155', []],
            [12, 'Mechanical Keyboard',    'EL-KEYBRD',  '8991000000112', 5, 4, 1, 18.0, 28.0, 24.0, 5,   30,  6,   'MK', '#1e293b', []],
            [13, 'USB-C Cable 1m',         'EL-USBC',    '8991000000113', 5, 5, 1, 1.50, 2.50, 2.00, 20,  200, 120, 'UC', '#475569', []],
            [14, 'Basic T-Shirt',          'AP-TSHIRT',  '8991000000114', 6, 8, 1, 3.00, 5.00, 4.20, 12,  120, 60,  'TS', '#0ea5e9', [
                ['name' => 'S / Black', 'sku' => 'AP-TSHIRT-S-BLK', 'barcode' => '8991000000114A', 'stock' => 12],
                ['name' => 'M / Black', 'sku' => 'AP-TSHIRT-M-BLK', 'barcode' => '8991000000114B', 'stock' => 18],
                ['name' => 'L / Black', 'sku' => 'AP-TSHIRT-L-BLK', 'barcode' => '8991000000114C', 'stock' => 15],
                ['name' => 'S / White', 'sku' => 'AP-TSHIRT-S-WHT', 'barcode' => '8991000000114D', 'stock' => 9],
                ['name' => 'M / White', 'sku' => 'AP-TSHIRT-M-WHT', 'barcode' => '8991000000114E', 'stock' => 6],
                ['name' => 'L / White', 'sku' => 'AP-TSHIRT-L-WHT', 'barcode' => '8991000000114F', 'stock' => 0],
            ]],
            [15, 'Denim Jeans',            'AP-JEANS',   '8991000000115', 6, 8, 1, 8.00, 14.0, 11.0, 8,   80,  24,  'DJ', '#3b82f6', [
                ['name' => '28', 'sku' => 'AP-JEANS-28', 'barcode' => '8991000000115A', 'stock' => 8],
                ['name' => '30', 'sku' => 'AP-JEANS-30', 'barcode' => '8991000000115B', 'stock' => 10],
                ['name' => '32', 'sku' => 'AP-JEANS-32', 'barcode' => '8991000000115C', 'stock' => 6],
            ]],
            [16, 'Sports Cap',             'AP-CAP',     '8991000000116', 6, 8, 1, 2.00, 3.50, 2.80, 10,  100, 0,   'SC', '#6366f1', []],
            [17, 'Shampoo 400ml',          'PC-SHAMPOO', '8991000000117', 7, 6, 2, 2.80, 4.20, 3.50, 12,  120, 44,  'SH', '#db2777', []],
            [18, 'Toothpaste 150g',        'PC-TOOTH',   '8991000000118', 7, 6, 1, 1.00, 1.60, 1.30, 20,  200, 76,  'TP', '#06b6d4', []],
            [19, 'Body Soap 250g',         'PC-SOAP',    '8991000000119', 7, 6, 1, 0.70, 1.10, 0.90, 24,  240, 8,   'BS', '#c026d3', []],
            [20, 'Dish Soap 750ml',        'CL-DISH',    '8991000000120', 8, 6, 2, 1.40, 2.00, 1.70, 16,  160, 92,  'DS', '#22c55e', []],
            [21, 'Laundry Detergent 2kg',  'CL-DETER',   '8991000000121', 8, 6, 7, 3.50, 5.00, 4.20, 10,  100, 30,  'LD', '#84cc16', []],
            [22, 'Cooking Oil 1L',         'ST-OIL1L',   '8991000000122', 4, 8, 6, 2.60, 3.40, 2.90, 15,  150, 58,  'CO', '#ca8a04', []],
            [23, 'Sugar 1kg',              'ST-SUGAR',   '8991000000123', 4, 8, 7, 1.20, 1.70, 1.40, 20,  200, 68,  'SU', '#facc15', []],
            [24, 'Green Tea 500ml',        'BEV-GTEA',   '8991000000124', 1, 2, 2, 0.45, 0.65, 0.55, 36,  360, 120, 'GT', '#16a34a', []],
        ];

        foreach ($products as $row) {
            [, $name, $sku, $barcode, $cat, $brand, $unit, $cost, $price, $wholesale, $min, $max, $stock, $label, $color, $variants] = $row;

            Product::updateOrCreate(['sku' => $sku], [
                'id'              => $row[0],
                'category_id'     => $cat,
                'brand_id'        => $brand,
                'unit_id'         => $unit,
                'name'            => $name,
                'barcode'         => $barcode,
                'description'     => "{$name} — demo product.",
                'image_label'     => $label,
                'image_color'     => $color,
                'cost'            => $cost,
                'price'           => $price,
                'wholesale_price' => $wholesale,
                'tax_percent'     => 10,
                'track_inventory' => true,
                'min_stock'       => $min,
                'max_stock'       => $max,
                'stock'           => $stock,
                'status'          => 'active',
            ]);

            $product = Product::where('sku', $sku)->first();
            foreach ($variants as $variant) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variant['sku']],
                    [
                        'product_id' => $product->id,
                        'name'       => $variant['name'],
                        'barcode'    => $variant['barcode'],
                        'stock'      => $variant['stock'],
                        'cost'       => $cost,
                        'price'      => $price,
                    ]
                );
            }
        }
    }

    // -----------------------------------------------------------------------
    // 5. Suppliers
    // -----------------------------------------------------------------------
    private function seedSuppliers(): void
    {
        $suppliers = [
            ['id' => 1, 'name' => 'Angkor Beverage Co.',       'contact' => 'Mr. Kheang',  'email' => 'sales@angkorbeverage.com',  'phone' => '+855 23 111 222', 'tax' => 'KH-001-2233', 'address' => 'Phnom Penh, Chbar Ampov',       'total' => 12850.50, 'outstanding' => 1250.00, 'status' => 'active'],
            ['id' => 2, 'name' => 'Mekong Food Distribution',  'contact' => 'Ms. Linda',   'email' => 'orders@mekongfood.com',     'phone' => '+855 12 555 666', 'tax' => 'KH-002-3344', 'address' => 'Phnom Penh, Stung Meanchey',    'total' => 9240.75,  'outstanding' => 0,       'status' => 'active'],
            ['id' => 3, 'name' => 'Rieltech Electronics',      'contact' => 'Mr. Sovann',  'email' => 'b2b@rieltech.com',          'phone' => '+855 97 777 888', 'tax' => 'KH-003-4455', 'address' => 'Phnom Penh, Toul Tompoung',     'total' => 15750.00, 'outstanding' => 3200.00, 'status' => 'active'],
            ['id' => 4, 'name' => 'Green Fields Rice Mill',    'contact' => 'Mr. Ratha',   'email' => 'info@greenfields-rice.com', 'phone' => '+855 11 333 444', 'tax' => 'KH-004-5566', 'address' => 'Battambang Province',           'total' => 6840.25,  'outstanding' => 0,       'status' => 'active'],
            ['id' => 5, 'name' => 'Sunrise Dairy Products',    'contact' => 'Ms. Sreymom', 'email' => 'sales@sunrisedairy.com',    'phone' => '+855 69 888 999', 'tax' => 'KH-005-6677', 'address' => 'Kampong Cham Province',         'total' => 5120.00,  'outstanding' => 640.00,  'status' => 'active'],
            ['id' => 6, 'name' => 'Evergreen Apparel',         'contact' => 'Mr. Visal',   'email' => 'wholesale@evergreenapparel.com', 'phone' => '+855 88 222 333', 'tax' => 'KH-006-7788', 'address' => 'Phnom Penh, Russian Market', 'total' => 3960.00,  'outstanding' => 0,       'status' => 'inactive'],
        ];
        foreach ($suppliers as $row) {
            Supplier::firstOrCreate(['id' => $row['id']], [
                'name'            => $row['name'],
                'contact_person'  => $row['contact'],
                'email'           => $row['email'],
                'phone'           => $row['phone'],
                'tax_number'      => $row['tax'],
                'address'         => $row['address'],
                'total_purchases' => $row['total'],
                'outstanding'     => $row['outstanding'],
                'status'          => $row['status'],
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 6. Purchases (restock history)
    // -----------------------------------------------------------------------
    private function seedPurchases(): void
    {
        $products = Product::orderBy('id')->get();
        $supplierChoices = [1, 2, 3, 4, 5, 6];

        // [number, daysAgo, supplierOffset, productStart, productCount, status]
        $raw = [
            ['PO-2026-0001', 62, 4, 0, 5, 'draft'],
            ['PO-2026-0002', 60, 4, 1, 8, 'ordered'],
            ['PO-2026-0003', 58, 4, 2, 8, 'received'],
            ['PO-2026-0004', 56, 3, 4, 6, 'received'],
            ['PO-2026-0005', 54, 3, 6, 6, 'received'],
            ['PO-2026-0006', 50, 2, 8, 5, 'received'],
            ['PO-2026-0007', 47, 2, 10, 5, 'received'],
            ['PO-2026-0008', 43, 1, 12, 4, 'received'],
            ['PO-2026-0009', 39, 1, 14, 4, 'received'],
            ['PO-2026-0010', 36, 1, 18, 4, 'received'],
            ['PO-2026-0011', 32, 1, 21, 3, 'received'],
            ['PO-2026-0012', 28, 1, 25, 3, 'received'],
            ['PO-2026-0013', 25, 1, 30, 3, 'received'],
            ['PO-2026-0014', 20, 1, 38, 3, 'received'],
            ['PO-2026-0015', 15, 1, 45, 2, 'received'],
        ];

        foreach ($raw as $i => [$number, $days, $offset, $start, $count, $status]) {
            $received   = $status === 'received';
            $orderDate  = now()->subDays($days)->setTime(9, 0, 0);
            $slots      = array_slice($products->all(), $start, $count);

            $items = [];
            $subtotal = 0;
            foreach ($slots as $product) {
                $qty      = 24;
                $subtotal += $product->cost * $qty;
                $items[]  = [
                    'product_id'        => $product->id,
                    'name'              => $product->name,
                    'sku'               => $product->sku,
                    'cost'              => $product->cost,
                    'quantity'          => $qty,
                    'received_quantity' => $received ? $qty : 0,
                ];
            }
            $subtotal = round($subtotal, 2);
            $tax      = round($subtotal * 0.05, 2);
            $total    = round($subtotal + $tax, 2);

            $purchase = Purchase::updateOrCreate(['purchase_number' => $number], [
                'supplier_id'     => $supplierChoices[($i + $offset) % 6],
                'branch_id'       => 1,
                'warehouse_id'    => 1,
                'order_date'      => $orderDate,
                'expected_date'   => $orderDate->copy()->subDays(2),
                'received_at'     => $received ? $orderDate : null,
                'subtotal'        => $subtotal,
                'discount'        => 0,
                'tax'             => $tax,
                'total'           => $total,
                'status'          => $status,
                'payment_status'  => $received ? 'paid' : 'unpaid',
                'notes'           => $status === 'ordered' ? 'Awaiting supplier delivery' : '',
                'created_by'      => 1,
            ]);
            $purchase->forceFill(['created_at' => $orderDate, 'updated_at' => $orderDate])->save();

            foreach ($items as $item) {
                PurchaseItem::updateOrCreate(
                    ['purchase_id' => $purchase->id, 'product_id' => $item['product_id'], 'sku' => $item['sku']],
                    [...$item, 'purchase_id' => $purchase->id]
                );
            }

            if ($received) {
                foreach ($items as $item) {
                    StockMovement::updateOrCreate(
                        ['type' => 'purchase', 'reference' => $number, 'product_id' => $item['product_id']],
                        [
                            'warehouse_id'  => 1,
                            'user_id'       => 1,
                            'movement_date' => $orderDate,
                            'quantity'      => $item['quantity'],
                            'note'          => "Purchase received into warehouse #1 ({$purchase->purchase_number}).",
                        ]
                    );
                }
            }
        }
    }

    // -----------------------------------------------------------------------
    // 7. Sales (+ POS movement history)
    // -----------------------------------------------------------------------
    private function seedSales(): void
    {
        $products = Product::orderBy('id')->get();
        $methods  = ['cash', 'cash', 'cash', 'card', 'qr', 'mobile_payment', 'bank_transfer'];
        $state    = 20260701;

        for ($i = 0; $i < 48; $i++) {
            $daysAgo = (int) floor($this->rnd($state) * 90);
            $hour    = 8 + (int) floor($this->rnd($state) * 12);
            $minute  = (int) floor($this->rnd($state) * 60);
            $saleDate = now()->subDays($daysAgo)->setTime($hour, $minute, 0);

            $itemCount = 1 + (int) floor($this->rnd($state) * 5);
            $chosen = [];
            while (count($chosen) < $itemCount) {
                $product = $products[(int) floor($this->rnd($state) * $products->count())];
                $chosen[$product->id] = $product;
            }

            $items = [];
            $lineSubtotal = 0;
            foreach ($chosen as $product) {
                $qty = $this->rnd($state) < 0.7 ? 1 : 2;
                $items[] = [
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'sku'        => $product->sku,
                    'price'      => $product->price,
                    'cost'       => $product->cost,
                    'quantity'   => $qty,
                    'discount'   => 0,
                    'tax'        => $product->tax_percent,
                ];
                $lineSubtotal += $product->price * $qty;
            }

            $subtotal = round($lineSubtotal, 2);
            $discount = 0;
            if ($subtotal > 30) {
                $discount = round($subtotal * 0.05, 2);
            }
            $afterDiscount = round($subtotal - $discount, 2);
            $tax   = round($afterDiscount * 0.10, 2);
            $total = round($afterDiscount + $tax, 2);

            $method = $methods[(int) floor($this->rnd($state) * count($methods))];
            $paid   = $method === 'card' || $this->rnd($state) > 0.3 ? $total : round($total - 5, 2);
            $paid   = max(0, $paid);
            $change = $paid >= $total ? round($paid - $total, 2) : 0;

            $invoice = sprintf('INV-2026-%04d', 4001 + $i);

            $sale = Sale::updateOrCreate(['invoice_number' => $invoice], [
                'customer_id'    => 1 + (int) floor($this->rnd($state) * 9),
                'cashier_id'     => $this->rnd($state) < 0.5 ? 2 : 3,
                'branch_id'      => 1,
                'register_id'    => $this->rnd($state) < 0.5 ? 1 : 2,
                'sale_date'      => $saleDate,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => $change,
                'payment_method' => $method,
                'status'         => 'completed',
                'payment_status' => $paid >= $total ? 'paid' : 'partial',
                'notes'          => '',
                'created_by'     => 1,
            ]);
            $sale->forceFill(['created_at' => $saleDate, 'updated_at' => $saleDate])->save();

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'name'       => $item['name'],
                    'sku'        => $item['sku'],
                    'price'      => $item['price'],
                    'cost'       => $item['cost'],
                    'quantity'   => $item['quantity'],
                    'discount'   => $item['discount'],
                    'tax'        => $item['tax'],
                    'created_at' => $saleDate,
                ]);

                StockMovement::updateOrCreate(
                    ['type' => 'sale', 'reference' => $invoice, 'product_id' => $item['product_id']],
                    [
                        'user_id'       => $sale->cashier_id,
                        'movement_date' => $saleDate,
                        'quantity'      => -$item['quantity'],
                        'note'          => 'Items sold via POS.',
                    ]
                );
            }
        }

        // A couple of low-stock / damage / adjustment movements for extra history
        $extras = [
            ['product_id' => 12, 'type' => 'damage',     'qty' => -1, 'note' => 'Keyboard damaged in transit'],
            ['product_id' => 19, 'type' => 'adjustment', 'qty' => -2, 'note' => 'Stock count discrepancy resolved'],
            ['product_id' => 3,  'type' => 'adjustment', 'qty' => 0,  'note' => 'Out of stock verified'],
        ];
        foreach ($extras as $x) {
            StockMovement::create([
                'product_id'    => $x['product_id'],
                'warehouse_id'  => 1,
                'user_id'       => 1,
                'movement_date' => now()->subDays(5)->setTime(10, 0, 0),
                'type'          => $x['type'],
                'quantity'      => $x['qty'],
                'reference'     => 'ADJ-000' . $x['product_id'],
                'note'          => $x['note'],
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 8. Returns
    // -----------------------------------------------------------------------
    private function seedReturns(): void
    {
        $sale1 = Sale::where('invoice_number', 'INV-2026-4001')->first();
        $sale2 = Sale::where('invoice_number', 'INV-2026-4010')->first();
        $sale3 = Sale::where('invoice_number', 'INV-2026-4020')->first();

        $returnData = [
            [$sale1, 'Customer changed their mind', 2, [
                ['product_id' => 9, 'name' => 'Potato Chips 80g', 'sku' => 'PC-80G', 'price' => 0.90, 'cost' => 0.60, 'quantity' => 1, 'discount' => 0, 'tax' => 10],
            ]],
            [$sale2, 'Damaged item returned', 3, [
                ['product_id' => 1, 'name' => 'Coca-Cola 500ml', 'sku' => 'CC-500ML', 'price' => 0.75, 'cost' => 0.55, 'quantity' => 1, 'discount' => 0, 'tax' => 10],
                ['product_id' => 3, 'name' => 'Mineral Water 500ml', 'sku' => 'MW-500ML', 'price' => 0.25, 'cost' => 0.18, 'quantity' => 2, 'discount' => 0, 'tax' => 10],
            ]],
            [$sale3, 'Wrong size', 2, [
                ['product_id' => 14, 'name' => 'Basic T-Shirt', 'sku' => 'AP-TSHIRT', 'price' => 5.00, 'cost' => 3.00, 'quantity' => 1, 'discount' => 0, 'tax' => 10],
            ]],
        ];

        foreach ($returnData as $i => [$sale, $reason, $cashier, $items]) {
            if (!$sale) {
                continue;
            }
            $refund = array_sum(array_map(fn ($it) => $it['price'] * $it['quantity'], $items));

            $return = SaleReturn::updateOrCreate(['sale_id' => $sale->id, 'reason' => $reason], [
                'branch_id'     => 1,
                'cashier_id'    => $cashier,
                'refund_amount' => round($refund, 2),
            ]);

            foreach ($items as $item) {
                ReturnItem::updateOrCreate(
                    ['return_id' => $return->id, 'sku' => $item['sku']],
                    [...$item, 'return_id' => $return->id]
                );

                StockMovement::updateOrCreate(
                    ['type' => 'return', 'reference' => (string) $sale->id, 'product_id' => $item['product_id']],
                    [
                        'user_id'       => $cashier,
                        'movement_date' => now()->subDays(3 + $i),
                        'quantity'      => $item['quantity'],
                        'note'          => 'Customer return / refund.',
                    ]
                );
            }
        }
    }

    // -----------------------------------------------------------------------
    // 9. Expenses
    // -----------------------------------------------------------------------
    private function seedExpenses(): void
    {
        $raw = [
            [30, 'Rent',           450, 'bank_transfer', 'Monthly store rent for Main Branch'],
            [28, 'Utilities',      85,  'cash',          'Electricity bill'],
            [25, 'Salaries',       1200,'bank_transfer', 'Cashier salaries for the month'],
            [21, 'Supplies',       45,  'cash',          'Receipt paper and plastic bags'],
            [18, 'Transportation', 35,  'cash',          'Delivery fuel'],
            [15, 'Maintenance',    60,  'cash',          'Fridge compressor repair'],
            [12, 'Marketing',      90,  'qr',            'Facebook ads promotion'],
            [9,  'Utilities',      72,  'bank_transfer', 'Water and internet bill'],
            [6,  'Supplies',       28,  'cash',          'Cleaning supplies'],
            [3,  'Other',          40,  'cash',          'Miscellaneous store costs'],
        ];

        foreach ($raw as [$days, $category, $amount, $method, $description]) {
            Expense::create([
                'branch_id'      => 1,
                'created_by'     => 1,
                'category'       => $category,
                'amount'         => $amount,
                'payment_method' => $method,
                'expense_date'   => now()->subDays($days)->setTime(10, 0, 0),
                'description'    => $description,
                'receipt'        => null,
                'created_at'     => now()->subDays($days)->setTime(10, 0, 0),
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 10. Stock transfers
    // -----------------------------------------------------------------------
    private function seedStockTransfers(): void
    {
        $state = 991;

        $transfers = [
            ['TRF-2026-0001', 3, 1, 'received',  12, 'Replenish Main Warehouse'],
            ['TRF-2026-0002', 1, 2, 'in_transit', 6, ''],
            ['TRF-2026-0003', 1, 3, 'requested',  8, 'Emergency restock'],
            ['TRF-2026-0004', 2, 1, 'draft',     10, ''],
        ];

        foreach ($transfers as [$number, $src, $dst, $status, $itemCount, $notes]) {
            $transfer = StockTransfer::updateOrCreate(['transfer_number' => $number], [
                'source_warehouse_id'      => $src,
                'destination_warehouse_id' => $dst,
                'item_count'               => $itemCount,
                'status'                   => $status,
                'notes'                    => $notes,
                'created_by'               => 1,
                'created_at'               => now()->subDays(12),
                'updated_at'               => now()->subDays(12),
            ]);

            for ($j = 0; $j < $itemCount; $j++) {
                $product = Product::find((($j * 3 + $src + $dst) % 24) + 1);
                TransferItem::updateOrCreate(
                    ['transfer_id' => $transfer->id, 'product_id' => $product->id],
                    ['quantity' => 1 + ($j % 3)]
                );
            }

            if ($status === 'received') {
                foreach ($transfer->items as $item) {
                    StockMovement::updateOrCreate(
                        ['type' => 'transfer', 'reference' => $number, 'product_id' => $item->product_id],
                        [
                            'warehouse_id'  => $dst,
                            'user_id'       => 1,
                            'movement_date' => now()->subDays(10),
                            'quantity'      => $item->quantity,
                            'note'          => "Transferred IN from warehouse #{$src}.",
                        ]
                    );
                }
            }
        }
    }

    // -----------------------------------------------------------------------
    // 11. Cash register
    // -----------------------------------------------------------------------
    private function seedCashRegister(): void
    {
        $s1 = CashRegisterSession::updateOrCreate(['register_id' => 1, 'user_id' => 2, 'status' => 'closed'], [
            'branch_id'     => 1,
            'opening_cash'  => 200.00,
            'expected_cash' => 200.00,
            'actual_cash'   => 200.00,
            'difference'    => 0.00,
            'opened_at'     => now()->subDays(30)->setTime(7, 0, 0),
            'closed_at'     => now()->subDays(30)->setTime(21, 0, 0),
            'notes'         => 'Daily close.',
        ]);

        $s2 = CashRegisterSession::updateOrCreate(['register_id' => 2, 'user_id' => 3, 'status' => 'open'], [
            'branch_id'     => 1,
            'opening_cash'  => 150.00,
            'expected_cash' => 150.00,
            'actual_cash'   => null,
            'difference'    => null,
            'opened_at'     => now()->setTime(7, 30, 0),
            'closed_at'     => null,
            'notes'         => 'Morning shift.',
        ]);

        $s3 = CashRegisterSession::updateOrCreate(['register_id' => 3, 'user_id' => 3, 'status' => 'closed'], [
            'branch_id'     => 2,
            'opening_cash'  => 300.00,
            'expected_cash' => 300.00,
            'actual_cash'   => 300.00,
            'difference'    => 0.00,
            'opened_at'     => now()->subDays(2)->setTime(8, 0, 0),
            'closed_at'     => now()->subDays(2)->setTime(20, 0, 0),
            'notes'         => 'City Mall daily close.',
        ]);

        $transactions = [
            [$s1->id, 50,  'cash_in',  'Float top-up for the morning rush'],
            [$s1->id, 120, 'cash_in',  'Cash collected from Register 1'],
            [$s1->id, 45,  'cash_out', 'Petty cash withdrawal to buy supplies'],
            [$s3->id, 200, 'cash_in',  'Opening float adjustment'],
        ];
        foreach ($transactions as [$sessionId, $amount, $type, $description]) {
            CashTransaction::create([
                'session_id'       => $sessionId,
                'user_id'          => 1,
                'transaction_type' => $type,
                'amount'           => $amount,
                'description'      => $description,
                'created_at'       => now()->subDays(30),
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 12. Held sales
    // -----------------------------------------------------------------------
    private function seedHeldSales(): void
    {
        HeldSale::updateOrCreate(['hold_number' => 'HOLD-001'], [
            'customer_id' => 3,
            'cashier_id'  => 2,
            'discount'    => 0,
            'tax'         => 10,
            'total'       => 16.17,
            'items_json'  => [
                ['product_id' => 6, 'name' => 'Arabica Coffee Beans 250g', 'sku' => 'AC-250G', 'price' => 6.0, 'quantity' => 2, 'discount' => 0, 'tax' => 10],
                ['product_id' => 9, 'name' => 'Potato Chips 80g', 'sku' => 'PC-80G', 'price' => 0.9, 'quantity' => 3, 'discount' => 0, 'tax' => 10],
            ],
            'created_at' => now()->subMinutes(90),
        ]);
    }

    // -----------------------------------------------------------------------
    // 13. Notifications
    // -----------------------------------------------------------------------
    private function seedNotifications(): void
    {
        $items = [
            [1,  NotificationType::LOW_STOCK,    'Low Stock Alert: Body Soap 250g',  'Product Body Soap 250g has only 8 units remaining in stock.', '/products/19', false],
            [2,  NotificationType::OUT_OF_STOCK, 'Out of Stock: Mineral Water 500ml', 'Product Mineral Water 500ml is now out of stock in the main warehouse.', '/products/3', false],
            [1,  NotificationType::OUT_OF_STOCK, 'Out of Stock: Sports Cap',          'Product Sports Cap is now out of stock in the main warehouse.', '/products/16', false],
            [1,  NotificationType::SALES,        'New Sale Completed',                 'An invoice was completed at the POS register (Main Branch).', '/sales', true],
            [2,  NotificationType::PURCHASE,     'Purchase Order Received',            'Purchase order PO-2026-0015 was received into the warehouse.', '/purchases', true],
            [1,  NotificationType::PAYMENT,      'Payment Confirmed',                  'Payment for a sale invoice was confirmed successfully.', '/sales', true],
            [null, NotificationType::SYSTEM,     'Scheduled System Maintenance',       'The system will undergo scheduled maintenance this Sunday at 01:00 AM.', null, false],
        ];

        foreach ($items as [$userId, $type, $title, $message, $link, $read]) {
            $notification = Notification::firstOrCreate(
                ['user_id' => $userId, 'title' => $title],
                [
                    'type'     => $type,
                    'message'  => $message,
                    'link'     => $link,
                    'is_read'  => $read,
                ]
            );
            $notification->forceFill(['created_at' => now()->subHours(rand(1, 48))])->save();
        }
    }

    // -----------------------------------------------------------------------
    // 14. Audit logs
    // -----------------------------------------------------------------------
    private function seedAuditLogs(): void
    {
        $logs = [
            [1, AuditAction::LOGIN,     'auth',        '1',  'User logged in via API.'],
            [1, AuditAction::CREATE,    'products',    '1',  'Created product "Coca-Cola 500ml".'],
            [1, AuditAction::CREATE,    'categories',  '8',  'Created category "Cleaning Supplies".'],
            [1, AuditAction::RECEIVE,   'purchases',   'PO-2026-0015', 'Received 48 units into Warehouse #1.'],
            [1, AuditAction::CREATE,    'sales',       'INV-2026-4048', 'Completed a sale at the POS register.'],
            [1, AuditAction::CREATE,    'expenses',    '1',  'Recorded a Rent expense of $450.'],
            [1, AuditAction::TRANSFER,  'inventory',   'TRF-2026-0001', 'Transferred items from Warehouse #3 to Warehouse #1.'],
            [1, AuditAction::PAYMENT,   'sales',       'INV-2026-4001', 'Received payment for a sale invoice.'],
            [1, AuditAction::ADJUST,    'inventory',   'ADJ-00012', 'Stock count discrepancy resolved.'],
        ];

        foreach ($logs as [$userId, $action, $module, $record, $description]) {
            $log = AuditLog::create([
                'user_id'     => $userId,
                'action'      => $action,
                'module'      => $module,
                'record'      => $record,
                'description' => $description,
            ]);
            $log->forceFill(['created_at' => now()->subDays(rand(1, 30))->setTime(rand(8, 18), rand(0, 59))])->save();
        }
    }

    // -----------------------------------------------------------------------
    // Deterministic PRNG (mulberry32) so seeding is reproducible
    // -----------------------------------------------------------------------
    private function rnd(int &$state): float
    {
        $state = ($state + 0x6D2B79F5) & 0xFFFFFFFF;
        $t = $state;
        $t = (($t ^ ($t >> 15)) * ($t | 1)) & 0xFFFFFFFF;
        $t = (($t ^ ($t >> 13)) * ($t | 5)) & 0xFFFFFFFF;
        $t = ($t ^ ($t >> 16)) & 0xFFFFFFFF;
        return $t / 0xFFFFFFFF;
    }
}