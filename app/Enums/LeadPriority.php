<?php

namespace App\Enums;

enum LeadPriority: string
{
    case HOT = 'hot';
    case WARM = 'warm';
    case COLD = 'cold';

    public function label(): string
    {
        return match ($this) {
            self::HOT => 'Hot',
            self::WARM => 'Warm',
            self::COLD => 'Cold',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HOT => '#EF4444',
            self::WARM => '#F59E0B',
            self::COLD => '#3B82F6',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::HOT => 1,
            self::WARM => 2,
            self::COLD => 3,
        };
    }
}
