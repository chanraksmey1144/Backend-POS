<?php

namespace App\Enums;

enum NotificationType: string
{
    case LOW_STOCK    = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case PURCHASE     = 'purchase';
    case SALES        = 'sales';
    case RETURN       = 'return';
    case PAYMENT      = 'payment';
    case SYSTEM       = 'system';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}