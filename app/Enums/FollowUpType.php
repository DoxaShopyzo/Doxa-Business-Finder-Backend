<?php

namespace App\Enums;

enum FollowUpType: string
{
    case CALL = 'call';
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case MEETING = 'meeting';
    case DEMO = 'demo';
    case PROPOSAL = 'proposal';
    case PAYMENT = 'payment';

    public function label(): string
    {
        return match ($this) {
            self::CALL => 'Call',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'Email',
            self::MEETING => 'Meeting',
            self::DEMO => 'Demo',
            self::PROPOSAL => 'Proposal',
            self::PAYMENT => 'Payment',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CALL => 'phone',
            self::WHATSAPP => 'message-circle',
            self::EMAIL => 'mail',
            self::MEETING => 'users',
            self::DEMO => 'monitor',
            self::PROPOSAL => 'file-text',
            self::PAYMENT => 'credit-card',
        };
    }
}
