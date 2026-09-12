<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Search;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function index()
    {
        // Summary cards
        $totalRevenue = Payment::withoutGlobalScopes()->where('status', 'success')->sum('amount');
        $totalSearches = Search::withoutGlobalScopes()->count();
        $totalLeads = Lead::withoutGlobalScopes()->count();
        $activeTenants = Tenant::where('status', 'active')->count();

        // Revenue by month (last 6 months)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Payment::withoutGlobalScopes()
                ->where('status', 'success')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $count = Payment::withoutGlobalScopes()
                ->where('status', 'success')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $searches = Search::withoutGlobalScopes()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $revenueByMonth[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue,
                'count' => $count,
                'searches' => $searches,
            ];
        }

        // Top 10 tenants by search usage
        $topTenantsBySearch = Tenant::withCount(['searches' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->having('searches_count', '>', 0)
            ->orderByDesc('searches_count')
            ->take(10)
            ->get();

        // Lead conversion stats
        $leadStats = [
            'new' => Lead::withoutGlobalScopes()->where('status', 'new')->count(),
            'contacted' => Lead::withoutGlobalScopes()->where('status', 'contacted')->count(),
            'qualified' => Lead::withoutGlobalScopes()->where('status', 'qualified')->count(),
            'proposal' => Lead::withoutGlobalScopes()->where('status', 'proposal')->count(),
            'negotiation' => Lead::withoutGlobalScopes()->where('status', 'negotiation')->count(),
            'won' => Lead::withoutGlobalScopes()->where('status', 'won')->count(),
            'lost' => Lead::withoutGlobalScopes()->where('status', 'lost')->count(),
        ];

        $totalLeadValue = Lead::withoutGlobalScopes()->where('status', 'won')->sum('won_value') ?? 0;

        return view('admin.reports.index', compact(
            'totalRevenue', 'totalSearches', 'totalLeads', 'activeTenants',
            'revenueByMonth', 'topTenantsBySearch', 'leadStats', 'totalLeadValue'
        ));
    }

    public function export(Request $request)
    {
        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_reports',
            'model_type' => null,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'type' => 'monthly_analytics'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_reports_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Month', 'Successful Payments', 'Revenue (INR)', 'Search Volume']);

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $revenue = Payment::withoutGlobalScopes()
                    ->where('status', 'success')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');
                $count = Payment::withoutGlobalScopes()
                    ->where('status', 'success')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $searches = Search::withoutGlobalScopes()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                fputcsv($handle, [$date->format('M Y'), $count, $revenue, $searches]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}