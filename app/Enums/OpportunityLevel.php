<?php

namespace App\Enums;

enum OpportunityLevel: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function label(): string
    {
        return match ($this) {
            self::HIGH => 'High Opportunity',
            self::MEDIUM => 'Medium Opportunity',
            self::LOW => 'Low Opportunity',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HIGH => '#10B981',
            self::MEDIUM => '#F59E0B',
            self::LOW => '#EF4444',
        };
    }

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 80 => self::HIGH,
            $score >= 60 => self::MEDIUM,
            default => self::LOW,
        };
    }
}
