<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Tab 1: General Business
            'business_name'          => 'Smart Inventory & POS',
            'business_email'         => 'contact@smartpos.com',
            'business_phone'         => '+1 (555) 019-2834',
            'business_address'       => '123 Main Street, Suite 400, New York, NY 10001',
            // Tab 2: Localization & Currency
            'currency_code'          => 'USD',
            'currency_symbol'        => '$',
            'timezone'               => 'UTC',
            'date_format'            => 'YYYY-MM-DD',
            // Tab 3: Tax & Billing
            'enable_tax'             => true,
            'tax_rate'               => 10,
            'tax_number'             => 'VAT-99887766',
            // Tab 4: POS & Receipts
            'receipt_header'         => 'Thank you for shopping with us!',
            'receipt_footer'         => 'Goods sold are not returnable without receipt.',
            'auto_print_receipt'     => true,
            'allow_discount'         => true,
            // Tab 5: Payment Methods (JSON Array)
            'payment_methods'        => [
                ['id' => 'cash', 'name' => 'Cash', 'enabled' => true],
                ['id' => 'credit_card', 'name' => 'Credit / Debit Card', 'enabled' => true],
                ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'enabled' => true],
                ['id' => 'qr_code', 'name' => 'QR Code Payment', 'enabled' => true],
            ],
            // Tab 6: Inventory & Products
            'low_stock_threshold'    => 5,
            'enable_negative_stock'  => false,
            'barcode_type'           => 'CODE128',
            // Tab 7: Notifications & Alerts
            'notify_low_stock'       => true,
            'notify_out_of_stock'    => true,
            'notification_email'     => 'alerts@smartpos.com',
        ];
        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['value' => $value]
            );
        }
    }
}
