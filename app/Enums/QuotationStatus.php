<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case VIEWED = 'viewed';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::VIEWED => 'Viewed',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => '#6B7280',
            self::SENT => '#3B82F6',
            self::VIEWED => '#8B5CF6',
            self::ACCEPTED => '#10B981',
            self::REJECTED => '#EF4444',
            self::EXPIRED => '#F59E0B',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::ACCEPTED, self::REJECTED, self::EXPIRED]);
    }
}
