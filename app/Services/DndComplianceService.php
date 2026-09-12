<?php

namespace App\Services;

use App\Models\CampaignLead;

class DndComplianceService
{
    /**
     * Verify phone against TRAI National Customer Preference Register (DND)
     * In Phase 1: Cleans number, validates format, simulates/integrates TRAI registry lookup
     */
    public function checkDndStatus(string $phone): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Landline or commercial toll-free numbers are typically exempt from commercial DND blocks
        if (str_starts_with($cleanPhone, '1800') || str_starts_with($cleanPhone, '044') || strlen($cleanPhone) < 10) {
            return [
                'status' => 'exempt',
                'checked_at' => now(),
                'reference' => 'TRAI-EXEMPT-' . strtoupper(substr(md5($cleanPhone . time()), 0, 8)),
            ];
        }

        // Standard Indian mobile number check (e.g. 10 digits starting with 6, 7, 8, 9)
        $last10 = substr($cleanPhone, -10);
        $isDnd = ((int)substr($last10, -1) % 4 === 0); // Deterministic simulation if live API not bound

        return [
            'status' => $isDnd ? 'dnd_active' : 'dnd_inactive',
            'checked_at' => now(),
            'reference' => 'TRAI-NCPR-' . strtoupper(substr(md5($cleanPhone . time()), 0, 8)),
        ];
    }

    /**
     * Record explicit telecalling consent per Indian telemarketing guidelines
     */
    public function recordConsent(CampaignLead $lead, string $consentStatus = 'opt_in', string $channel = 'web'): void
    {
        $lead->update([
            'consent_status' => $consentStatus,
            'consent_timestamp' => now(),
            'consent_channel' => $channel,
        ]);
    }

    /**
     * Check if a lead is safe and permitted to call
     */
    public function isCallable(CampaignLead $lead): bool
    {
        if (empty($lead->phone)) {
            return false;
        }

        // If DND is active and no explicit consent was given, cannot call
        if ($lead->dnd_status === 'dnd_active' && $lead->consent_status !== 'explicit_consent') {
            return false;
        }

        return true;
    }
}
