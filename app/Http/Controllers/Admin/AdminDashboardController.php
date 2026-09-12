<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Lead;
use App\Models\Search;
use App\Models\ApiUsageLog;

class AdminDashboardController extends Controller {
    public function index() {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'trial_tenants' => Tenant::where('status', 'trial')->count(),
            'total_users' => User::where('is_super_admin', false)->count(),
            'active_subscriptions' => Subscription::withoutGlobalScopes()->whereIn('status', ['active', 'trial'])->count(),
            'revenue_this_month' => Payment::where('status', 'success')->whereMonth('created_at', now()->month)->sum('amount'),
            'revenue_total' => Payment::where('status', 'success')->sum('amount'),
            'total_leads' => Lead::withoutGlobalScopes()->count(),
            'total_searches' => Search::withoutGlobalScopes()->count(),
        ];

        $recentTenants = Tenant::latest()->take(10)->get();
        $recentPayments = Payment::with('tenant')->where('status', 'success')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentTenants', 'recentPayments'));
    }
}