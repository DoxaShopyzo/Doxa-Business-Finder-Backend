<?php

namespace App\Enums;

enum LeadSource: string
{
    case BUSINESS_FINDER = 'business_finder';
    case META_LEADS = 'meta_leads';
    case WEBSITE_LEADS = 'website_leads';
    case REFERRAL = 'referral';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::BUSINESS_FINDER => 'Business Finder',
            self::META_LEADS => 'Meta Leads',
            self::WEBSITE_LEADS => 'Website Leads',
            self::REFERRAL => 'Referral',
            self::MANUAL => 'Manual Entry',
        };
    }
}
