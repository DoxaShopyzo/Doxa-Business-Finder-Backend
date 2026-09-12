<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminApiUsageController extends Controller
{
    // Enterprise SKU rate: $35 per 1,000 requests = $0.035 per request
    const COST_PER_REQUEST = 0.0350;

    public function index(Request $request)
    {
        $query = ApiUsageLog::withoutGlobalScopes()->with('tenant', 'user');

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('endpoint', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        // Summary stats
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalCallsToday = ApiUsageLog::withoutGlobalScopes()->whereDate('created_at', $today)->count();
        $totalCallsMonth = ApiUsageLog::withoutGlobalScopes()->where('created_at', '>=', $startOfMonth)->count();
        $totalCallsAllTime = ApiUsageLog::withoutGlobalScopes()->count();

        // Calculate spend based on Enterprise tier ($35/1000 = $0.035/request)
        $dailySpendUsd = $totalCallsToday * self::COST_PER_REQUEST;
        $monthlySpendUsd = $totalCallsMonth * self::COST_PER_REQUEST;
        $allTimeSpendUsd = $totalCallsAllTime * self::COST_PER_REQUEST;

        $avgResponseTime = ApiUsageLog::withoutGlobalScopes()->whereDate('created_at', $today)->avg('response_time_ms') ?? 0;
        $errorCount = ApiUsageLog::withoutGlobalScopes()
            ->whereDate('created_at', $today)
            ->where('response_status', '>=', 400)
            ->count();
        $errorRate = $totalCallsToday > 0 ? round(($errorCount / $totalCallsToday) * 100, 1) : 0;

        // Provider breakdown
        $providerBreakdown = ApiUsageLog::withoutGlobalScopes()
            ->selectRaw('provider, count(*) as count, sum(estimated_cost) as total_cost')
            ->groupBy('provider')
            ->get();

        $tenants = Tenant::orderBy('company_name')->get(['id', 'company_name']);
        $providers = ApiUsageLog::withoutGlobalScopes()->distinct()->pluck('provider')->filter()->values();

        return view('admin.api-usage.index', compact(
            'logs', 'totalCallsToday', 'totalCallsMonth', 'totalCallsAllTime',
            'dailySpendUsd', 'monthlySpendUsd', 'allTimeSpendUsd',
            'avgResponseTime', 'errorRate', 'errorCount',
            'providerBreakdown', 'tenants', 'providers'
        ));
    }

    public function export(Request $request)
    {
        $query = ApiUsageLog::withoutGlobalScopes()->with('tenant', 'user');

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        $logs = $query->latest()->get();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_api_usage',
            'model_type' => ApiUsageLog::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $logs->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_api_usage_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Tenant', 'User', 'Provider', 'Endpoint', 'Status', 'Response Time (ms)', 'Cost (USD)', 'Credits Charged', 'Timestamp']);
            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->tenant?->company_name ?? 'N/A',
                    $l->user?->name ?? 'N/A',
                    $l->provider,
                    $l->endpoint,
                    $l->response_status,
                    $l->response_time_ms,
                    $l->estimated_cost ?? 0.0350,
                    $l->credits_charged,
                    $l->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}