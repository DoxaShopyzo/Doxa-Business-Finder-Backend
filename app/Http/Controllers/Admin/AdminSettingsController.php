<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index(Request $request)
    {
        $selectedTenantId = $request->input('tenant_id');

        $query = Setting::query();
        if ($selectedTenantId) {
            $query->where('tenant_id', $selectedTenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();
        $grouped = $settings->groupBy('group');

        // Define default settings structure if none exist
        $defaultGroups = [
            'credits' => [
                ['key' => 'default_credits', 'value' => '50', 'description' => 'Default credits allocated on signup'],
                ['key' => 'search_credit_cost', 'value' => '5', 'description' => 'Credits deducted per business search request'],
                ['key' => 'lead_credit_cost', 'value' => '2', 'description' => 'Credits consumed per lead export'],
                ['key' => 'low_credit_threshold', 'value' => '10', 'description' => 'Threshold to trigger low credit warning email'],
            ],
            'opportunity_scoring' => [
                ['key' => 'no_website_weight', 'value' => '30', 'description' => 'Opportunity points awarded if business lacks a website (High potential for web dev)'],
                ['key' => 'low_rating_weight', 'value' => '25', 'description' => 'Opportunity points awarded for Google rating below 4.0'],
                ['key' => 'low_review_count_weight', 'value' => '20', 'description' => 'Opportunity points awarded for fewer than 10 reviews'],
                ['key' => 'no_phone_weight', 'value' => '15', 'description' => 'Opportunity points awarded if phone number is unlisted'],
            ],
            'google_places_compliance' => [
                ['key' => 'pricing_tier', 'value' => 'Enterprise ($35 / 1,000 requests)', 'description' => 'Text Search (New) with rating + userRatingCount field masks bills at Enterprise SKU ($35/1000)'],
                ['key' => 'cache_retention_days', 'value' => '30', 'description' => 'Maximum allowed caching duration per Google Places API terms (Strict 30 days)'],
                ['key' => 'attribution_required', 'value' => 'true', 'description' => 'Powered by Google badge enabled on all search & detail screens'],
            ],
            'payment' => [
                ['key' => 'currency', 'value' => 'INR', 'description' => 'Default currency code'],
                ['key' => 'razorpay_enabled', 'value' => 'true', 'description' => 'Enable Razorpay payment gateway'],
                ['key' => 'tax_rate_percent', 'value' => '18', 'description' => 'GST tax rate percentage (18%)'],
            ],
            'general' => [
                ['key' => 'app_name', 'value' => 'Doxa Business Finder', 'description' => 'Application display name'],
                ['key' => 'support_email', 'value' => 'support@doxabusinessfinder.com', 'description' => 'Support email address'],
                ['key' => 'maintenance_mode', 'value' => 'false', 'description' => 'Enable system-wide maintenance mode'],
            ],
        ];

        // Seed defaults if no global settings exist
        if ($settings->isEmpty() && !$selectedTenantId) {
            foreach ($defaultGroups as $group => $items) {
                foreach ($items as $item) {
                    Setting::create([
                        'tenant_id' => null,
                        'group' => $group,
                        'key' => $item['key'],
                        'value' => $item['value'],
                    ]);
                }
            }
            $settings = Setting::whereNull('tenant_id')->orderBy('group')->orderBy('key')->get();
            $grouped = $settings->groupBy('group');
        }

        $descriptions = [];
        foreach ($defaultGroups as $group => $items) {
            foreach ($items as $item) {
                $descriptions[$group . '.' . $item['key']] = $item['description'] ?? '';
            }
        }

        $groupLabels = [
            'credits' => 'Credits & Pricing',
            'opportunity_scoring' => 'Opportunity Score Algorithm Weights',
            'google_places_compliance' => 'Google Places API Compliance & Costs',
            'payment' => 'Payment & Billing',
            'general' => 'General Settings',
        ];

        $groupIcons = [
            'credits' => 'coins',
            'opportunity_scoring' => 'target',
            'google_places_compliance' => 'map-pin',
            'payment' => 'credit-card',
            'general' => 'settings',
        ];

        $tenants = Tenant::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.settings.index', compact(
            'grouped', 'groupLabels', 'groupIcons', 'descriptions', 'tenants', 'selectedTenantId'
        ));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        $tenantId = $request->input('tenant_id');

        foreach ($settings as $id => $value) {
            Setting::where('id', $id)->update(['value' => $value]);
        }

        AuditLog::create([
            'tenant_id' => $tenantId ?: null,
            'user_id' => auth()->id(),
            'action' => 'admin_update_settings',
            'model_type' => Setting::class,
            'model_id' => null,
            'new_values' => ['updated_count' => count($settings), 'tenant_id' => $tenantId],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.settings.index', ['tenant_id' => $tenantId])
            ->with('success', 'Settings updated and applied successfully.');
    }
}