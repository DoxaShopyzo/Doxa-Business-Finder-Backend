<?php

namespace App\Services\Preview;

use App\Models\GeneratedPreview;
use App\Models\PreviewTemplate;
use App\Models\CampaignLead;
use App\Models\Lead;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PreviewGeneratorService
{
    public function __construct(
        protected TemplateResolverService $templateResolver,
        protected ColorExtractionService $colorExtractor,
        protected MonogramGeneratorService $monogramGenerator,
        protected WhatsAppDeliveryService $whatsAppDelivery
    ) {}

    /**
     * Generate or update a preview for a lead
     */
    public function generateForLead(array $params): array
    {
        $leadType = $params['lead_type'] ?? 'campaign'; // 'campaign' or 'crm'
        $leadId = (int)$params['lead_id'];
        $previewType = $params['preview_type'] ?? 'website'; // 'website' or 'app'
        $tenantId = (int)$params['tenant_id'];
        $userId = !empty($params['user_id']) ? (int)$params['user_id'] : null;

        // 1. Fetch Lead Information
        $leadData = $this->extractLeadDetails($leadType, $leadId, $tenantId);
        $businessName = $leadData['business_name'];
        $category = $leadData['category'];

        // 2. Select Template
        if (!empty($params['template_id'])) {
            $template = PreviewTemplate::findOrFail($params['template_id']);
        } else {
            $template = $this->templateResolver->resolveTemplate($category, $previewType);
        }

        // 3. Resolve Brand Colors
        $manualPalette = !empty($params['custom_colors']) ? (array)$params['custom_colors'] : null;
        $logoPath = $params['logo_path'] ?? null;
        $palette = $this->colorExtractor->resolvePalette($logoPath, $template->default_palette, $manualPalette);

        // 4. Resolve Logo / Monogram Avatar
        $logoUrl = $params['logo_url'] ?? null;
        if (empty($logoUrl)) {
            $logoUrl = $this->monogramGenerator->generateMonogramDataUri(
                $businessName,
                $palette['primary'],
                $palette['accent']
            );
        }

        // 5. Build Theme & Custom Content
        $themeJson = [
            'primary_color' => $palette['primary'],
            'secondary_color' => $palette['secondary'],
            'accent_color' => $palette['accent'],
            'bg_color' => $palette['bg'],
            'font_family' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'border_radius' => '12px',
        ];

        $content = [
            'phone' => $leadData['phone'],
            'address' => $leadData['address'],
            'rating' => $leadData['rating'],
            'review_count' => $leadData['review_count'],
            'tagline' => $template->sample_content['tagline'] ?? "Premium services tailored for your needs",
            'features' => $template->sample_content['features'] ?? ['100% Quality Guaranteed', 'Professional Team', 'Fast Service'],
        ];

        // 6. Check existing preview for this lead, type, and template, or create fresh
        $forceNew = !empty($params['force_new']);
        $existing = null;

        if (!$forceNew) {
            $existing = GeneratedPreview::where('tenant_id', $tenantId)
                ->where('preview_type', $previewType)
                ->where('template_id', $template->id)
                ->when($leadType === 'campaign', fn($q) => $q->where('campaign_lead_id', $leadId))
                ->when($leadType === 'crm', fn($q) => $q->where('crm_lead_id', $leadId))
                ->first();
        }

        $token = $existing?->token ?? bin2hex(random_bytes(20));
        $expiresAt = now()->addDays(90); // 90-day retention window

        if ($existing) {
            $existing->update([
                'business_name' => $businessName,
                'category' => $category,
                'logo_url' => $logoUrl,
                'theme_json' => $themeJson,
                'custom_content' => $content,
                'expires_at' => $expiresAt,
            ]);
            $preview = $existing;
        } else {
            $preview = GeneratedPreview::create([
                'tenant_id' => $tenantId,
                'preview_type' => $previewType,
                'campaign_lead_id' => ($leadType === 'campaign') ? $leadId : null,
                'crm_lead_id' => ($leadType === 'crm') ? $leadId : null,
                'template_id' => $template->id,
                'token' => $token,
                'preview_code' => 'TEMP',
                'status' => 'generated',
                'version' => '1.0',
                'share_count' => 0,
                'business_name' => $businessName,
                'category' => $category,
                'logo_url' => $logoUrl,
                'theme_json' => $themeJson,
                'custom_content' => $content,
                'expires_at' => $expiresAt,
                'created_by' => $userId,
            ]);
            $preview->update(['preview_code' => sprintf('PREVIEW-%06d', $preview->id)]);
        }

        // 7. Generate WhatsApp Share Payload
        $whatsApp = $this->whatsAppDelivery->generateSharePayload($preview, $leadData['phone'], $userId);

        Log::info("[PREVIEW_GENERATED] {$previewType} preview generated for '{$businessName}' (#{$preview->id}, Code: {$preview->preview_code}, Token: {$preview->token}, Template: {$template->slug}).");

        return [
            'preview' => $preview->load('template'),
            'public_url' => $preview->public_url,
            'whatsapp' => $whatsApp,
        ];
    }

    /**
     * Extract unified business details from campaign_leads or CRM leads
     */
    protected function extractLeadDetails(string $leadType, int $leadId, int $tenantId): array
    {
        if ($leadType === 'crm') {
            $crmLead = Lead::withoutGlobalScopes()->where('id', $leadId)->firstOrFail();
            return [
                'business_name' => $crmLead->business_name,
                'category' => $crmLead->category ?? 'General Business',
                'phone' => $crmLead->phone,
                'address' => $crmLead->formatted_address ?? $crmLead->location,
                'rating' => 4.5,
                'review_count' => 35,
            ];
        }

        $campaignLead = CampaignLead::withoutGlobalScopes()->where('id', $leadId)->firstOrFail();

        // Gate preview generation on human verification
        if ($campaignLead->verification_status !== 'approved') {
            throw new \App\Exceptions\LeadNotVerifiedException(
                "Lead not yet verified — approve in AI Lead Analyzer before generating a preview"
            );
        }

        return [
            'business_name' => $campaignLead->business_name,
            'category' => $campaignLead->category ?? 'General Business',
            'phone' => $campaignLead->phone,
            'address' => $campaignLead->location_address,
            'rating' => $campaignLead->google_rating ?? 4.5,
            'review_count' => $campaignLead->google_review_count ?? 30,
        ];
    }
}
