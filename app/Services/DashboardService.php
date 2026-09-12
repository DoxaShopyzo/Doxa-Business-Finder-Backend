<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\Search;
use App\Models\CreditWallet;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Models\FollowUp;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(int $tenantId): array
    {
        $wallet = CreditWallet::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNull('user_id')
            ->first();

        return [
            'credits_balance' => $wallet?->balance ?? 0,
            'total_leads' => Lead::withoutGlobalScopes()->where('tenant_id', $tenantId)->count(),
            'leads_this_month' => Lead::withoutGlobalScopes()->where('tenant_id', $tenantId)->whereMonth('created_at', now()->month)->count(),
            'total_deals' => Deal::withoutGlobalScopes()->where('tenant_id', $tenantId)->count(),
            'revenue' => Deal::withoutGlobalScopes()->where('tenant_id', $tenantId)->sum('deal_value'),
            'total_searches' => Search::withoutGlobalScopes()->where('tenant_id', $tenantId)->count(),
            'pending_followups' => FollowUp::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('status', 'scheduled')->count(),
            'overdue_followups' => FollowUp::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('status', 'scheduled')->where('followup_date', '<', today())->count(),
            'team_members' => User::withoutGlobalScopes()->where('tenant_id', $tenantId)->count(),
        ];
    }

    public function getLeadFunnel(int $tenantId, ?string $from = null, ?string $to = null): array
    {
        $query = Lead::withoutGlobalScopes()->where('tenant_id', $tenantId);
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to);

        return $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getSalesPipeline(int $tenantId, ?string $from = null, ?string $to = null): array
    {
        $query = Deal::withoutGlobalScopes()->where('tenant_id', $tenantId);
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to);

        return [
            'total_deals' => $query->count(),
            'total_value' => $query->sum('deal_value'),
            'by_status' => $query->select('payment_status', DB::raw('count(*) as count'), DB::raw('sum(deal_value) as value'))
                ->groupBy('payment_status')
                ->get()
                ->toArray(),
        ];
    }

    public function getUsageStats(int $tenantId, ?string $from = null, ?string $to = null): array
    {
        $query = Search::withoutGlobalScopes()->where('tenant_id', $tenantId);
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to);

        $creditQuery = CreditTransaction::withoutGlobalScopes()->where('tenant_id', $tenantId);
        if ($from) $creditQuery->where('created_at', '>=', $from);
        if ($to) $creditQuery->where('created_at', '<=', $to);

        return [
            'total_searches' => $query->count(),
            'credits_used' => $query->sum('credits_used'),
            'avg_results' => round($query->avg('result_count') ?? 0),
            'credits_earned' => $creditQuery->where('type', '!=', 'search')->sum('credits'),
        ];
    }

    public function getStaffPerformance(int $tenantId): array
    {
        return User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->select('users.id', 'users.name')
            ->withCount([
                'leads as total_leads',
                'leads as won_leads' => fn($q) => $q->where('status', 'won'),
            ])
            ->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'total_leads' => $u->total_leads,
                'won_leads' => $u->won_leads,
                'conversion_rate' => $u->total_leads > 0
                    ? round(($u->won_leads / $u->total_leads) * 100, 1)
                    : 0,
            ])
            ->toArray();
    }

    public function getUserDashboard(int $tenantId, ?int $userId): array
    {
        return $this->getStats($tenantId);
    }

    public function getAdminDashboard(): array
    {
        return [
            'total_tenants' => \App\Models\Tenant::count(),
            'active_subscriptions' => \App\Models\Subscription::where('status', 'active')->count(),
            'revenue_this_month' => \App\Models\Payment::where('status', 'success')->whereMonth('created_at', now()->month)->sum('amount'),
        ];
    }
}
