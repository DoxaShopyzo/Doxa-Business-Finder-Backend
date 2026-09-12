<?php

namespace App\Http\Controllers\Preview;

use App\Http\Controllers\Controller;
use App\Models\GeneratedPreview;
use App\Models\PreviewTemplate;
use App\Services\Preview\PreviewGeneratorService;
use App\Services\Preview\WhatsAppDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PreviewController extends Controller
{
    public function __construct(
        protected PreviewGeneratorService $generatorService,
        protected WhatsAppDeliveryService $whatsAppService
    ) {}

    /**
     * Public Route: Server-render a personalized website or app preview
     * GET /preview/{token}
     */
    public function renderPreview(string $token)
    {
        $preview = GeneratedPreview::withoutGlobalScopes()
            ->with(['template'])
            ->where('token', $token)
            ->firstOrFail();

        // Check 90-day expiration
        if ($preview->expires_at && $preview->expires_at->isPast()) {
            return response()->view('errors.410', [
                'message' => 'This preview link has expired. Please contact your Doxa representative for a fresh concept link.'
            ], 410);
        }

        // Increment view count asynchronously
        $preview->increment('view_count');
        $preview->update(['last_viewed_at' => now()]);

        $template = $preview->template;
        $type = $preview->preview_type; // 'website' or 'app'
        $categoryKey = $template?->category_key ?? 'general_business';

        // Resolve Blade view name
        $viewName = "previews.templates.{$type}.{$categoryKey}";

        if (!View::exists($viewName)) {
            $viewName = ($type === 'app')
                ? 'previews.templates.app.general_business_app'
                : 'previews.templates.website.general_business';
        }

        return view($viewName, [
            'preview' => $preview,
            'theme' => $preview->theme_json ?? [],
            'salesMode' => request()->query('mode') === 'sales',
            'inEditor' => request()->query('in_editor') == '1',
        ]);
    }

    /**
     * API: Generate a personalized preview
     * POST /api/previews/generate
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer',
            'lead_type' => 'nullable|in:campaign,crm',
            'preview_type' => 'nullable|in:website,app',
            'template_id' => 'nullable|integer|exists:preview_templates,id',
            'custom_colors' => 'nullable|array',
            'logo' => 'nullable|image|max:4096', // Max 4MB
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $logoPath = null;
        $logoUrl = null;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("previews/{$tenantId}", $filename, 'public');
            $logoPath = Storage::disk('public')->path($path);
            $logoUrl = Storage::disk('public')->url($path);
        }

        try {
            $result = $this->generatorService->generateForLead([
                'lead_id' => $validated['lead_id'],
                'lead_type' => $validated['lead_type'] ?? 'campaign',
                'preview_type' => $validated['preview_type'] ?? 'website',
                'template_id' => $validated['template_id'] ?? null,
                'custom_colors' => $validated['custom_colors'] ?? null,
                'logo_path' => $logoPath,
                'logo_url' => $logoUrl,
                'tenant_id' => $tenantId,
                'user_id' => $user?->id ?? 1,
            ]);
        } catch (\App\Exceptions\LeadNotVerifiedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 423);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($validated['preview_type'] ?? 'website') . ' preview generated successfully.',
            'preview' => $result['preview'],
            'public_url' => $result['public_url'],
            'whatsapp' => $result['whatsapp'],
        ]);
    }

    /**
     * API: Get existing generated previews for a lead
     * GET /api/previews/lead/{leadType}/{leadId}
     */
    public function getLeadPreviews(string $leadType, int $leadId)
    {
        $tenantId = Auth::user()->tenant_id ?? 1;

        $previews = GeneratedPreview::where('tenant_id', $tenantId)
            ->with(['template'])
            ->when($leadType === 'campaign', fn($q) => $q->where('campaign_lead_id', $leadId))
            ->when($leadType === 'crm', fn($q) => $q->where('crm_lead_id', $leadId))
            ->get();

        $websitePreview = $previews->firstWhere('preview_type', 'website');
        $appPreview = $previews->firstWhere('preview_type', 'app');

        return response()->json([
            'success' => true,
            'website_preview' => $websitePreview ? [
                'id' => $websitePreview->id,
                'token' => $websitePreview->token,
                'public_url' => $websitePreview->public_url,
                'template' => $websitePreview->template?->name,
                'view_count' => $websitePreview->view_count,
                'theme' => $websitePreview->theme_json,
                'whatsapp' => $this->whatsAppService->generateSharePayload($websitePreview, null, Auth::id()),
            ] : null,
            'app_preview' => $appPreview ? [
                'id' => $appPreview->id,
                'token' => $appPreview->token,
                'public_url' => $appPreview->public_url,
                'template' => $appPreview->template?->name,
                'view_count' => $appPreview->view_count,
                'theme' => $appPreview->theme_json,
                'whatsapp' => $this->whatsAppService->generateSharePayload($appPreview, null, Auth::id()),
            ] : null,
        ]);
    }

    /**
     * API: Log WhatsApp or external share attempt
     * POST /api/previews/{id}/log-send
     */
    public function logSend(Request $request, int $id)
    {
        $preview = GeneratedPreview::findOrFail($id);
        $phone = $request->input('phone');
        $channel = $request->input('channel', 'whatsapp_deeplink');

        try {
            $log = $this->whatsAppService->logSendAttempt($preview, $phone, Auth::id(), $channel);
        } catch (\App\Exceptions\DndBlockedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'dnd_status' => $e->getDndStatus(),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Preview share logged successfully.',
            'log' => $log,
        ]);
    }

    /**
     * API: List master templates
     * GET /api/previews/templates
     */
    public function listTemplates()
    {
        $templates = PreviewTemplate::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'website_templates' => $templates->where('type', 'website')->values(),
            'app_templates' => $templates->where('type', 'app')->values(),
        ]);
    }

    /**
     * Web Route: Open interactive Preview Editor for admin/sales
     * GET /admin/previews/{id}/editor
     */
    public function editor(Request $request, int $id)
    {
        $preview = GeneratedPreview::withoutGlobalScopes()
            ->with(['template'])
            ->findOrFail($id);

        $compatibleTemplates = PreviewTemplate::where('type', $preview->preview_type)
            ->where('is_active', true)
            ->get();

        $whatsAppPayload = $this->whatsAppService->generateSharePayload(
            $preview,
            $preview->custom_content['phone'] ?? null,
            Auth::id()
        );

        return view('admin.previews.editor', compact('preview', 'compatibleTemplates', 'whatsAppPayload'));
    }

    /**
     * API: Update preview colors, business data, content, and CTA
     * POST /api/previews/{id}/update
     */
    public function updatePreview(Request $request, int $id)
    {
        $preview = GeneratedPreview::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'cta_title' => 'nullable|string|max:255',
            'cta_button' => 'nullable|string|max:100',
            'theme' => 'nullable|array',
            'template_id' => 'nullable|integer|exists:preview_templates,id',
        ]);

        $customContent = $preview->custom_content ?? [];
        if (isset($validated['phone'])) $customContent['phone'] = $validated['phone'];
        if (isset($validated['address'])) $customContent['address'] = $validated['address'];
        if (isset($validated['cta_title'])) $customContent['cta_title'] = $validated['cta_title'];
        if (isset($validated['cta_button'])) $customContent['cta_button'] = $validated['cta_button'];

        $themeJson = $preview->theme_json ?? [];
        if (!empty($validated['theme'])) {
            $themeJson = array_merge($themeJson, $validated['theme']);
        }

        $preview->update([
            'business_name' => $validated['business_name'] ?? $preview->business_name,
            'category' => $validated['category'] ?? $preview->category,
            'template_id' => $validated['template_id'] ?? $preview->template_id,
            'custom_content' => $customContent,
            'theme_json' => $themeJson,
            'status' => 'edited',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preview saved successfully.',
            'preview' => $preview->fresh(['template']),
        ]);
    }

    /**
     * API: Switch master template while retaining all custom branding
     * POST /api/previews/{id}/switch-template
     */
    public function switchTemplate(Request $request, int $id)
    {
        $validated = $request->validate([
            'template_id' => 'required|integer|exists:preview_templates,id',
        ]);

        $preview = GeneratedPreview::withoutGlobalScopes()->findOrFail($id);
        $newTemplate = PreviewTemplate::findOrFail($validated['template_id']);

        $preview->update([
            'template_id' => $newTemplate->id,
            'preview_type' => $newTemplate->type,
            'status' => 'edited',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Switched to template '{$newTemplate->name}'.",
            'preview' => $preview->fresh(['template']),
        ]);
    }
}
