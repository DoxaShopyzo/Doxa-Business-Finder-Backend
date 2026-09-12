<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PreviewTemplate;
use App\Models\GeneratedPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPreviewTemplateController extends Controller
{
    /**
     * Display listing of master preview templates
     */
    public function index(Request $request)
    {
        $typeFilter = $request->query('type'); // 'website' or 'app'
        $search = $request->query('search');

        $query = PreviewTemplate::query()->withCount('previews');

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category_key', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('type')->orderBy('name')->paginate(25);

        $stats = [
            'total' => PreviewTemplate::count(),
            'website_count' => PreviewTemplate::where('type', 'website')->count(),
            'app_count' => PreviewTemplate::where('type', 'app')->count(),
            'active_count' => PreviewTemplate::where('is_active', true)->count(),
            'total_previews_generated' => GeneratedPreview::withoutGlobalScopes()->count(),
        ];

        return view('admin.previews.templates.index', compact('templates', 'stats', 'typeFilter', 'search'));
    }

    /**
     * Toggle active/inactive status of a master template
     */
    public function toggleStatus(int $id)
    {
        $template = PreviewTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        return redirect()->back()->with('success', "Template '{$template->name}' status updated to " . ($template->is_active ? 'Active' : 'Inactive'));
    }

    /**
     * Duplicate a master template with a new version/slug
     */
    public function duplicate(int $id)
    {
        $template = PreviewTemplate::findOrFail($id);

        $newSlug = $template->slug . '_v' . Str::random(4);
        $newName = $template->name . ' (Copy)';

        $copy = $template->replicate();
        $copy->slug = $newSlug;
        $copy->name = $newName;
        $copy->version = '1.1';
        $copy->save();

        return redirect()->back()->with('success', "Template duplicated successfully as '{$newName}'.");
    }

    /**
     * Render raw master template with sample preview data
     */
    public function previewRaw(int $id)
    {
        $template = PreviewTemplate::findOrFail($id);

        // Dummy preview container for sample visualization
        $dummyPreview = new GeneratedPreview([
            'id' => 0,
            'business_name' => 'Apex ' . $template->name,
            'category' => ucwords(str_replace('_', ' ', $template->category_key)),
            'preview_type' => $template->type,
            'token' => 'sample-preview-token',
            'preview_code' => 'PREVIEW-SAMPLE',
            'theme_json' => [
                'primary_color' => $template->default_palette['primary'] ?? '#1E3A5F',
                'secondary_color' => $template->default_palette['secondary'] ?? '#0F172A',
                'accent_color' => $template->default_palette['accent'] ?? '#0EA5E9',
                'bg_color' => $template->default_palette['bg'] ?? '#F8FAFC',
                'surface_color' => '#FFFFFF',
                'text_color' => '#1E293B',
                'heading_color' => '#0F172A',
                'border_color' => '#E2E8F0',
                'button_color' => $template->default_palette['primary'] ?? '#1E3A5F',
                'border_radius' => '12px',
                'font_family' => 'Plus Jakarta Sans, sans-serif',
            ],
            'custom_content' => [
                'phone' => '+91 98765 43210',
                'address' => '42 Business Avenue, High Street, Chennai, Tamil Nadu',
                'rating' => 4.8,
                'review_count' => 128,
                'tagline' => $template->sample_content['tagline'] ?? "Premier solutions crafted for your business excellence",
                'features' => $template->sample_content['features'] ?? ['Verified Excellence', 'Dedicated Support', 'Modern Technology'],
            ],
        ]);

        $dummyPreview->setRelation('template', $template);

        $viewName = "previews.templates.{$template->type}.{$template->category_key}";
        if (!\Illuminate\Support\Facades\View::exists($viewName)) {
            $viewName = ($template->type === 'app')
                ? 'previews.templates.app.general_business_app'
                : 'previews.templates.website.general_business';
        }

        return view($viewName, [
            'preview' => $dummyPreview,
            'theme' => $dummyPreview->theme_json,
        ]);
    }
}
