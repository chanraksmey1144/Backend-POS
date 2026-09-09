<?php

namespace App\Enums;

enum AuditAction: string
{
    case CREATE   = 'create';
    case UPDATE   = 'update';
    case DELETE   = 'delete';
    case ARCHIVE  = 'archive';
    case LOGIN    = 'login';
    case LOGOUT   = 'logout';
    case RECEIVE  = 'receive';
    case RETURN   = 'return';
    case ADJUST   = 'adjust';
    case TRANSFER = 'transfer';
    case PAYMENT  = 'payment';
    case CANCEL   = 'cancel';
    case EXPORT   = 'export';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}