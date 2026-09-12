<?php

namespace App\Services\Preview;

use App\Models\GeneratedPreview;
use App\Models\PreviewSendLog;
use Illuminate\Support\Facades\Log;

class WhatsAppDeliveryService
{
    /**
     * Generate WhatsApp deep link and pre-filled message text
     */
    public function generateSharePayload(GeneratedPreview $preview, ?string $recipientPhone = null, ?int $userId = null): array
    {
        $businessName = $preview->business_name;
        $previewUrl = $preview->public_url;
        $typeLabel = ($preview->preview_type === 'app') ? 'Mobile App UI Concept' : 'Modern Website Preview';

        $phone = $this->cleanPhoneNumber($recipientPhone ?? ($preview->custom_content['phone'] ?? ''));

        // FIX 2: Check DND status on the linked lead
        $dndStatus = null;
        if ($preview->campaign_lead_id) {
            $campaignLead = \App\Models\CampaignLead::withoutGlobalScopes()->find($preview->campaign_lead_id);
            $dndStatus = $campaignLead?->dnd_status;
        } elseif ($preview->crm_lead_id) {
            $crmLead = \App\Models\Lead::withoutGlobalScopes()->find($preview->crm_lead_id);
            $dndStatus = $crmLead?->dnd_status ?? 'exempt';
        }

        // Block if DND is active
        if ($dndStatus === 'dnd_active') {
            return [
                'preview_id' => $preview->id,
                'token' => $preview->token,
                'can_send' => false,
                'dnd_status' => 'dnd_active',
                'recipient_phone' => $phone,
                'error' => 'WhatsApp dispatch blocked: Lead has active DND registration under TRAI NDNC registry.',
                'whatsapp_deep_link' => null,
                'message_text' => null,
            ];
        }

        // Block if DND is not checked
        if (empty($dndStatus) || $dndStatus === 'not_checked') {
            return [
                'preview_id' => $preview->id,
                'token' => $preview->token,
                'can_send' => false,
                'dnd_status' => 'not_checked',
                'recipient_phone' => $phone,
                'error' => 'WhatsApp dispatch blocked: TRAI DND status not checked. Verify DND registry before dispatching sales pitches.',
                'whatsapp_deep_link' => null,
                'message_text' => null,
            ];
        }

        // Bilingual professional sales pitch message
        $message = "Vanakkam! We designed an exclusive *{$typeLabel}* tailored for *{$businessName}*:\n\n"
                 . "🔗 *Click to view your live interactive demo:*\n{$previewUrl}\n\n"
                 . "✨ *Key Highlights Included:*\n"
                 . "• 100% Mobile & Desktop Responsive Design\n"
                 . "• Instant WhatsApp Customer Booking & Direct Inquiries\n"
                 . "• Optimized for Local Google Search Visibility\n\n"
                 . "Would you like us to walk you through how this can generate more customers for your business? Please let us know!\n\n"
                 . "Best regards,\n*Doxa Business Solutions*";

        $encodedText = rawurlencode($message);
        $deepLink = !empty($phone)
            ? "https://wa.me/{$phone}?text={$encodedText}"
            : "https://wa.me/?text={$encodedText}";

        return [
            'preview_id' => $preview->id,
            'token' => $preview->token,
            'can_send' => true,
            'dnd_status' => $dndStatus,
            'recipient_phone' => $phone,
            'message_text' => $message,
            'whatsapp_deep_link' => $deepLink,
        ];
    }

    /**
     * Log a send/share event
     */
    public function logSendAttempt(GeneratedPreview $preview, ?string $recipientPhone, ?int $userId, string $channel = 'whatsapp_deeplink'): PreviewSendLog
    {
        $payload = $this->generateSharePayload($preview, $recipientPhone, $userId);

        if (isset($payload['can_send']) && $payload['can_send'] === false) {
            throw new \App\Exceptions\DndBlockedException($payload['error'], $payload['dnd_status'] ?? 'dnd_active');
        }

        $log = PreviewSendLog::create([
            'tenant_id' => $preview->tenant_id,
            'preview_id' => $preview->id,
            'campaign_lead_id' => $preview->campaign_lead_id,
            'crm_lead_id' => $preview->crm_lead_id,
            'user_id' => $userId,
            'channel' => $channel,
            'recipient_phone' => $payload['recipient_phone'],
            'message_body' => $payload['message_text'],
            'status' => 'initiated',
        ]);

        $preview->increment('share_count');
        $preview->update(['status' => 'shared']);

        Log::info("[PREVIEW_WHATSAPP_SEND] Preview #{$preview->id} ({$preview->preview_type}) share initiated for {$preview->business_name} (Phone: {$payload['recipient_phone']}) by User #{$userId}.");

        return $log;
    }

    /**
     * Clean phone number into international format without + or spaces
     */
    public function cleanPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Strip non-numeric characters
        $digits = preg_replace('/[^\d]/', '', $phone);

        // Standard Indian mobile number normalization
        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '91' . substr($digits, 1);
        }

        return $digits;
    }
}
