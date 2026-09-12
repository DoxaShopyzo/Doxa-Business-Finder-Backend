<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Models\AuditLog;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminSecurityController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLog::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('device', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        // Security Stats
        $today = Carbon::today();
        $totalLoginsToday = LoginLog::whereDate('created_at', $today)->count();
        $successfulLoginsToday = LoginLog::whereDate('created_at', $today)->where('status', 'success')->count();
        $failedLoginsToday = LoginLog::whereDate('created_at', $today)->where('status', 'failed')->count();
        $activeTokensCount = PersonalAccessToken::count();

        // Active Tokens list (recent 20)
        $activeTokens = PersonalAccessToken::with('tokenable')
            ->latest('last_used_at')
            ->take(20)
            ->get();

        return view('admin.security.index', compact(
            'logs', 'totalLoginsToday', 'successfulLoginsToday', 'failedLoginsToday',
            'activeTokensCount', 'activeTokens'
        ));
    }

    public function revokeToken(Request $request, $id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $userName = $token->tokenable?->name ?? 'User #' . $token->tokenable_id;
        $token->delete();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'admin_revoke_sanctum_token',
            'model_type' => PersonalAccessToken::class,
            'model_id' => $id,
            'new_values' => ['revoked_for' => $userName],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Token for ' . $userName . ' revoked successfully.');
    }

    public function export(Request $request)
    {
        $query = LoginLog::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $logs = $query->latest()->get();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_security_logs',
            'model_type' => LoginLog::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $logs->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_login_security_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User Name', 'User Email', 'IP Address', 'Device', 'Status', 'Timestamp']);
            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->user?->name ?? 'Unknown',
                    $l->user?->email ?? 'Unknown',
                    $l->ip_address,
                    $l->device ?? 'N/A',
                    $l->status,
                    $l->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
