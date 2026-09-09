<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 $userId = User::query()->first()?->id ?? 1;
        $items = [
            [
                'user_id' => $userId,
                'type'    => NotificationType::LOW_STOCK,
                'title'   => 'Low Stock Alert: Mechanical Keyboard',
                'message' => 'Product Mechanical Keyboard has only 3 units remaining in stock.',
                'link'    => '/inventory/products/101',
                'is_read' => false,
            ],
            [
                'user_id' => $userId,
                'type'    => NotificationType::OUT_OF_STOCK,
                'title'   => 'Out of Stock: Wireless Gaming Mouse',
                'message' => 'Product Wireless Gaming Mouse is now out of stock in the main warehouse.',
                'link'    => '/inventory/products/102',
                'is_read' => false,
            ],
            [
                'user_id' => $userId,
                'type'    => NotificationType::SALES,
                'title'   => 'New Sales Order #ORD-2026-001',
                'message' => 'Customer John Doe placed a new order totaling $125.00.',
                'link'    => '/sales/orders/ORD-2026-001',
                'is_read' => true,
            ],
            [
                'user_id' => $userId,
                'type'    => NotificationType::PAYMENT,
                'title'   => 'Payment Confirmed for #ORD-2026-001',
                'message' => 'Payment gateway processed a payment of $125.00 successfully.',
                'link'    => '/finance/transactions/TX-9988',
                'is_read' => true,
            ],
            [
                'user_id' => null, // Global notice
                'type'    => NotificationType::SYSTEM,
                'title'   => 'Scheduled System Maintenance',
                'message' => 'The system will undergo scheduled server maintenance this Sunday at 01:00 AM UTC.',
                'link'    => null,
                'is_read' => false,
            ],
        ];
        foreach ($items as $item) {
            Notification::create($item);
        }
    }
}
