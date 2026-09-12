<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SCHEDULED => '#3B82F6',
            self::COMPLETED => '#10B981',
            self::OVERDUE => '#EF4444',
            self::CANCELLED => '#6B7280',
        };
    }
}
