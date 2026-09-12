<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case INTERESTED = 'interested';
    case DEMO = 'demo';
    case PROPOSAL = 'proposal';
    case NEGOTIATION = 'negotiation';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::INTERESTED => 'Interested',
            self::DEMO => 'Demo / Meeting',
            self::PROPOSAL => 'Proposal Sent',
            self::NEGOTIATION => 'Negotiation',
            self::WON => 'Won',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => '#3B82F6',
            self::CONTACTED => '#8B5CF6',
            self::INTERESTED => '#F59E0B',
            self::DEMO => '#EC4899',
            self::PROPOSAL => '#6366F1',
            self::NEGOTIATION => '#F97316',
            self::WON => '#10B981',
            self::LOST => '#EF4444',
        };
    }

    public function pipelineOrder(): int
    {
        return match ($this) {
            self::NEW => 1,
            self::CONTACTED => 2,
            self::INTERESTED => 3,
            self::DEMO => 4,
            self::PROPOSAL => 5,
            self::NEGOTIATION => 6,
            self::WON => 7,
            self::LOST => 8,
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::WON, self::LOST]);
    }
}
