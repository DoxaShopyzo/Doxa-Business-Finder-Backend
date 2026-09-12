<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    case ADDED = 'added';
    case USED = 'used';
    case BONUS = 'bonus';
    case REFUND = 'refund';
    case ADMIN_ADJUSTMENT = 'admin_adjustment';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ADDED => 'Credits Added',
            self::USED => 'Credits Used',
            self::BONUS => 'Bonus Credits',
            self::REFUND => 'Refund',
            self::ADMIN_ADJUSTMENT => 'Admin Adjustment',
            self::EXPIRED => 'Credits Expired',
        };
    }

    public function isCredit(): bool
    {
        return in_array($this, [self::ADDED, self::BONUS, self::REFUND, self::ADMIN_ADJUSTMENT]);
    }

    public function isDebit(): bool
    {
        return in_array($this, [self::USED, self::EXPIRED]);
    }
}
