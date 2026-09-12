<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedSuperAdmin();
        $this->seedPlans();
        $this->seedCreditPackages();
        $this->seedSettings();
    }

    protected function seedPermissions(): void
    {
        $permissions = [
            // Leads
            'view_leads', 'create_leads', 'edit_leads', 'delete_leads',
            'assign_leads', 'view_team_leads', 'export_leads',
            // Search
            'search_businesses', 'use_credits',
            // Credits
            'buy_credits', 'view_credits',
            // Reports
            'view_reports', 'view_revenue', 'view_usage_reports',
            // Team
            'manage_team', 'view_team',
            // Subscription
            'manage_subscription',
            // Settings
            'manage_settings',
            // CRM
            'create_followups', 'edit_followups', 'view_followups',
            'create_quotations', 'edit_quotations', 'view_quotations',
            'create_deals', 'view_deals',
            // Activity
            'log_activity', 'view_activity',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }
    }

    protected function seedRoles(): void
    {
        // Super Admin - gets all via Gate::before
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);

        // Tenant Owner
        $tenantOwner = Role::firstOrCreate(['name' => 'tenant_owner', 'guard_name' => 'sanctum']);
        $tenantOwner->syncPermissions(Permission::all());

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'sanctum']);
        $manager->syncPermissions([
            'view_leads', 'create_leads', 'edit_leads', 'assign_leads',
            'view_team_leads', 'export_leads', 'search_businesses', 'use_credits',
            'view_credits', 'view_reports', 'view_revenue', 'manage_team', 'view_team',
            'create_followups', 'edit_followups', 'view_followups',
            'create_quotations', 'edit_quotations', 'view_quotations',
            'create_deals', 'view_deals', 'log_activity', 'view_activity',
        ]);

        // Sales Executive
        $salesExec = Role::firstOrCreate(['name' => 'sales_executive', 'guard_name' => 'sanctum']);
        $salesExec->syncPermissions([
            'view_leads', 'create_leads', 'edit_leads',
            'search_businesses', 'use_credits', 'view_credits',
            'create_followups', 'edit_followups', 'view_followups',
            'create_quotations', 'view_quotations',
            'create_deals', 'view_deals', 'log_activity', 'view_activity',
        ]);

        // Telecaller
        $telecaller = Role::firstOrCreate(['name' => 'telecaller', 'guard_name' => 'sanctum']);
        $telecaller->syncPermissions([
            'view_leads', 'edit_leads',
            'create_followups', 'edit_followups', 'view_followups',
            'log_activity', 'view_activity',
        ]);

        // Viewer
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'sanctum']);
        $viewer->syncPermissions([
            'view_leads', 'view_followups', 'view_quotations',
            'view_deals', 'view_activity', 'view_credits',
        ]);
    }

    protected function seedSuperAdmin(): void
    {
        $admin = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'admin@doxainfotech.com'],
            [
                'name' => 'Doxa Admin',
                'password' => Hash::make('DoxaAdmin@2024'),
                'email_verified_at' => now(),
                'is_super_admin' => true,
                'status' => 'active',
                'onboarding_completed' => true,
            ]
        );

        // Super admin has no tenant - use team_id = 0 for global scope
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $admin->assignRole('super_admin');
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    protected function seedPlans(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Get started with limited features',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'credits_included' => 50,
                'max_users' => 1,
                'max_searches_per_day' => 5,
                'is_free' => true,
                'sort_order' => 1,
                'features' => [
                    'search' => true,
                    'crm' => true,
                    'opportunity_score' => false,
                    'reports' => false,
                    'export' => false,
                    'api' => false,
                    'ai' => false,
                    'white_label' => false,
                ],
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for individual sales professionals',
                'price' => 499,
                'billing_cycle' => 'monthly',
                'credits_included' => 1000,
                'max_users' => 3,
                'max_searches_per_day' => 20,
                'sort_order' => 2,
                'features' => [
                    'search' => true,
                    'crm' => true,
                    'opportunity_score' => true,
                    'reports' => true,
                    'export' => false,
                    'api' => false,
                    'ai' => false,
                    'white_label' => false,
                ],
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For growing teams with advanced needs',
                'price' => 999,
                'billing_cycle' => 'monthly',
                'credits_included' => 3000,
                'max_users' => 10,
                'max_searches_per_day' => 50,
                'sort_order' => 3,
                'features' => [
                    'search' => true,
                    'crm' => true,
                    'opportunity_score' => true,
                    'reports' => true,
                    'export' => true,
                    'api' => false,
                    'ai' => false,
                    'white_label' => false,
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Full-featured plan for serious businesses',
                'price' => 1999,
                'billing_cycle' => 'monthly',
                'credits_included' => 10000,
                'max_users' => 25,
                'max_searches_per_day' => 100,
                'sort_order' => 4,
                'features' => [
                    'search' => true,
                    'crm' => true,
                    'opportunity_score' => true,
                    'reports' => true,
                    'export' => true,
                    'api' => true,
                    'ai' => true,
                    'white_label' => false,
                ],
            ],
            [
                'name' => 'Agency',
                'slug' => 'agency',
                'description' => 'Enterprise plan for agencies and large teams',
                'price' => 4999,
                'billing_cycle' => 'monthly',
                'credits_included' => 50000,
                'max_users' => 100,
                'max_searches_per_day' => 500,
                'sort_order' => 5,
                'features' => [
                    'search' => true,
                    'crm' => true,
                    'opportunity_score' => true,
                    'reports' => true,
                    'export' => true,
                    'api' => true,
                    'ai' => true,
                    'white_label' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

    protected function seedCreditPackages(): void
    {
        $packages = [
            ['name' => 'Starter Pack', 'credits' => 1000, 'price' => 499, 'bonus_credits' => 0, 'sort_order' => 1],
            ['name' => 'Growth Pack', 'credits' => 3000, 'price' => 999, 'bonus_credits' => 300, 'sort_order' => 2],
            ['name' => 'Pro Pack', 'credits' => 10000, 'price' => 1999, 'bonus_credits' => 1500, 'sort_order' => 3],
            ['name' => 'Agency Pack', 'credits' => 50000, 'price' => 4999, 'bonus_credits' => 10000, 'sort_order' => 4],
        ];

        foreach ($packages as $package) {
            CreditPackage::firstOrCreate(['name' => $package['name']], $package);
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            // Opportunity Score Weights
            ['group' => 'opportunity_score', 'key' => 'website_opportunity', 'value' => 30],
            ['group' => 'opportunity_score', 'key' => 'digital_presence_gap', 'value' => 20],
            ['group' => 'opportunity_score', 'key' => 'business_relevance', 'value' => 15],
            ['group' => 'opportunity_score', 'key' => 'business_activity', 'value' => 15],
            ['group' => 'opportunity_score', 'key' => 'contact_availability', 'value' => 10],
            ['group' => 'opportunity_score', 'key' => 'growth_potential', 'value' => 10],

            // Credit Costs
            ['group' => 'credits', 'key' => 'search_cost', 'value' => 5],
            ['group' => 'credits', 'key' => 'detail_cost', 'value' => 2],
            ['group' => 'credits', 'key' => 'ai_analysis_cost', 'value' => 10],
            ['group' => 'credits', 'key' => 'enrichment_cost', 'value' => 3],
            ['group' => 'credits', 'key' => 'low_credit_threshold_percent', 'value' => 20],

            // Lead Statuses (custom, admin can add more)
            ['group' => 'lead_config', 'key' => 'default_statuses', 'value' => ['new', 'contacted', 'interested', 'demo', 'proposal', 'negotiation', 'won', 'lost']],

            // Service Recommendations
            ['group' => 'service_rules', 'key' => 'restaurant', 'value' => ['website' => 'high', 'digital_marketing' => 'high', 'mobile_app' => 'medium', 'branding' => 'medium']],
            ['group' => 'service_rules', 'key' => 'textile', 'value' => ['website' => 'high', 'ecommerce' => 'high', 'digital_marketing' => 'medium', 'branding' => 'high']],
            ['group' => 'service_rules', 'key' => 'builder', 'value' => ['website' => 'high', 'digital_marketing' => 'high', 'branding' => 'high', 'video_creation' => 'medium']],
            ['group' => 'service_rules', 'key' => 'school', 'value' => ['website' => 'high', 'mobile_app' => 'high', 'digital_marketing' => 'medium', 'crm' => 'medium']],
            ['group' => 'service_rules', 'key' => 'hotel', 'value' => ['website' => 'high', 'digital_marketing' => 'high', 'mobile_app' => 'medium', 'branding' => 'medium']],
            ['group' => 'service_rules', 'key' => 'manufacturer', 'value' => ['website' => 'high', 'ecommerce' => 'medium', 'custom_software' => 'high', 'crm' => 'high']],
            ['group' => 'service_rules', 'key' => 'salon', 'value' => ['website' => 'high', 'digital_marketing' => 'high', 'branding' => 'medium', 'mobile_app' => 'low']],
            ['group' => 'service_rules', 'key' => 'hospital', 'value' => ['website' => 'high', 'mobile_app' => 'high', 'digital_marketing' => 'medium', 'custom_software' => 'high']],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['tenant_id' => null, 'group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
