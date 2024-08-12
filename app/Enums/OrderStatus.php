<?php 
namespace App\Enums;

enum OrderStatus: string {
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
    case DECLINED = 'Declined';
    case INCOMPLETE = 'Incomplete';
    case PENDING = 'Pending';
    case REFUNDED = 'Refunded';
    case DISPUTED = 'Disputed';
    case FAILED = 'Failed';

    public static function getOrderStatusValues(): array
    {
        return [
            self::COMPLETED,
            self::CANCELLED,
            self::DECLINED,
            self::INCOMPLETE,
            self::PENDING,
            self::REFUNDED,
            self::DISPUTED,
            self::FAILED,
        ];
    }
}