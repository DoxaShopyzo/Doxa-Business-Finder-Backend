<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'tenant']);

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($modelType = $request->input('model_type')) {
            $query->where('model_type', $modelType);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->filter()->sort()->values();
        $modelTypes = AuditLog::distinct()->pluck('model_type')->filter()->sort()->values();
        $users = User::withoutGlobalScopes()
            ->whereIn('id', AuditLog::distinct()->pluck('user_id')->filter())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.audit-logs.index', compact('logs', 'actions', 'modelTypes', 'users'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with(['user', 'tenant']);

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->get();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_audit_logs',
            'model_type' => AuditLog::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $logs->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_audit_logs_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Tenant', 'Action', 'Model', 'Model ID', 'IP Address', 'Timestamp']);
            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->user?->name ?? 'System/Guest',
                    $l->tenant?->company_name ?? 'N/A',
                    $l->action,
                    $l->model_type ?? 'N/A',
                    $l->model_id ?? 'N/A',
                    $l->ip_address,
                    $l->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}